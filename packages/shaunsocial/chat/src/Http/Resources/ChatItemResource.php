<?php


namespace Packages\ShaunSocial\Chat\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ChatItemResource extends JsonResource
{
    public function toArray($request)
    {        
        $subject = $this->getSubject();
        return [
            'id' => $this->id,
            'message_id' => $this->message_id, 
            'subject' => $subject ? $this->getSubjectResource()->toArray($request) : null,
        ];
    }
}
