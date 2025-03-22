<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Auth;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Validation\PasswordValidation;
use Packages\ShaunSocial\Core\Validation\UserNameValidate;

class SignupValidate extends BaseFormRequest
{
    use Utility;
    
    public function authorize()
    {
        return setting('feature.enable_signup');
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:64',
            'user_name' => [
                'required',
                'string',
                new UserNameValidate(),
                'max:128',
                function ($attribute, $userName, $fail) {
                    if (checkUsernameBan($userName)) {
                        return $fail(__('The username has been banned.'));
                    }

                    $user = User::findByField('user_name', $userName);

                    if ($user) {
                        return $fail(__('The username already exists.'));
                    }
                },
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                function ($attribute, $email, $fail) {
                    if (checkEmailBan($email)) {
                        return $fail(__('The email has been banned.'));
                    }

                    $user = User::findByField('email', $email);

                    if ($user) {
                        return $fail(__('The email already exists.'));
                    }
                },
            ],
            'password' => ['required', 'string', new PasswordValidation()],
            'ref_code' => ['nullable', 'max:255']
        ];
    }

    public function withValidator($validator)
    {
        if (setting('spam.signup_enable_recapcha')) {
            if (! $validator->fails()) {
                $validator->after(function ($validator) {
                    $result = $this->validateSpam($this->request->all());
                    if (! $result['status']) {
                        throw new MessageHttpException($result['message']); 
                    }
                });
            }
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'name.required' => __('The name is required.'),
            'name.max' => __('The name must not be greater than 64 characters.'),
            'user_name.required' => __('The username is required.'),
            'user_name.max' => __('The username must not be greater than 128 characters.'),
            'email.required' => __('The email is required.'),
            'email.email' => __('The email should be valid.'),
            'email.max' => __('The email must not be greater than 255 characters.'),
            'password.required' => __('The password is required.'),
            'ref_code.max' => __('The reference code must not be greater than 255 characters.'),
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot sign up this site.'));
    }
}
