<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Auth\LoginValidate;
use Packages\ShaunSocial\Core\Http\Requests\Auth\LoginWithCodeValidate;
use Packages\ShaunSocial\Core\Http\Requests\Auth\SignupValidate;
use Packages\ShaunSocial\Core\Models\CodeVerify;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;
use Packages\ShaunSocial\Core\Repositories\Api\InviteRepository;
use Packages\ShaunSocial\Core\Traits\Utility;

class AuthController extends ApiController
{
    use Utility;
    
    protected $userRepository;
    protected $inviteRepository;
    
    public function __construct(UserRepository $userRepository, InviteRepository $inviteRepository)
    {
        $this->userRepository = $userRepository;
        $this->inviteRepository = $inviteRepository;
    }
    
    protected function loginUser($user, $request)
    {
        if (! $user->is_active) {
            return $this->messageWithCodeResponse(__('Your account is pending approval.'), 'inactive', 400);
        }

        $result = $this->loginUserBase($user, $request);

        return $this->successResponse($result);
    }

    public function login(LoginValidate $request)
    {
        $data = $request->only(['email', 'password']);

        if (! Auth::validate($data)) {
            return $this->errorMessageRespone(__('Your email or password was incorrect.'));
        }

        $user = User::findByField('email', $data['email']);
        return $this->loginUser($user, $request);
    }

    public function login_with_code(LoginWithCodeValidate $request)
    {
        $codeVerify = CodeVerify::where('type', 'login')->where('code', $request->code)->first();
        $user = User::findByField('id', $codeVerify->user_id);
        $codeVerify->delete();
        
        return $this->loginUser($user, $request);
    }

    public function signup(SignupValidate $request)
    {
        $data = $request->all();
        
        $user = $this->userRepository->store([
            'name' => $data['name'],
            'email' => $data['email'],
            'user_name' => $data['user_name'],
            'password' => Hash::make($data['password']),
        ]);

        if ($request->ref_code) {
            $this->inviteRepository->check($request->ref_code, $user);
        }

        return $this->loginUser($user, $request);
    }

    public function logout(Request $request)
    {
        if ($request->headers->get('SupportCookie')) {
            setAppCookie('access_token', null, -1);
        }
                
        if ($request->has('fcm_token')){
            $viewer = $request->user();
            $viewer->deleteFcmToken($request->fcm_token);
        }

        return $this->successResponse();
    }
}
