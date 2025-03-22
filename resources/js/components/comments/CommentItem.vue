<template>
    <Error v-if="error">{{error}}</Error>
    <div class="comment-item flex mb-base-2 group">
        <Avatar :user="comment.user"/>
        <div class="flex-1 mx-base-2 min-w-0">
            <UserName :user="comment.user" class="float-start me-1 comment-item-username" />
            <ContentHtml :content="comment.content" :mentions="comment.mentions" class="comment-item-content break-normal"/>
            <div class="comment-item-date flex flex-wrap items-center gap-base-1 text-xs text-sub-color mt-1 dark:text-slate-400 w-full">
                <router-link :to="{name: router_name, params: {id : item.id, comment_id: comment.id}}" class="text-sub-color dark:text-slate-400">{{comment.created_at}} . </router-link>
                <button v-if="comment.like_count > 0" @click="openLikersModal('comments', comment.id)">{{ $filters.numberShortener(comment.like_count, $t('[number] like'), $t('[number] likes')) }} . </button>
                <button @click.prevent="replyComment(comment)">{{$t('reply')}}</button>
                <button v-if="authenticated" @click="openDropdownCommentMenu(comment)" class="invisible group-hover:visible">
                    <BaseIcon name="more_horiz_outlined" size="16"/>                          
                </button>
            </div>
            <div v-if="comment.reply_count > 0">
                <button v-if="(comment.reply_count - comment.replies.length - (replyIdData ? 1 : 0)) > 0 && showRepliesBtn" class="comment-item-view_all text-xs text-sub-color mt-1 dark:text-slate-400" @click="showReplies(comment.id, page)">{{ $filters.numberShortener(comment.reply_count - comment.replies.length - (replyIdData ? 1 : 0), $t('View reply ([number])'), $t('View replies ([number])')) }}</button>
                <button v-else class="comment-item-view_all text-xs text-sub-color mt-1 dark:text-slate-400" @click="hideReplies(comment.id)">{{$t('Hide replies')}}</button>
            </div>
        </div>
        <div class="pt-[0.2rem]">
            <button v-if="comment.is_liked" @click="unLikeComment(comment.id)">
                <BaseIcon name="like_active" size="16" class="comment-item-icon-active text-base-red"/>
            </button>
            <button v-else @click="likeComment(comment.id)">
                <BaseIcon name="like" size="16" class="comment-item-icon"/>
            </button>
        </div>
    </div>
    <div v-if="comment.replies.length > 0 || (this.replyIdData && comment.reply)" class="replies-list ps-10 mt-base-1 ms-base-2">
        <!-- At Reply Detail Page -->
        <div v-if="this.replyIdData && comment.reply" class="reply-item flex mb-base-2 group">
            <Avatar :user="comment.reply.user"/>
            <div class="flex-1 mx-base-2 truncate">
                <UserName :user="comment.reply.user" class="float-start me-1 reply-item-username max-w-full" />
                <ContentHtml :content="comment.reply.content" :mentions="comment.reply.mentions" class="reply-item-content"/>
                <div class="reply-item-date flex flex-wrap items-center gap-base-1 text-xs text-sub-color dark:text-slate-400">
                    <router-link :to="{name: router_name, params: {id : item.id, comment_id: comment.id, reply_id: comment.reply.id}}" class="text-sub-color dark:text-slate-400">{{comment.reply.created_at}} . </router-link>
                    <button v-if="comment.reply.like_count > 0" @click="openLikersModal('comment_replies', comment.reply.id)">{{ $filters.numberShortener(comment.reply.like_count, $t('[number] like'), $t('[number] likes')) }} . </button>
                    <button @click.prevent="replyComment(comment, comment.reply)">{{$t('reply')}}</button>
                    <button v-if="authenticated" @click="openDropdownReplyMenu(comment, comment.reply)" class="invisible group-hover:visible">
                        <BaseIcon name="more_horiz_outlined" size="16"/>
                    </button>
                </div>
            </div>
            <div class="pt-[0.2rem]">
                <button v-if="comment.reply.is_liked" @click="unLikeReply(comment.id, comment.reply.id)">
                    <BaseIcon name="like_active" size="16" class="reply-item-icon-active text-base-red"/>
                </button>
                <button v-else @click="likeReply(comment.id, comment.reply.id)">
                    <BaseIcon name="like" size="16" class="reply-item-icon"/>
                </button>
            </div>
        </div>
        <!-- At Comments Page -->
        <template v-if="comment.replies.length > 0">
            <TransitionGroup name="fade">
                <div v-for="reply in comment.replies" :key="reply.id" class="reply-item flex mb-base-2 group">
                    <Avatar :user="reply.user"/>
                    <div class="flex-1 mx-base-2 truncate">
                        <UserName :user="reply.user" class="float-start me-1 reply-item-username max-w-full" />
                        <ContentHtml :content="reply.content" :mentions="reply.mentions" class="reply-item-content"/>
                        <div class="reply-item-date flex flex-wrap items-center gap-base-1 text-xs text-sub-color dark:text-slate-400">
                            <router-link :to="{name: router_name, params: {id : item.id, comment_id: comment.id, reply_id: reply.id}}" class="text-sub-color dark:text-slate-400">{{reply.created_at}} . </router-link>
                            <button v-if="reply.like_count > 0" @click="openLikersModal('comment_replies', reply.id)">{{ $filters.numberShortener(reply.like_count, $t('[number] like'), $t('[number] likes')) }} . </button>
                            <button @click.prevent="replyComment(comment, reply)">{{$t('reply')}}</button>
                            <button v-if="authenticated" @click="openDropdownReplyMenu(comment, reply)" class="invisible group-hover:visible">
                                <BaseIcon name="more_horiz_outlined" size="16"/>
                            </button>
                        </div>
                    </div>
                    <div class="pt-[0.2rem]">
                        <button v-if="reply.is_liked" @click="unLikeReply(comment.id, reply.id)">
                            <BaseIcon name="like_active" size="16" class="reply-item-icon-active text-base-red"/>
                        </button>
                        <button v-else @click="likeReply(comment.id, reply.id)">
                            <BaseIcon name="like" size="16" class="reply-item-icon"/>
                        </button>
                    </div>
                </div>
            </TransitionGroup>
        </template>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { checkPopupBodyClass } from '@/utility/index'
