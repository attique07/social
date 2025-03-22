<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Http\Resources\Post\ItemResource;
use Packages\ShaunSocial\Core\Http\Resources\Post\PostResource;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\PostItem;
use Packages\ShaunSocial\Core\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingStatisticType;
use Packages\ShaunSocial\Core\Jobs\PostQueueJob;
use Packages\ShaunSocial\Core\Models\History;
use Packages\ShaunSocial\Core\Models\PostHome;
use Packages\ShaunSocial\Core\Models\PostQueue;
use Packages\ShaunSocial\Core\Models\UserFollowNotificationCron;
use Packages\ShaunSocial\Core\Models\UserHashtagSuggest;
use Packages\ShaunSocial\Core\Notification\Post\PostShareNotification;
use Packages\ShaunSocial\Core\Notification\Post\PostUserFollowNotification;
use Packages\ShaunSocial\Core\Notification\Post\PostVideoNotification;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Support\Facades\Utility;
use Packages\ShaunSocial\Core\Traits\HasUserList;
use Packages\ShaunSocial\Advertising\Traits\Utility as AdvertisingUtility;

class PostRepository
{
    use HasUserList, AdvertisingUtility;

    public function upload_photo($file, $viewerId)
    {
        $storageFile = File::storePhoto($file, [
            'parent_type' => 'post_item',
            'user_id' => $viewerId,
        ]);

        $postItem = PostItem::create([
            'user_id' => $viewerId,
            'subject_type' => $storageFile->getSubjectType(),
            'subject_id' => $storageFile->id,
        ]);

        $storageFile->update([
            'parent_id' => $postItem->id,
        ]);

        $postItem->setSubject($storageFile);

        return new ItemResource($postItem);
    }

    public function filterPostList($posts, $viewer)
    {
        return $this->filterUserList($posts, $viewer, 'user_id', ['privacy', 'active']);
    }

    public function store_queue($data, $viewer)
    {
        $post = PostQueue::create([
            'user_id' => $viewer->id,
            'type' => $data['type'],
            'content' => $data['content']
        ]);

        foreach ($data['items'] as $key => $item) {
            $postItem = PostItem::findByField('id', $item);
            $postItem->update([
                'post_queue_id' => $post->id,
                'order' => $key,
            ]);
        }

        if (config('shaun_core.core.queue')) {
            dispatch((new PostQueueJob($post))->onQueue(config('shaun_core.queue.post_queue')));
        }

        return [
            'queue' => true
        ];
    }

    public function store_now($data, $viewer)
    {
        $data['user_id'] = $viewer->id;
        $data['privacy'] = $viewer->privacy;
        $post = Post::create($data);

        if (count($data['items'])) {
            $postItems = [];
            foreach ($data['items'] as $key => $item) {
                $postItem = PostItem::findByField('id', $item);
                $postItem->update([
                    'post_id' => $post->id,
                    'post_queue_id' => 0,
                    'order' => $key,
                ]);
                $postItems[] = $postItem;
            }
            $post->setItems(collect($postItems));
        }        

        // send notify
        $post->sendMentionNotification($post->getUSer());
        
        //Send notify to follower
        if ($viewer->checkUserEnableFollowNotification()) {
            UserFollowNotificationCron::create([
                'user_id' => $viewer->id,
                'subject_type' => $post->getSubjectType(),
                'subject_id' => $post->id,
                'class'=> PostUserFollowNotification::class
            ]);
        }

        Post::setCacheQueryFieldsResult('id', $post->id, $post);

        //Send notify to parent post
        if ($post->type == 'share') {
            $parentPost = Post::findByField('id', $post->parent_id);
            if ($parentPost->user_id != $post->user_id) {
                if ($parentPost->getUser()->checkNotifySetting('share')) {
                    Notification::send($parentPost->getUser(), $viewer, PostShareNotification::class, $parentPost);
                }

                //add statistic for page
                $parentPost->getUser()->addPageStatistic('post_share', $viewer, $post, false);
            }
        }

        return new PostResource($post);
    }

