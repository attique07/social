<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Packages\ShaunSocial\Advertising\Models\Advertising;
use Packages\ShaunSocial\Advertising\Models\AdvertisingStatistic;
use Packages\ShaunSocial\Core\Http\Resources\Post\PostResource;
use Packages\ShaunSocial\Core\Notification\Post\PostCommentNotification;
use Packages\ShaunSocial\Core\Notification\Post\PostCommentOfCommentNotification;
use Packages\ShaunSocial\Core\Notification\Post\PostLikeNotification;
use Packages\ShaunSocial\Core\Notification\Post\PostMentionCommentNotification;
use Packages\ShaunSocial\Core\Notification\Post\PostMentionLikeNotification;
use Packages\ShaunSocial\Core\Notification\Post\PostMentionNotification;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasComment;
use Packages\ShaunSocial\Core\Traits\HasLike;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Core\Traits\HasMention;
use Packages\ShaunSocial\Core\Traits\HasCacheShortUserHome;
use Packages\ShaunSocial\Core\Traits\HasBookmark;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Traits\HasHistory;
use Packages\ShaunSocial\Core\Traits\HasNotificationStop;
use Packages\ShaunSocial\Core\Traits\HasReport;
use Packages\ShaunSocial\Core\Traits\HasShareEmail;
use Packages\ShaunSocial\Core\Traits\HasUserHashtag;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\Core\Traits\IsSubjectNotification;
use Packages\ShaunSocial\UserPage\Models\UserPageStatistic;

class Post extends Model
{
    use HasLike, IsSubject, HasUser, HasComment, HasCachePagination, HasCacheQueryFields, HasMention, HasCacheShortUserHome, HasBookmark, 
        IsSubjectNotification, HasNotificationStop, HasUserList, HasReport, HasShareEmail, HasHistory, HasUserHashtag;

    protected $fillable = [
        'user_id',
        'type',
        'content',
        'hashtags',
        'parent_id',
        'content_search',
        'user_privacy',
        'is_ads',
        'show'
    ];

    protected $isAds = false;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $casts = [
        'is_ads' => 'boolean',
        'show' => 'boolean',
    ];

    protected $mentionField = 'content';

    protected $items = null;

    protected $hashtagList = null;

    public function getListCachePagination()
    {
        $hashtags = $this->getHashtags();
        $result = [
            'user_profile_'.$this->user_id,
            'user_profile_media_'.$this->user_id
        ];

        if ($hashtags) {
            foreach ($hashtags as $hashtag) {
                $result[] = 'hashtag_'.$hashtag->name;
            }
        }

        return $result;
    }

    public function getListFieldPagination()
    {
        return [
            'like_count',
            'comment_count',
            'content',
            'is_ads',
            'show'
        ];
    }

