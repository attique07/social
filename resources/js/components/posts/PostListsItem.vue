<template>
    <div class="feed-item bg-white border border-white rounded-none md:rounded-base-lg p-4 mb-base-2 dark:bg-slate-800 dark:border-slate-800">
        <PostContent :post="post" :autoPlay="true" @comment_click="showPostDetail()"/>
        <div class="feed-comment-info-comment-holder">
            <div v-if="post.commentsList.length > 0" class="-mt-1 mb-base-1">
                <div v-for="comment in post.commentsList" :key="comment.id" class="feed-comment-info-comment-holder-comment">
                    <UserName :user="comment.user" :activePopover="false" class="comment-item-username float-start me-1 max-w-full" />
                    <ContentHtml :content="comment.content" :mentions="comment.mentions" class="comment-item-content break-normal"/>
                </div>
            </div>
            <div v-if="post.comment_count > 0" class="feed-comment-info-comment-holder-view-all text-sub-color dark:text-slate-400 -mt-1 mb-2">              
                <button @click="showPostDetail()">
                    {{ $filters.numberShortener(post.comment_count, $t('View all [number] comment'), $t('View all [number] comments')) }}
                </button>              
            </div>
            <div class="border-t border-divider dark:border-white/10 feed-comment-info-comment-holder-border">
                <CommentForm ref="commentForm" @post_comment="postNewComment" class="pt-base-2"/>
            </div>
        </div>
    </div>
</template>

<script>
import { mapActions } from 'pinia'
import { checkPopupBodyClass, changeUrl } from '@/utility/index'
import PostDetailModal from '@/components/posts/PostDetailModal.vue'
import PostContent from '@/components/posts/PostContent.vue'
import CommentForm from '@/components/comments/CommentForm.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import UserName from '@/components/user/UserName.vue'
import { usePostStore } from '@/store/post'
import { useCommentStore } from '@/store/comment'

export default {
    components: {
        PostContent,
        CommentForm,
        ContentHtml,
        UserName
    },
    props: ['post'],
    data() {
		return {
            comment: {
                subject_type: 'post',
                subject_id: null
            }
		}
	},
    methods: {
        ...mapActions(useCommentStore, ['postComment']),
        ...mapActions(usePostStore, ['addCommentToPost']),
        showPostDetail(){
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
        focusCommentBox(){
            this.$refs.commentForm.setContent('')
        },
        async postNewComment(content){
			try {
                await this.postComment({                 
                    subject_type: 'posts',
					subject_id: this.post.id,
					content: content,
                    replies: []
                }).then((response) => {
                    this.addCommentToPost(response, this.post.id)
                })
                this.$refs.commentForm.setContent('')
			} catch (error) {
                this.showError(error.error)
			}  
		}
    }
}
</script>

<style>

</style>