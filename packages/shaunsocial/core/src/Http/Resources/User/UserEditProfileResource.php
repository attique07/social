<?php


namespace Packages\ShaunSocial\Core\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Models\Gender;

class UserEditProfileResource extends JsonResource
{
    protected $preserveKeys = true;
    
    public function toArray($request)
    {        
        return [
            'name' => $this->getName(),
            'birthday' => $this->birthday,
            'links' => $this->links ?? '',
            'address' => $this->location,
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'zip_code' => $this->zip_code,
            'bio' => $this->bio,
            'about' => $this->about,
            'gender_id' => $this->gender_id,
            'birthday' => $this->birthday,
            'genders' => Gender::getAll(),
            'avatar' => $this->getAvatar(),
            'timezone' => $this->timezone,
            'timezones' => getTimezoneList(),
        ];
    }
}
