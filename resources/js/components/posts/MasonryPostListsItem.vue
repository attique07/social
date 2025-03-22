<template>
    <div class="relative cursor-pointer bg-white dark:bg-slate-800 pb-[100%]" @click="handleClickPost()">
        <div class="absolute inset-0">
            <div v-if="post.type === 'photo'" class="w-full h-full bg-center bg-cover bg-no-repeat" :style="{ backgroundImage: `url(${post.items[0].subject.url})`}"></div>
            <div v-else-if="post.type === 'video'" class="w-full h-full bg-center bg-cover bg-no-repeat" :style="{ backgroundImage: `url(${post.items[0].subject.thumb.url})`}"></div>
            <BaseIcon v-if="post.type === 'photo' && post.items.length > 1" name="images" size="28" class="absolute top-3 right-3 text-white"/>
            <BaseIcon v-else-if="post.type === 'video'" name="video_solid" size="28" class="absolute top-3 right-3 text-white"/>
            <div class="flex items-center justify-center gap-6 text-white absolute inset-0 bg-black-trans-4 opacity-0 transition-opacity hover:opacity-100">
                <div class="flex items-center gap-2">
                    <BaseIcon name="like"/>
                    <span>{{ $filters.numberShortener(post.like_count, $t('[number]'), $t('[number]')) }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <BaseIcon name="message"/>
                    <span>{{ $filters.numberShortener(post.comment_count, $t('[number]'), $t('[number]')) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { checkPopupBodyClass, changeUrl } from '@/utility/index'
import PostDetailModal from '@/components/posts/PostDetailModal.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    props: ['post'],
    components: { BaseIcon },
    methods: {
        handleClickPost(){
            let postUrl = this.$router.resolve({
                name: 'post',
                params: { 'id': this.post.id }
            });
            changeUrl(postUrl.fullPath)
            this.$dialog.open(PostDetailModal, {
                data: {
                    post: this.post
                },
                props:{
                    class: 'post-detail-modal p-dialog-lg p-dialog-full-page',
                    modal: true,
                    dismissableMask: true,
                    showHeader: false,
                    draggable: false
                },
                onClose: () => {
                    changeUrl(this.$router.currentRoute.value.fullPath)
                    checkPopupBodyClass();
                }
            });
        },
    }
}
</script>