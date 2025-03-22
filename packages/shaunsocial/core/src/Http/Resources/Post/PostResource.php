<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Post;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Traits\Utility;

class PostResource extends JsonResource
{
    use Utility;

    public function toArray($request)
    {
        $viewer = $request->user();
        $viewerId = $viewer ? $viewer->id : 0;
        $isAdmin = $viewer ? $viewer->isModerator() : false;
        $parent = $this->getParent();
        $timezone = $viewer ? $viewer->timezone : setting('site.timezone');

        return [
            'id' => $this->id,
            'type' => $this->type,
            'content' => $this->makeContentHtml($this->getMentionContent($viewer)),
            'items' => ItemResource::collection($this->getItems()),
            'like_count' => $this->like_count,
            'comment_count' => $this->comment_count,
            'created_at' => $this->created_at->setTimezone($timezone)->diffForHumans(),
            'user' => $this->getUserResource(),
            'is_liked' => $this->getLike($viewerId) ? true : false,
            'mentions' => $this->getMentionUsersResource($viewer),
            'is_bookmarked' => $this->getBookmark($viewerId) ? true : false,
            'parent' => $parent ? new PostResource($parent) : null,
            'enable_notify' => $this->getNotificationStop($viewerId) ? false : true,
            'canDelete' => $this->canDelete($viewerId) || $isAdmin,
            'href' => $this->getHref(),
            'canEdit' => $this->canEdit($viewerId) || $isAdmin,
            'isEdited' => $this->is_edited ? true : false,
            'created_at_full' => $this->created_at->setTimezone($timezone)->toDateTimeString(),
            'create_at_timestamp' => $this->created_at->timestamp,
            'is_ads' => setting('shaun_advertising.enable') && $this->isAdvertising(),
            'canBoot' => setting('shaun_advertising.enable') && $this->canBoot($viewerId)
        ];
    }
}
