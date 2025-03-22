<?php


namespace Packages\ShaunSocial\Wallet\Wallet;

use Packages\ShaunSocial\Wallet\Models\WalletPaymentType;

class WalletTransactionTypePayment extends WalletTransactionTypeBase
{
    public function getDescription($transaction)
    {
        $class = WalletPaymentType::getClassByType($transaction->type_extra);
        return app($class)->getDescription($transaction);
    }

    public function getName()
    {
        return __('Payment');
    }
}
