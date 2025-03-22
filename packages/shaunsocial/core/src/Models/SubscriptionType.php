<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SubscriptionType extends Model
{
    protected $fillable = [
        'type',
        'class',
        'order'
    ];

    static function getAll()
    {
        return Cache::rememberForever('subscription_types', function () {
            return self::orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
        });
    }

    public function getClass()
    {
        return app($this->class);
    }

    static function getClassByType($type)
    {
        $types = self::getAll();
        $typeObject = $types->first(function ($value, $key) use ($type){
            return $value->type == $type;
        });

        if ($typeObject) {
            return $typeObject->class;
        }

        return '';
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($type) {
            Cache::forget('subscription_types');
        });

        static::saved(function ($package) {
            Cache::forget('subscription_types');
        });
    }
}
