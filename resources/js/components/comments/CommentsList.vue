<template>
    <Error v-if="error">{{error}}</Error>
    <Loading v-if="loading_comment" />
    <div v-else>
        <div v-if="commentsList.length > 0" class="comment_list border-t border-divider pt-3 my-3 dark:border-white/10 feed-comment-info-comment-holder-border">
            <!-- show comment item detail -->
            <div v-if="commentInfo" class="border-t-4 border-b-4 border-divider pt-3 mb-base-2">
                <CommentItem :item="item" :router_name="router_name" :comment="commentInfo" @reply_comment="replyComment" :reply_id="reply_id"/>
            </div>
            <!-- end show comment item detail -->
            <TransitionGroup name="fade">
                <div v-for="comment in commentsList" :key="comment.id">
                    <CommentItem v-if="comment.id != comment_id" :item="item" :comment="comment" :router_name="router_name" @reply_comment="replyComment"/>
                </div>
            </TransitionGroup>
            <div v-if="showLoadmore" class="text-center my-5">
                <BaseButton size="sm" @click="loadComments(page)" >{{$t('View more')}}</BaseButton>
            </div>
        </div>
        <div class="comment_form bg-white dark:bg-slate-800 border-t border-divider dark:border-white/10 feed-comment-info-comment-holder-border fixed left-0 right-0 lg:relative lg:bottom-0 z-10" :class="authenticated ? 'bottom-[64px]' : 'bottom-0'">
            <div v-if="replyingUserName" class="reply-status-item flex items-center justify-between bg-web-wash p-base-2 rounded-none absolute bottom-full left-0 right-0 w-full md:rounded-base dark:bg-dark-web-wash">
                {{$t('Replying to')}} {{ replyingUserName }}
                <BaseIcon name="close" size="16" @click="cancelMention()" class="reply-status-item-icon"/>
            </div>
            <CommentForm ref="commentForm" @post_comment="postNewComment" class="p-base-2"/>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import Error from '@/components/utilities/Error.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import Loading from '@/components/utilities/Loading.vue'
import CommentForm from '@/components/comments/CommentForm.vue'
import CommentItem from '@/components/comments/CommentItem.vue'
import BaseIcon from "@/components/icons/BaseIcon.vue"
import { useCommentStore } from '@/store/comment'
import { usePostStore } from '@/store/post'
import { useAuthStore } from '@/store/auth'

export default {
    components: { Error, BaseButton, Loading, CommentForm, CommentItem, BaseIcon },
    props: ['item', 'type', 'comment_id', 'reply_id', 'router_name'],
    data(){
        return{
            replyingUserName: null,
            error: null,
            loading_comment: true,
            showLoadmore: false,
            replyCommentId: null,
            commentSingle: null,
            page: 1
        }
    },
    computed:{
        ...mapState(useAuthStore, ['authenticated']),
        ...mapState(useCommentStore, ['commentsList', 'commentInfo'])
    },
    mounted(){
        this.loadComments(this.page);
        var replyId = (typeof this.reply_id === 'undefined') ? '' : this.reply_id
        if(this.comment_id){
            this.getCommentDetail({
                type: this.type, 
                itemId: this.item.id, 
                commentId: this.comment_id, 
                replyId
            })
        }
    },
    unmounted(){
		this.resetCommentsData()
	},
    methods:{
        ...mapActions(useCommentStore, ['getCommentsListByItemId', 'postComment', 'postReply', 'getCommentSingleDetail', 'resetCommentsData']),
        ...mapActions(usePostStore, ['addCommentToPost']),
        async loadComments(page){
            try {
                const response = await this.getCommentsListByItemId({itemType: 'posts', itemId: this.item.id, page: page})
                if(response.has_next_page){
                    this.showLoadmore = true
                    this.page++;
                }else{
                    this.showLoadmore = false
                }
                this.loading_comment = false
            } catch (error) {
                this.error = error
                this.loading_comment = false
            }
        },
        async postNewComment(content){
            if(this.replyCommentId){
                try {
                    await this.postReply({
                        comment_id: this.replyCommentId,
                        content: content
                    })
                } catch (error) {
                    this.showError(error.error)
                }
            }else{
                try {
                    await this.postComment({                 
                        subject_type: this.type,
                        subject_id: this.item.id,
                        content: content,
                        replies: []
                    }).then((response) => {
                        this.addCommentToPost(response, this.item.id)
                    })
                } catch (error) {
                    this.showError(error.error)
                }
            }
            this.replyingUserName = null
            this.focusForm()
        },
        replyComment(content, commentId, name) {
            this.$refs.commentForm.setContent(content)
            this.replyCommentId = commentId
            this.replyingUserName = name
        },
        focusForm(){
            this.$refs.commentForm.setContent('')
            this.replyCommentId = null
        },
        cancelMention(){
            this.replyCommentId = null
            this.replyingUserName = null
        },
        async getCommentDetail(type, itemId, commentId, replyId){
            try {
                await this.getCommentSingleDetail(type, itemId, commentId, replyId)
            } catch (error) {
                this.$router.push({name: this.router_name, params: {id : this.item.id}})
            }
        }
    }
}
</script>