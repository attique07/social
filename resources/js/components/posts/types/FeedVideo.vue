<template>
    <div v-for="postItem in post.items" :key="postItem.id" class="activity_content video_feed_activity_content">
        <div v-if="postItem.subject.thumb" class="activity_content_thumb w-full bg-black relative">
            <VideoPlayer ref="videoRef" :video="postItem.subject" :autoPlay="autoPlay" class="w-full" :class="(aspectRatioVideo(post.items[0].subject.thumb.params) == 'horizontal') ? '' : 'max-w-[16rem] mx-auto'"  :id="`videoContainer-${postItem.subject.file.id}-${videoKey}`"/>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import VideoPlayer from '@/components/utilities/VideoPlayer.vue';
import { useAuthStore } from '../../../store/auth';
import {uuidv4} from '@/utility/index'

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
    data(){
        return{
            videoKey: uuidv4()
        }
    },
    components: { VideoPlayer },
    computed: {
        ...mapState(useAuthStore, ['user'])
	},
    mounted(){
        if (this.user.video_auto_play && this.autoPlay) {
            const _self = this
            let observer = new IntersectionObserver(function(entries) {
                if (entries[0].isIntersecting) {
                    _self.$refs.videoRef[0]?.playVideo()
                } else {
                    _self.$refs.videoRef[0]?.pauseVideo()
                }
            }, { threshold: [0.8]});
            observer.observe(document.getElementById(`videoContainer-${_self.post.items[0].subject.file.id}-${this.videoKey}`));
        } 
    }
}
</script>