import Constant from '@/utility/constant'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Error from '@/components/utilities/Error.vue'
import LikersModal from '@/components/modals/LikersModal.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import CommentOptionsMenu from '@/components/comments/CommentOptionsMenu.vue'
import ReplyOptionsMenu from '@/components/comments/ReplyOptionsMenu.vue'
import { useAuthStore } from '../../store/auth'
import { useCommentStore } from '../../store/comment'

export default {
    components: { Error, BaseIcon, ContentHtml, UserName, Avatar },
    props: ['comment', 'router_name', 'item', 'reply_id'],
    data(){
        return{           
            error: null,
            page: 1,
            replyIdData: this.reply_id,
            showRepliesBtn: true
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user', 'authenticated']),
        ...mapState(useCommentStore, ['repliesList'])
    },
    methods:{
        ...mapActions(useCommentStore, ['getRepliesByCommentId', 'toggleLikeReplyItem', 'toggleLikeCommentItem','hideRepliesList']),
        openLikersModal(type, id){
            this.$dialog.open(LikersModal, {
                data: {
                    itemType: type,
                    itemId: id
                },
                props:{
                    header: this.$t('Likes'),
                    class: 'likers-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            });
        },
        openDropdownCommentMenu(comment){
            this.$dialog.open(CommentOptionsMenu, {
                data: {
                    comment
                },
                props:{
                    showHeader: false,
                    class: 'dropdown-menu-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            });
        },
        openDropdownReplyMenu(comment, reply){
            this.$dialog.open(ReplyOptionsMenu, {
                data: {
                    comment,
                    reply
                },
                props:{
                    showHeader: false,
                    class: 'dropdown-menu-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            });
        },      
        async loadReplies(commentId, page, reply_id){
            try {
                const response = await this.getRepliesByCommentId({commentId, page, reply_id})
                if(response.has_next_page){
                    this.page++;
                    this.showRepliesBtn = true
                }else{
                    this.showRepliesBtn = false
                }
            } catch (error) {
                this.error = error
            }
        },
        showReplies(commentId, page){
            if(this.replyIdData){
                this.replyIdData = this.reply_id
            }
            this.loadReplies(commentId, page, this.replyIdData);
        },
        hideReplies(commentId){
            this.hideRepliesList(commentId)
            this.page = 1
            this.showRepliesBtn = true
            if(this.replyIdData){
                this.replyIdData = null
            }
        },
        async likeComment(commentId){
            if(this.authenticated){
                try {
                    await this.toggleLikeCommentItem({
                    subject_type: 'comments',
                    subject_id: commentId,
                    action: 'add'
                })
                } catch (error) {
                    this.showError(error.error)
                }
            }else{
                this.showRequireLogin()
            }                 
        },
        async unLikeComment(commentId){
            if(this.authenticated){
                try {
                    await this.toggleLikeCommentItem({
                    subject_type: 'comments',
                    subject_id: commentId,
                    action: 'remove'
                })
                } catch (error) {
                    this.showError(error.error)
                }  
            }else{
                this.showRequireLogin()
            }                   
        },
        replyComment(comment, reply = null) {
            if(reply){
                this.$emit('reply_comment', (this.user.id != reply.user.id) ? Constant.MENTION + reply.user.user_name + ' ' : '', comment.id, reply.user.name)
            }else{
                this.$emit('reply_comment', (this.user.id != comment.user.id) ? Constant.MENTION + comment.user.user_name + ' ' : '', comment.id, comment.user.name)
            }
        },
        likeReply(commentId, replyId){
            if(this.authenticated){
                this.toggleLikeReplyItem({
                    comment_id: commentId,
                    subject_type: 'comment_replies',
                    subject_id: replyId,
                    action: 'add'
                })
            }else{
                this.showRequireLogin()
            }       
        },
        unLikeReply(commentId, replyId){
            if(this.authenticated){
                this.toggleLikeReplyItem({
                    comment_id: commentId,
                    subject_type: 'comment_replies',
                    subject_id: replyId,
                    action: 'remove'
                })
            }else{
                this.showRequireLogin()
            }     
        }
    },
    emits: ['reply_comment']
}
</script>