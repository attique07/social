<template>
    <button class="post-header-back sticky top-0 z-50 flex items-center justify-start w-full p-base-2 bg-white text-black border-b border-divider lg:hidden dark:bg-slate-800 dark:border-white/10 dark:text-white" @click="closePostDetailModal()">
        <BaseIcon name="arrow_left"></BaseIcon>
    </button>
    <div v-if="error" class="bg-white p-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800">
        <Error class="mb-0">{{error}}</Error>
    </div>
    <div v-else class="post-detail-modal-content p-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800">
        <PostContent :post="post" @comment_click="focusForm()"/>
        <div class="feed-comment-info-comment-holder">
            <CommentsList ref="CommentsList" :router_name="'post'" :item="post" :type="'posts'"/>
        </div> 
    </div>
    <CloseButton @click="closePostDetailModal()" />
</template>

<script>
import { mapState, mapActions } from 'pinia'
import Error from '@/components/utilities/Error.vue'
import PostContent from '@/components/posts/PostContent.vue'
import CommentsList from '@/components/comments/CommentsList.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import { usePostStore } from '../../store/post'
import CloseButton from '@/components/utilities/CloseButton.vue'

export default {
    components: {
        Error,
        PostContent,
        CommentsList,
        BaseIcon,
        CloseButton
    },
    inject: ['dialogRef'],
    data() {    
        return {
            error: null,
            post: this.dialogRef.data.post
		}
	},
    watch: {
        '$route'(){
            this.dialogRef.close()
        },
        postsList(newValue, oldValue) {
            if(newValue < oldValue){ // Post Item has been deleted
                this.closePostDetailModal()
            }
        }
    },
    mounted() {
        if(this.dialogRef.data.post){
            this.loadPostInfo(this.dialogRef.data.post.id)
        }
    },
    computed: {
        ...mapState(usePostStore, ['postsList']),
    },
    methods: {
        ...mapActions(usePostStore, [ "getPostById"]),
        async loadPostInfo(postId){
            try {
                await this.getPostById(postId);
            } catch (error) {
                this.error = error.error.detail.id
            }
        },
        focusForm(){
            this.$refs.CommentsList.focusForm()
        },
        closePostDetailModal(){
            this.dialogRef.close()
        }
    }
}
</script>

<style>

</style>