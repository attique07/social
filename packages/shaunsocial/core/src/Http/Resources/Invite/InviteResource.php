<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Invite;

use Illuminate\Http\Resources\Json\JsonResource;

class InviteResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');

        return [
            'email' => $this->email,
            'status' => $this->getStatus(),
            'sent_at' => $this->updated_at->setTimezone($timezone)->diffForHumans(),
            'sent_at_timestamp' => $this->updated_at->timestamp            
        ];
    }
}