    public function store($data, $viewer)
    {
        if (count($data['items'])) {
            $checkQueue = false;
            foreach ($data['items'] as $key => $item) {
                $postItem = PostItem::findByField('id', $item);
                if ($postItem->checkNeedQueue()) {
                    $checkQueue = true;
                    break;    
                }                
            }

            if ($checkQueue) {
                return $this->store_queue($data, $viewer);
            }
        }
        
        return $this->store_now($data, $viewer);
    }

    public function profile($userId, $page, $viewer)
    {
        $posts = Post::getCachePagination('user_profile_'.$userId, Post::where('user_id', $userId)->orderBy('id', 'DESC'), $page);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });
        return PostResource::collection($posts);
    }

    public function profile_media($userId, $page, $viewer)
    {
        $posts = Post::getCachePagination('user_profile_media_'.$userId, Post::where('user_id', $userId)->whereIn('type', array('photo', 'video'))->orderBy('id', 'DESC'), $page);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });
        return PostResource::collection($posts);
    }
    
    public function addConditionUserPrivacy($builder, $viewer)
    {
        if ($viewer) {
            if ($viewer->following_count) {
                if ($viewer->following_count > config('shaun_core.follow.user.max_query_join')) {
                    $builder->where(function($query) use ($viewer) {
                        $query->orWhere('user_id', $viewer->id);
                        $query->orWhere('user_privacy', config('shaun_core.privacy.user.everyone'));
                        $query->orWhere(function($query) use ($viewer) {
                            $query->whereIn('user_id', function($select) use ($viewer) {
                                $select->from('user_follows')
                                    ->select('follower_id')
                                    ->where('user_id', $viewer->id);
                            });
                            $query->where('user_privacy','!=',config('shaun_core.privacy.user.only_me'));
                        });
                    });
                } else {
                    $userFollowers = $viewer->getFollows()->pluck('follower_id')->toArray();
                    $builder->where(function($query) use ($userFollowers, $viewer) {
                        $query->orWhere('user_id', $viewer->id);
                        $query->orWhere('user_privacy', config('shaun_core.privacy.user.everyone'));
                        $query->orWhere(function($query) use ($userFollowers) {
                            $query->whereIn('user_id',$userFollowers);
                            $query->where('user_privacy','!=',config('shaun_core.privacy.user.only_me'));
                        });
                    });                
                }
            } else {
                $builder->where(function($query) use ($viewer) {
                    $query->orWhere('user_id', $viewer->id);
                    $query->orWhere('user_privacy', config('shaun_core.privacy.user.everyone'));
                }); 
            }
        } else {
            $builder->where('user_privacy', config('shaun_core.privacy.user.everyone'));
        }
    }

    public function home($viewer, $page, $key)
    {
        $builder = PostHome::orderBy('id', 'DESC')->where('show', true);
        $builder->where(function($query) use ($viewer) {
            if (setting('site.home_feed_type')) {
                $query->where('user_id', $viewer->id);
                if ($viewer->following_count) {
                    if ($viewer->following_count > config('shaun_core.follow.user.max_query_join')) {
                        $query->orWhere(function($query) use ($viewer) {
                            $query->whereIn('user_id', function($select) use ($viewer) {
                                $select->from('user_follows')
                                    ->select('follower_id')
                                    ->where('user_id', $viewer->id);
                            });
                            $query->where('user_privacy','!=',config('shaun_core.privacy.user.only_me'));
                        });
                    } else {                    
                        $userFollowers = $viewer->getFollows()->pluck('follower_id')->toArray();
                        $query->orWhere(function($select) use ($userFollowers) {
                            $select->whereIn('user_id',$userFollowers);
                            $select->where('user_privacy','!=',config('shaun_core.privacy.user.only_me'));
                        });
                    }
                }
                
                if ($viewer->hashtag_follow_count) {
                    $hashtagFollowers = $viewer->getHashtagFollows()->pluck('hashtag_id')->join(' ');
                    $query->orWhere(function($query) use ($hashtagFollowers) {
                        $query->whereFullText('hashtags',$hashtagFollowers);
                        $query->where('user_privacy',config('shaun_core.privacy.user.everyone'));
                    });
                }
            } else {
                $query->where('user_id', $viewer->id)->orWhere('user_privacy',config('shaun_core.privacy.user.everyone'));
            }
        });
        
        $results = Cache::remember('post_user_home_'.$viewer->id.'_'.$page, config('shaun_core.cache.time.short'), function () use ($builder, $page) {            
            return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
        });

        $posts = collect();
        foreach ($results as $result) {
            $post = Post::findByField('id', $result->post_id);
            if ($post) {
                $posts->push($post);
            }            
        }
        $posts = $this->filterPostList($posts, $viewer);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });
        
        $posts = $this->addAdvertisings($posts, $page, $viewer, $key);

        return PostResource::collection($posts);
    }

    public function delete($id)
    {
        $post = Post::findByField('id', $id);
        
        $post->delete();
    }

    public function get($id, $viewer, $ip)
    {
        $post = Post::findByField('id', $id);
        $post->addStatistic('post_reach', $viewer);
        //add ads statistic
        $post->addAdvertisingStatistic(AdvertisingStatisticType::CLICK, $viewer ? $viewer->id : 0, $ip);
        if ($viewer) {
            $hashtags = $post->getHashtags();

            if ($hashtags) {
                foreach ($hashtags as $hashtag) {
                    if (! $viewer->getHashtagFollow($hashtag->name)) {
                        UserHashtagSuggest::create([
                            'name' => $hashtag->name,
                            'hashtag_id' => $hashtag->id,
                            'is_active' => $hashtag->is_active,
                            'user_id' => $viewer->id
                        ]);
                    }
                }
                Cache::forget('user_hashtag_suggest_'.$viewer->id);
            }
        }        

        return new PostResource($post);
    }

    public function fetch_link($url, $viewerId)
    {
        $link = Utility::parseLink($url, $viewerId);

        if ($link) {
            $postItem = PostItem::create([
                'user_id' => $viewerId,
                'subject_type' => $link->getSubjectType(),
                'subject_id' => $link->id,
            ]);

            $postItem->setSubject($link);

            return new ItemResource($postItem);
        }

        return null;
    }

    public function delete_item($itemId)
    {
        $item = PostItem::findByField('id', $itemId);
        $item->delete();
    }

    public function hashtag($hashtag, $page, $viewer)
    {
        $posts = Post::getCachePagination('hashtag_'.$hashtag->name, Post::whereFullText('hashtags', $hashtag->id)->orderBy('id', 'DESC'), $page);

        $posts = $this->filterPostList($posts, $viewer);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });

        return PostResource::collection($posts);
    }

    public function getConditionForTrendding()
    {
        $builder = PostHome::addSelect(DB::raw('(total_count*'.config('shaun_core.trending_point.reach').' - DATEDIFF(CURRENT_DATE,created_at)*'.config('shaun_core.trending_point.diff_day').') as trending_order, post_id'))->where('show', true);
        $builder->orderBy('trending_order', 'DESC')->orderBy('id', 'DESC');

        return $builder;
    }

    public function discovery($viewer, $page, $key)
    {
        $builder = $this->getConditionForTrendding();

        $this->addConditionUserPrivacy($builder, $viewer);
        $userId = $viewer ? $viewer->id : 0;

        $results = Cache::remember('post_user_discovery_'.$userId.'_'.$page, config('shaun_core.cache.time.short'), function () use ($builder, $page) {            
            return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
        });

        $posts = collect();
        foreach ($results as $result) {
            $post = Post::findByField('id', $result->post_id);
            if ($post) {
                $posts->push(Post::findByField('id', $result->post_id));
            }
        }
        $posts = $this->filterPostList($posts, $viewer);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });

        $posts = $this->addAdvertisings($posts, $page, $viewer, $key);
        
        return PostResource::collection($posts);
    }

    public function watch($viewer, $page, $key)
    {
        $builder = $this->getConditionForTrendding();
        $builder->where('type', 'video');

        $this->addConditionUserPrivacy($builder, $viewer);
        $userId = $viewer ? $viewer->id : 0;

        $results = Cache::remember('post_user_watch_'.$userId.'_'.$page, config('shaun_core.cache.time.short'), function () use ($builder, $page) {            
            return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
        });

        $posts = collect();
        foreach ($results as $result) {
            $post = Post::findByField('id', $result->post_id);
            if ($post) {
                $posts->push(Post::findByField('id', $result->post_id));
            }
        }
        $posts = $this->filterPostList($posts, $viewer);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });

        $posts = $this->addAdvertisings($posts, $page, $viewer, $key);

        return PostResource::collection($posts);
    }

    public function media($viewer, $page)
    {
        $builder = $this->getConditionForTrendding();
        $builder->whereIn('type', array('photo', 'video'));
        
        $this->addConditionUserPrivacy($builder, $viewer);
        $userId = $viewer ? $viewer->id : 0;

        $results = Cache::remember('post_user_media_'.$userId.'_'.$page, config('shaun_core.cache.time.short'), function () use ($builder, $page) {            
            return $builder->limit(setting('feature.item_per_page'))->offset(($page - 1) * setting('feature.item_per_page'))->get();
        });

        $posts = collect();
        foreach ($results as $result) {
            $post = Post::findByField('id', $result->post_id);
            if ($post) {
                $posts->push(Post::findByField('id', $result->post_id));
            }
        }

        $posts = $this->filterPostList($posts, $viewer);
        $posts->each(function($post) use ($viewer){
            $post->addStatistic('post_reach', $viewer);
        });

        return PostResource::collection($posts);
    }

    public function store_edit($id, $content, $viewer)
    {
        $post = Post::findByField('id', $id);
        $mentions = $post->mentions;

        History::create([
            'user_id' => $viewer->id,
            'content' => $post->getMentionContent(null),
            'mentions' => $post->mentions,
            'subject_type' => $post->getSubjectType(),
            'subject_id' => $post->id,
        ]);

        $post->update([
            'content' => $content,
            'is_edited' => true
        ]);
        
        $post->updateMention();
        $post->sendMentionNotificationWhenEdit($mentions);

        return new PostResource($post);
    }

    public function upload_video($file , $isConverted, $convertNow, $viewerId)
    {
        $result = Utility::storeVideo($file, $viewerId, $isConverted, $convertNow);
        if ($result['status']) {
            $video = $result['video'];
            $postItem = PostItem::create([
                'user_id' => $viewerId,
                'subject_type' => $video->getSubjectType(),
                'subject_id' => $video->id,
                'has_queue' => ! $video->is_converted
            ]);

            $postItem->setSubject($video);

            return ['status' => true, 'item' => new ItemResource($postItem)];
        }
        
        return $result;
    }

    public function run_queue($postQueue)
    {
        $items = $postQueue->getItems();
        $items->each(function($item) {
            $item->runQueue();
        });

        $item = $items->first(function ($item, $key) {
            return $item->checkNeedQueue();
        });

        if ($item) {
            $postQueue->delete();
            return;
        }
        $data = [
            'content' => $postQueue->content,
            'type' => $postQueue->type,
            'items' => $items->pluck('id')->all()
        ];
        
        $postResource = $this->store_now($data, $postQueue->getUser());
        $post = Post::findByField('id', $postResource['id']);            
        //send notify
        Notification::send($postQueue->getUser(), $postQueue->getUser(), PostVideoNotification::class, $post, ['is_system' => true], 'shaun_core', false);
        $postQueue->setCanDeleteItem(false);
        $postQueue->delete();
    }

    public function upload_file($file, $viewerId)
    {
        $storageFile = File::store($file, [
            'parent_type' => 'message_item',
            'user_id' => $viewerId,
            'extension' => $file->getClientOriginalExtension(),
			'name' => $file->getClientOriginalName()
        ]);

        $postItem = PostItem::create([
            'user_id' => $viewerId,
            'subject_type' => $storageFile->getSubjectType(),
            'subject_id' => $storageFile->id,
        ]);

        $storageFile->update([
            'parent_id' => $postItem->id
        ]);

        $postItem->setSubject($storageFile);

        return new ItemResource($postItem);
    }
}
