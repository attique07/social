<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;
use Packages\ShaunSocial\Core\Models\User;

trait HasUser
{
    public function getUser()
    {
        return User::findByField('id', $this->user_id);
    }

    public function getUserResource()
    {
        return new UserResource($this->getUser());
    }

    public function isOwner($userId)
    {
        return $this->user_id == $userId;
    }
}