    public function getItems()
    {
        if ($this->type == 'text') {
            return [];
        }

        if (! $this->items) {
            $items = PostItem::findByField('post_id', $this->id, true);
            if ($items) {
                $items = $items->sortBy(function ($item) {
                    return $item->order;
                });
            }

            $this->items = $items;
        }

        return $this->items;
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function getHashtags()
    {
        if (! $this->hashtagList) {
            if ($this->hashtags) {
                $collection = Str::of($this->hashtags)->explode(' ');
                $this->hashtagList = $collection->map(function ($value, $key) {
                    return Hashtag::findByField('id', $value);
                });
            }
        }
        
        return $this->hashtagList;
    }

    public function setHashtags($hashtagList)
    {
        $this->hashtagList = $hashtagList;
    }

    public function canDelete($userId)
    {
        return $this->isOwner($userId) && ! $this->is_ads;
    }

    public function canEdit($userId)
    {
        return $this->isOwner($userId);
    }

    public function canShare($userId)
    {
        return $this->canView($userId) && !$this->parent_id;
    }

    public static function getTypes()
    {
        return [
            'text',
            'photo',
            'link',
            'share',
            'video',
            'file'
        ];
    }

    public function getParent()
    {
        if ($this->parent_id) {
            return self::findByField('id', $this->parent_id);
        }

        return null;
    }

    public function getDataForPostHome()
    {
        $data = $this->toArray();
        unset($data['id']);
        unset($data['created_at']);
        unset($data['updated_at']);
        $data['post_id'] = $this->id;

        return $data;
    }

    public function canView($viewerId)
    {
        return $this->getUser()->canView($viewerId);
    }

    public function canLike($viewerId)
    {
        return $this->canView($viewerId);
    }

    public function canBookmark($viewerId)
    {
        return $this->canView($viewerId);
    }

    public function canComment($viewerId)
    {
        return $this->canView($viewerId);
    }

    public function canViewComment($viewerId)
    {
        return $this->canView($viewerId);
    }

    public function getHref()
    {
        return route('web.post.detail',[
            'id' => $this->id
        ]);
    }

    public function getTitle()
    {
        return __('Post');
    }

    public function sendCommentNotification($viewer, $comment)
    {
        if ($viewer->id != $this->user_id && $this->getUser()->checkNotifySetting('comment')) {
            Notification::send($this->getUser(), $viewer, PostCommentNotification::class, $comment);            
        }

        $users = $this->getMentionUsers($viewer);
        foreach ($users as $user) {
            if ($viewer->id != $user->id && $user->checkNotifySetting('comment')) {
                Notification::send($user, $viewer, PostMentionCommentNotification::class, $comment);
            }
        }

        if ($viewer->id == $this->user_id) {
            $users = Distinct::where('user_hash', Distinct::getUserHash($comment->getDistinctValue(true)))->where('updated_at', '>', now()->subDays(config('shaun_core.notify.limit_day_owner_send')))->orderBy('updated_at', 'DESC')->limit(config('shaun_core.notify.limit_number_owner_send'))->get();
            if ($users) {
                $users = $this->filterUserList($users, $viewer);
            }

            if ($users) {
                $users->each(function($user) use ($viewer, $comment){
                    Notification::send($user->getUser(), $viewer, PostCommentOfCommentNotification::class, $comment);
                });              
            }
        }
    }

    public function sendLikeNotification($viewer)
    {
        if ($viewer->id != $this->user_id && $this->getUser()->checkNotifySetting('like')) {
            Notification::send($this->getUser(), $viewer, PostLikeNotification::class, $this);
        }

        $users = $this->getMentionUsers($viewer);
        foreach ($users as $user) {
            if ($viewer->id != $user->id && $user->checkNotifySetting('like')) {
                Notification::send($user, $viewer, PostMentionLikeNotification::class, $this);
            }
        }
    }

    public function deleteLikeNotification($viewer)
    {
        UserNotification::deleteFromAndSubject($viewer,$this,PostLikeNotification::class);
        UserNotification::deleteFromAndSubject($viewer,$this,PostMentionLikeNotification::class);
    }
    
    static function getMentionNotificationClass()
    {
        return PostMentionNotification::class;
    }

    public static function getResourceClass()
    {
        return PostResource::class;
    }

    public function addHashtag($hashtags) 
    {
        $currentHashtags = $this->getHashtags();
        if (! $currentHashtags) {
            $currentHashtags = collect();
        }

        $arrayIds = $currentHashtags->pluck('id')->toArray();
        foreach ($hashtags as $hashtag) {
            $item = $currentHashtags->first(function ($value, $key) use ($hashtag) {
                return $value->name == $hashtag;
            });

            if (! $item) {
                
                $item = Hashtag::firstOrCreate([
                    'name' => $hashtag,
                ]);

                $item->increment('post_count');
            }

            $arrayIds[] = $item->id;
        }
        
        $this->update(['hashtags' => Arr::join(array_unique($arrayIds), ' ')]);
    }

    protected static function booted()
    {
        parent::booted();

        static::deleted(function ($post) {
            $hashtags = $post->getHashtags();
            if ($hashtags) {
                foreach ($hashtags as $hashtag) {
                    $hashtag->decrement('post_count');
                }
            }

            $items = $post->getItems();
            foreach ($items as $item) {
                $item->delete();
            }

            HashtagTrending::where('post_id',$post->id)->delete();

            if (!$post->parent_id) {
                self::where('parent_id', $post->id)->get()->each(function ($post) {
                    $post->delete();
                });
            }

            //delete post home
            $postHome = PostHome::where('post_id', $post->id)->first();
            if ($postHome) {
                $postHome->delete();
            }            
        });

        static::saving(function ($post) {
            if ($post->id) {
                if ($post->content != $post->getOriginal('content')) {
                    $content = $post->getMentionContent(null);
                    $mentions = getMentionsFromContent($content);
                    foreach ($mentions as $mention) {
                        $content = str_replace($mention, '', $content);
                    }
                    $post->content_search = $content;
                }
            } else {
                $post->content_search = $post->content;
            }

            if ($post->content) {
                if ($post->id && ($post->content == $post->getOriginal('content') || ! $post->getOriginal('content'))) {
                    return;
                }
                $hashtags = getHashtagsFromContent($post->content);
                $hashtagsKeep = [];
                $hashtagArray = [];
                $items = [];
                if ($post->id && $post->hashtags) {
                    $hashtagsCurrent = Str::of($post->hashtags)->explode(' ');
                    $hashtagsKeep = array_intersect($hashtags, $hashtagsCurrent->toArray());
                    $hashtagsDelete = array_diff($hashtagsCurrent->toArray(), $hashtags);
                    if (count($hashtagsDelete)){
                        $deleteIds = [];
                        foreach ($hashtagsDelete as $hashtagDelete) {
                            $item = Hashtag::findByField('id', $hashtagDelete);
                            if ($item) {
                                $item->decrement('post_count');
                                $deleteIds[] = $item->id;
                            }
                        }
                        
                        HashtagTrending::where('post_id', $post->id)->whereIn('hashtag_id', $deleteIds)->delete();
                    }                    
                }
                foreach ($hashtags as $hashtag) {
                    $hashtag = str_replace('#', '', $hashtag);
                    
                    $item = Hashtag::firstOrCreate([
                        'name' => $hashtag,
                    ]);
                    if (! in_array($hashtag, $hashtagsKeep)) {
                        $item->increment('post_count');
                    }

                    $hashtagArray[] = $item->id;
                    $items[] = $item;
                }
                $post->hashtags = Arr::join(array_unique($hashtagArray), ' ');
                $post->setHashtags(collect($items));
                
                if ($post->id && count($items)) {
                    foreach ($items as $item) {
                        HashtagTrending::create([
                            'name' => $item->name,
                            'hashtag_id' => $item->id,
                            'is_active' => $item->is_active,
                            'post_id' => $post->id
                        ]);
                    }
                }
            }
        });

        static::updated(function ($post) {            
            //check update for post home
            $check = false;
            $fieldChange = ['content', 'hashtags', 'like_count', 'comment_count', 'privacy', 'show'];
            foreach ($fieldChange as $field) {
                if ($post->{$field} != $post->getOriginal($field)) {
                    $check = true;
                    break;
                }
            }
            if ($check)
            {
                $data = $post->getDataForPostHome();
                $postHome = PostHome::where('post_id', $post->id)->first();
                if ($postHome) {
                    $postHome->update($data);
                }
            }
        });

        static::created(function ($post) {
            $hashtags = $post->getHashtags();

            if ($hashtags) {
                foreach ($hashtags as $hashtag) {
                    HashtagTrending::create([
                        'name' => $hashtag->name,
                        'hashtag_id' => $hashtag->id,
                        'is_active' => $hashtag->is_active,
                        'post_id' => $post->id
                    ]);
                }
            }

            //add to post home
            $data = $post->getDataForPostHome();
            PostHome::create($data);
        });
    }

    public static function updatePrivacy($userId, $privacy)
    {
        self::where('user_id', $userId)->update([
            'user_privacy' => $privacy
        ]);
    }

    public function getOgImage()
    {
        $items = $this->getItems();
        if ($items && count($items)) {
            $item = $items->first();
            return $item->getOgImage();
        }

        return '';
    }

    public function addStatistic($type, $viewer)
    {
        $owner = $this->getUser();
        if ($owner) {
            if (! $viewer || $viewer->id != $owner->id) {
                $owner->addPageStatistic($type, $viewer, $this);
            }
        }
    }

    public function getHistoryContentFirst()
    {
        switch ($this->type) {
            case 'photo':
                return 'Added photo(s) to this post.';
                break;
            case 'video':
                return 'Added a video to this post.';
                break;
            case 'link':
                return 'Added a link to this post.';
                break;
        }

        return '';
    }

    public function addAdvertisingStatistic($type, $viewerId, $ip = '')
    {
        if ($this->is_ads && $viewerId != $this->user_id) {
            $advertising = Advertising::findByField('post_id', $this->id);
            if ($advertising && $advertising->canAddStatistic()) {
                AdvertisingStatistic::add([
                    'type' => $type,
                    'user_id' => $viewerId,
                    'ip' => $ip,
                    'advertising_id' => $advertising->id
                ]);
                $advertising->addCheckDoneForReport();
            }
        }
    }

    public function canBoot($viewerId) {
        if ($this->is_ads) {
            return false;
        }

        if ($this->isOwner($viewerId)) {
            return true;
        }

        $user = $this->getUser();
        if ($user->isPage()) {
            $admin = $user->getAdminPage($viewerId);
            if ($admin) {
                return true;
            }
        }
    
        return false;
    }

    public function setIsAdvertising($isAds)
    {
        $this->isAds = $isAds;
    }

    public function isAdvertising()
    {
        return $this->isAds;
    }
}
