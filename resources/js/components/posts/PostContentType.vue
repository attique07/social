<template>
    <template v-if="post.type === 'video'">
        <Component v-if="postTypeComponent" :is="postTypeComponent" :post="post" :autoPlay="autoPlay"/>
    </template>
    <template v-else>
        <Component v-if="postTypeComponent" :is="postTypeComponent" :post="post" />
    </template>
</template>

<script>
import FeedPhotos from '@/components/posts/types/FeedPhotos.vue'
import FeedShareLink from '@/components/posts/types/FeedShareLink.vue'
import FeedSharePost from '@/components/posts/types/FeedSharePost.vue'
import FeedVideo from '@/components/posts/types/FeedVideo.vue'
import FeedFiles from '@/components/posts/types/FeedFiles.vue'

export default {
    props: {
        post: {
            type: Object,
            default: null
        },
        autoPlay:{
            type: Boolean,
            default: false
        }
    },
    computed: {
        postTypeComponent(){
            switch(this.post.type) {
                case 'photo':
                    return FeedPhotos
                case 'link':
                    return FeedShareLink
                case 'share':
                    return FeedSharePost
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