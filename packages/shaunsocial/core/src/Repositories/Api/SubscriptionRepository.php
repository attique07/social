<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Enum\SubscriptionStatus;
use Packages\ShaunSocial\Core\Http\Resources\Subscription\SubscriptionDetailResource;
use Packages\ShaunSocial\Core\Http\Resources\Subscription\SubscriptionResource;
use Packages\ShaunSocial\Core\Http\Resources\Subscription\SubscriptionTransactionResource;
use Packages\ShaunSocial\Core\Models\Subscription;
use Packages\ShaunSocial\Core\Models\SubscriptionTransaction;

class SubscriptionRepository
{
    public function get($page, $status, $viewer)
    {
        $builder = Subscription::where('user_id', $viewer->id)->orderBy('id', 'DESC');

        switch ($status) {
            case 'active':
                $builder->where('status', SubscriptionStatus::ACTIVE);
                break;
            case 'stop':
                $builder->where('status', SubscriptionStatus::STOP);
                break;
            case 'cancel':
                $builder->where('status', SubscriptionStatus::CANCEL);
                break;
        }

        $subscriptions = Subscription::getCachePagination('subscription_'.$status.'_'.$viewer->id, $builder, $page);
        $subscriptionsNextPage = Subscription::getCachePagination('subscription_'.$status.'_'.$viewer->id, $builder, $page + 1);

        return [
            'items' => SubscriptionResource::collection($subscriptions),
            'has_next_page' => count($subscriptionsNextPage) ? true : false
        ];
    }

    public function get_detail($id)
    {
        $subscription = Subscription::findByField('id', $id);

        return new SubscriptionDetailResource($subscription);
    }

    public function store_cancel($id)
    {
        $subscription = Subscription::findByField('id', $id);
        $subscription->doCancel();
    }

    public function store_resume($id)
    {
        $subscription = Subscription::findByField('id', $id);
        $subscription->doResume();
    }

    public function get_transaction($id, $page)
    {
        $builder = SubscriptionTransaction::where('subscription_id', $id)->orderBy('id', 'DESC');
        $subscriptions = SubscriptionTransaction::getCachePagination('subscription_transaction_'.$id, $builder, $page);
        $subscriptionsNextPage = SubscriptionTransaction::getCachePagination('subscription_transaction_'.$id, $builder, $page + 1);

        return [
            'items' => SubscriptionTransactionResource::collection($subscriptions),
            'has_next_page' => count($subscriptionsNextPage) ? true : false
        ];
    }
}
