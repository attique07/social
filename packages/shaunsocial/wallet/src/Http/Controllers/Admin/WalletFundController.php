<?php

namespace Packages\ShaunSocial\Wallet\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Validation\AmountValidation;
use Packages\ShaunSocial\Wallet\Repositories\Api\WalletRepository;

class WalletFundController extends Controller
{
    use Utility;

    protected $walletRepository;

    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;

        $this->middleware('has.permission:admin.wallet.mass_funds');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Transfer Mass Funds'),
            ],
        ];
        $title = __('Transfer Mass Funds');
    
        return view('shaun_wallet::admin.fund.index', compact('breadcrumbs', 'title'));
    }

    public function send(Request $request)
    {
        $request->validate(
            [
                'id' => 'required|alpha_num',
                'amount' => ['required', new AmountValidation()]
            ],
            [
                'id.required' => __('The user is required.'),
                'amount.required' => __('The amount is required.'),
            ]
        );

        User::findOrFail($request->id);

        $this->walletRepository->send_mass_fund($request->only([
            'id', 'amount', 'notify'
        ]), $request->user());

        return redirect()->route('admin.wallet.fund.index')->with([
            'admin_message_success' => __('Successfully sent!')
        ]);
    }
}
