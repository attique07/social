<?php


namespace Packages\ShaunSocial\Core\Enum;

enum SubscriptionStatus: string {
    case INIT = 'init';
    case ACTIVE = 'active';
    case CANCEL = 'cancel';
    case STOP = 'stop';

    public static function getAll(): array
    {
       return [
        'init' => __('Init'),
        'active' => __('Active'),
        'cancel' => __('Cancelled'),
        'stop' => __('Stopped')
       ];
    }
}