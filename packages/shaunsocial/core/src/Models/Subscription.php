<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Enum\SubscriptionBillingCycleType;
use Packages\ShaunSocial\Core\Enum\SubscriptionStatus;
use Packages\ShaunSocial\Core\Enum\SubscriptionTransactionStatus;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasSubject;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Gateway\Models\Gateway;

class Subscription extends Model
{
    use HasSubject, HasCacheQueryFields, IsSubject, HasUser, HasCachePagination;

    protected $cacheQueryFields = [
        'id'
    ];

    public function getListCachePagination()
    {
        return [
            'subscription_all_'.$this->user_id,
            'subscription_active_'.$this->user_id,
            'subscription_stop_'.$this->user_id,
            'subscription_cancel_'.$this->user_id,
        ];
    }

    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'type',
        'gateway_id',
        'params',
        'billing_cycle',
        'billing_cycle_type',
        'package_type',
        'package_id',
        'package_name',
        'expired_at',
        'reminded_at',
        'reminded',
        'status',
        'first_time_active',
        'remind_day',
        'trial_day',
        'trial_amount',
        'admin_cancel',
    ];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'billing_cycle_type' => SubscriptionBillingCycleType::class,
        'expired_at' => 'datetime',
        'admin_cancel' => 'boolean'
    ];

    protected $is_detail = false;

    public function getClass()
    {
        $class = SubscriptionType::getClassByType($this->type);
        return app($class);
    }

    public function getName()
    {
        $class = $this->getClass();
        $name = $class->getName($this->is_detail);
        return ($name ? $name.' - ' : ''). $this->package_name. ' - ' . $this->getPrice();
    }

    public function getStatusText()
    {
        $status = SubscriptionStatus::getAll();
        return $status[$this->status->value];
    }

    public function getPrice()
    {
        return $this->amount. ' '.getWalletTokenName(). ' '. $this->getTypeText();
    }

    public function getGateway()
    {
        return Gateway::findByField('id', $this->gateway_id);
    }

    public function getParams()
    {
        return $this->params ? json_decode($this->params, true) : [];
    }

    public function canCancel()
    {
        return $this->status == SubscriptionStatus::ACTIVE && $this->getGateway()->getClass()->canCancel($this->getParams());
    }

    public function canResume()
    {
        return $this->status == SubscriptionStatus::CANCEL && ! $this->admin_cancel && $this->getGateway()->getClass()->canResume($this->getParams());
    }

    public function canResumeOnAdmin()
    {
        return $this->status == SubscriptionStatus::CANCEL && $this->getGateway()->getClass()->canResume($this->getParams());
    }

    public function getTypeText()
    {
        switch ($this->billing_cycle_type) {
            case SubscriptionBillingCycleType::DAY:
                if ($this->billing_cycle == 1) {
                    return __('daily');
                }
                return '/ '.$this->billing_cycle.' '.__('days');
                break;
            case SubscriptionBillingCycleType::WEEK:
                if ($this->billing_cycle == 1) {
                    return __('weekly');
                }
                return '/ '.$this->billing_cycle.' '.__('weeks');
                break;
            case SubscriptionBillingCycleType::MONTH:
                switch ($this->billing_cycle) {
                    case '1':
                        return __('monthly');
                        break;
                    case '3':
                        return __('quarterly');
                        break;
                    case '6':
                        return __('biannual');
                        break;
                }

                return '/ '.$this->billing_cycle.' '.__('months');
                break;
            case SubscriptionBillingCycleType::YEAR:
                if ($this->billing_cycle == 1) {
                    return __('annual');
                }
                return '/ '.$this->billing_cycle.' '.__('years');
                break;
        }
    }

    public function getListFieldPagination()
    {
        return [
            'status',
            'expired_at'
        ];
    }

    public function getNextExpiredDate()
    {
        if ($this->first_time_active && $this->trial_day) {
            return date('Y-m-d H:i:s',strtotime(' + '. $this->trial_day . ' days'));
        }
        switch ($this->billing_cycle_type) {
            case SubscriptionBillingCycleType::DAY:
                return date('Y-m-d H:i:s',strtotime(' + '. $this->billing_cycle . ' days'));
                break;
            case SubscriptionBillingCycleType::WEEK:
                return date('Y-m-d H:i:s',strtotime(' + '. $this->billing_cycle . ' weeks'));
                break;
            case SubscriptionBillingCycleType::MONTH:
                return date('Y-m-d H:i:s',strtotime(' + '. $this->billing_cycle . ' months'));
                break;
            case SubscriptionBillingCycleType::YEAR:
                return date('Y-m-d H:i:s',strtotime(' + '. $this->billing_cycle . ' years'));
                break;
        }
    }

    public function doActive($params = [], $addTransaction = true, $amount = 0, $gateway_transaction_id = '')
    {
        $remindDay = config('shaun_core.subscription.remind_day');
        $class = $this->getClass();
        $class->doActive($this);
        $remindDayTmp = $class->getRemindDay($this);
        if ($remindDayTmp) {
            $remindDay = $remindDayTmp;
        }
        $expiredAt = $this->getNextExpiredDate();
        $this->update([
            'status' => SubscriptionStatus::ACTIVE,
            'expired_at' => $expiredAt,
            'reminded_at' => date('Y-m-d H:i:s',strtotime($expiredAt. ' - '. $remindDay. ' days')),
            'reminded' => false,
            'remind_day' => $remindDay,
            'first_time_active' => false
        ]);

        if ($addTransaction && $amount > 0) {
            SubscriptionTransaction::create([
                'user_id' => $this->user_id,
                'subscription_id' => $this->id,
                'amount' => $amount,
                'currency' => $this->currency,
                'params' => json_encode($params),
                'status' => SubscriptionTransactionStatus::PAID,
                'gateway_transaction_id' => $gateway_transaction_id
            ]);
        }
    }

    public function doRemind()
    {
        $class = $this->getClass();
        $class->doRemind($this);
        
        $this->update([
            'reminded' => true
        ]);
    }

    public function doCancel()
    {
        $class = $this->getClass();
        $class->doCancel($this);

        $this->getGateway()->getClass()->doCancel($this->getParams());

        $this->update([
            'status' => SubscriptionStatus::CANCEL
        ]);
    }

    public function doResume()
    {
        $class = $this->getClass();
        $class->doResume($this);

        $this->getGateway()->getClass()->doResume($this->getParams());

        $this->update([
            'status' => SubscriptionStatus::ACTIVE
        ]);
    }

    public function doStop($force = false)
    {
        $class = $this->getClass();
        $class->doStop($this, $force);

        $this->update([
            'status' => SubscriptionStatus::STOP
        ]);
    }

    public function setIsDetail($isDetail)
    {
        $this->is_detail = $isDetail;
    }

    public function isDetail()
    {
        return $this->is_detail;
    }

    public static function booted()
    {
        static::creating(function ($subscription) {
            $subscription->first_time_active = true;
        });
    }
}