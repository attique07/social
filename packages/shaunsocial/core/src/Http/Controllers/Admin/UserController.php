<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Http\Requests\User\AdminStoreUserValidate;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\User;
use Illuminate\Support\Facades\Hash;
use Packages\ShaunSocial\Core\Http\Requests\User\AdminChangePasswordValidate;
use Packages\ShaunSocial\Core\Models\Gender;
use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;
use Packages\ShaunSocial\Core\Support\Facades\Mail;

class UserController extends Controller
{
    protected $userRepository;
    
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        $this->middleware('has.permission:admin.user.manage');
    }

    public function index(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Users'),
            ],
        ];
        $title = __('Users');
        $builder = User::orderBy('id','desc')->where('is_page', false);

        $name = $request->query('name');
        if ($name) {
            $builder->where(function ($query) use ($name){
                $query->where('name', 'LIKE', '%'.$name.'%')->orWhere('user_name', 'LIKE', '%'.$name.'%');
            });
        }

        $ip = $request->query('ip');
        if ($ip) {
            $builder->where('ip','LIKE', '%'.$ip.'%');
        }

        $statusArray = [
            '' => __('All status'),
            '1' => __('Active'),
            '0' => __('InActive')
        ];

        $status = $request->query('status', '');
        if (! in_array($status, array_keys($statusArray))) {
            $status = '';
        }

        if ($status !== '') {
            $builder->where('is_active', $status);
        }

        $roles = Role::all()->pluck('name', 'id')->toArray();
        $roleId = $request->query('role_id', '');
        if (! in_array($roleId, array_keys($roles))) {
            $roleId = '';
        }

        if ($roleId !== '') {
            $builder->where('role_id', $roleId);
        }

        $users = $builder->paginate(setting('feature.item_per_page'));

        return view('shaun_core::admin.user.index', compact('breadcrumbs', 'title', 'users', 'name', 'status', 'roleId', 'statusArray', 'roles', 'ip'));
    }

    public function create($id = null)
    {
        if (empty($id)) {
            $user = new User();
            $user->timezone = setting('site.timezone');
        } else {
            $user = User::findOrFail($id);
            if ($user->isPage() || ! $user->canActionOnAdminPanel(auth()->user())) {
                abort(400);
            }
        }

        $title = empty($id) ? __('Create') : __('Edit');
        
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Users'),
                'route' => 'admin.user.index',
            ],
            [
                'title' => $title,
            ],
        ];

        $roles = Role::getListEdit(auth()->user());

        $timezones = getTimezoneList();
        $genders = Gender::getAll();
        
        return view('shaun_core::admin.user.create', compact('breadcrumbs', 'title', 'user', 'roles', 'timezones', 'genders'));
    }

    public function store(AdminStoreUserValidate $request)
    {

        $request->mergeIfMissing([
            'email_verified' => false,
            'is_active' => false,
        ]);

        $data = $request->except('id', '_token', 'password');
        if (! $request->id) {
            $data['password'] = Hash::make($request->password);
            $data['password_real'] = $request->password;
        }

        if ($request->id) {
            $user = User::findOrFail($request->id);
            $viewer = $request->user();
            if (! $user->canActionOnAdminPanel($viewer)) {
                abort(400);
            }

            $activeCurrent = $user->is_active;
            if ($request->id == config('shaun_core.core.user_root_id') && isset($data['role_id'])) {
                unset($data['role_id']);
            }

            if (isset($data['role_id'])) {
                $roles = Role::getListEdit(auth()->user())->pluck('name', 'id')->toArray();
                if (! isset($roles[$data['role_id']])) {
                    abort(400);
                }
            }

            $user->update($data);

            if ($activeCurrent != $data['is_active']) {
                if ($data['is_active']) {
                    $this->userRepository->do_active($user);
                } else {
                    $this->userRepository->do_inactive($user);
                }
            }
        } else {
            $user = $this->userRepository->store($data, true);
        }

        return redirect()->route('admin.user.index')->with([
            'admin_message_success' => $request->id ? __('User has been successfully updated.') : __('User has been created.'),
        ]);
    }

    public function store_manage(Request $request)
    {
        $viewer = $request->user();

        $ids = $request->get('ids');        
        if (! is_array($ids)) {
            abort(404);
        }
        $message = '';
        $action = $request->get('action');

        switch ($action) {
            case 'delete':
                foreach ($ids as $id) {
                    $user = User::findByField('id', $id);
                    if ($user && ! $user->isPage() && ! $user->isRoot() && $user->canActionOnAdminPanel($viewer)) {
                        $this->userRepository->delete($user);
                    }
                }
                $message = __('The selected user(s) have been deleted.');
                break;
            case 'active':
                foreach ($ids as $id) {
                    $user = User::findByField('id', $id);
                    if ($user && ! $user->isPage() && $user->canActionOnAdminPanel($viewer)) {
                        $user->update([
                            'is_active' => true
                        ]);
                    }
                }
    
                $message = __('The selected user(s) have been activated successfully.');
                break;
            default:
                abort(404);
                break;
        }
        
        return redirect()->back()->with([
            'admin_message_success' => $message,
        ]);
    }

    public function login_as(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $viewer = $request->user();
        if ($user->isPage() || ! $user->canActionOnAdminPanel($viewer)) {
            abort(400);
        }

        $token = $user->createToken('authToken')->plainTextToken;
        setAppCookie('access_token', $token, config('shaun_core.core.time_coookie_login'));

        return view('shaun_core::app', ['mustLogin' => true]);
    }

    public function change_password(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $viewer = $request->user();
        if ($user->isPage() || ! $user->canActionOnAdminPanel($viewer)) {
            abort(400);
        }

        return view('shaun_core::admin.user.change_password', [
            'id' => $id
        ]);
    }

    public function store_change_password(AdminChangePasswordValidate $request, $id)
    {
        $user = User::findOrFail($id);

        $viewer = $request->user();
        if ($user->isPage() || ! $user->canActionOnAdminPanel($viewer)) {
            abort(400);
        }
        $request->mergeIfMissing([
            'notify' => false
        ]);

        $user->update(['password' => Hash::make($request->password)]);

        if ($request->notify) {
            Mail::send('admin_change_password', $user, [
                'password' => $request->password
            ]);
        }

        $request->session()->flash('admin_message_success', __('Password has been changed.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function remove_login_all_devices(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if ($user->isPage()) {
            abort(400);
        }

        $user->doRemoveLoginAllDevices();

        return redirect()->back()->with([
            'admin_message_success' => __('Log out successfully.'),
        ]);
    }
}
