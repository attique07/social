<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Invite;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class GetInviteValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'query' => 'nullable',
            'status' => 'required|string|in:all,sent,joined',
            'page' => 'integer'
        ];
    }
    
    public function messages()
    {
        return [
            'status.required' => __('The status is required.'),
            'status.in' => __('The status is not in the list (all,sent,joined).'),
            'page.integer' => __('The page must number.')
        ];
    }
}
