<template>
    <div class="feed-share-content bg-primary-box-color rounded-base-lg p-base-2 dark:bg-dark-web-wash">
        <div class="feed-main-info">
            <div class="activity_feed_header flex mb-base-1">
                <div class="whitespace-normal">
                    <UserName :user="post.parent.user" :truncate="false" /> 
                    {{$t('on')}}
                    <router-link :to="{name: 'post', params: {id: post.parent.id}}" class="text-inherit">{{post.parent.created_at}}</router-link>
                </div>
            </div>        
            <div class="activity_feed_content">
                <ContentHtml class="activity_feed_content_message" :content="post.parent.content" :mentions="post.parent.mentions" />
                <div class="activity_feed_content_item mt-base-2">
                    <Component v-if="postTypeComponent" :is="postTypeComponent" :post="post.parent"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import FeedPhotos from '@/components/posts/types/FeedPhotos.vue'
import FeedShareLink from '@/components/posts/types/FeedShareLink.vue'
import FeedVideo from '@/components/posts/types/FeedVideo.vue'
import FeedFiles from '@/components/posts/types/FeedFiles.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import UserName from '@/components/user/UserName.vue'

export default {
    components: { ContentHtml, UserName },
    props: ['post'],
    computed: {
        postTypeComponent(){
            switch(this.post.parent.type) {
                case 'photo':
                    return FeedPhotos
                case 'link':
                    return FeedShareLink
                case 'video':
                    return FeedVideo
                case 'file':
                    return FeedFiles
            }  
            return null
        }
    },
}
</script>