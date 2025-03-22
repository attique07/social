<template>
    <div v-if="post">
        <div class="feed-item-header flex mb-base-2">
            <div class="feed-item-header-img">
                <Avatar :user="post.user"/>
            </div>
            <div class="feed-item-header-info flex-grow ps-base-2">
                <div class="feed-item-header-info-title flex justify-between items-start gap-base-2">
                    <div class="whitespace-normal">
                        <UserName :user="post.user" :truncate="false" />
                        <template v-if="post.type === 'user_page_review'">
                            <span>{{ ' ' + (post.items[0].subject?.is_recommend ? $t('recommended') : $t("doesn't recommend")) + ' ' }}</span>
                            <UserName :user="post.items[0].subject?.page" :truncate="false" />
                        </template>
                    </div>
                    <button v-if="showMenuAction && authenticated" @click="openDropdownMenu()" class="pt-[0.1rem]">
                        <BaseIcon size="20" name="more_horiz_outlined" class="feed-item-dropdown text-sub-color dark:text-slate-400"/>
                    </button>
                </div>
                <div class="feed-item-header-info-date flex gap-base-1 items-center w-max text-sub-color text-xs dark:text-slate-400">
                    <router-link :to="{name: 'post', params: {id: post.id}}" class="text-inherit">{{post.created_at}}</router-link>
                    <span class="bg-web-wash text-main-color p-1 rounded-md dark:bg-dark-web-wash dark:text-slate-400" v-if="post.is_ads">{{ $t('Sponsored') }}</span>
                </div>
            </div>
        </div>
        <div class="feed-item-content">
            <ContentHtml class="activity_feed_content_message mb-base-2" :content="post.content" :mentions="post.mentions" />
            <div class="activity_feed_content_item">
                <PostContentType :post="post" :auto-play="autoPlay"/>
            </div>
        </div>
        <div v-if="showCommentAction" class="feed-main-action mt-3 mb-2 dark:text-white">
            <div class="flex justify-between mb-base-2">
                <div class="flex space-s-3">
                    <button v-if="post.is_liked" @click="unLikePost(post.id)">
                        <BaseIcon name="like_active" class="feed-main-action-active text-base-red"/>
                    </button>
                    <button v-else @click="likePost(post.id)">
                        <BaseIcon name="like"/>
                    </button>
                    <button @click="commentClick()">
                        <BaseIcon name="message"/>
                    </button>
                    <button @click="openShareModal('posts', post)">
                        <BaseIcon name="share"/>
                    </button>
                </div>
                <div class="feed-comment-info-action-right flex gap-base-2">
                    <button v-if="post.is_bookmarked" @click="unBookmarkPost(post.id)">
                        <BaseIcon name="bookmark_active" class="feed-main-action-bookmark-active text-primary-color dark:text-dark-primary-color"/>
                    </button>
                    <button v-else @click="bookmarkPost(post.id)">
                        <BaseIcon name="bookmarks" class="dark:text-white"/>
                    </button>
                    <BaseButton @click="boostPost" v-if="post.canBoot" size="sm">{{ $t('Boost Post') }}</BaseButton>
                </div>
            </div>
            <button class="feed-item-like-text inline-block text-main-color font-semibold dark:text-white" @click="openLikersModal('posts', post.id)">{{ $filters.numberShortener(post.like_count, $t('[number] like'), $t('[number] likes')) }}</button>
        </div>  
    </div>
    <Error v-else class="mb-0">{{$t('Post is not found')}}</Error>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { checkPopupBodyClass } from '@/utility/index'
import PostContentType from '@/components/posts/PostContentType.vue'
import PostOptionsMenu from '@/components/posts/PostOptionsMenu.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import LikersModal from '@/components/modals/LikersModal.vue'
import Error from '@/components/utilities/Error.vue'
import ShareOptionsMenu from '@/components/share/ShareOptionsMenu.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import { useAuthStore } from '../../store/auth'
import { usePostStore } from '../../store/post'
import { useAppStore } from '../../store/app'
import { useUtilitiesStore } from '@/store/utilities'
import { switchPage } from '@/api/page'

export default {
    components: { ContentHtml, PostContentType, BaseIcon, Error, UserName, Avatar, BaseButton },
    props: {
        post: {
            type: Object,
            default: null
        },
        showCommentAction: {
            type: Boolean,
            default: true
        },
        showMenuAction: {
            type: Boolean,
            default: true
        },
        autoPlay:{
            type: Boolean,
            default: false
        }
    },
    computed:{
        ...mapState(useAuthStore, ['authenticated', 'user']),
        ...mapState(useAppStore, ['setDynamicDialogShown'])
    },
    methods: {
        ...mapActions(usePostStore, ['toggleLikePostItem', 'toggleBookmarkPostItem']),
        ...mapActions(useUtilitiesStore, ['setSelectedPage']),
        async likePost(postId){
            if(this.authenticated){
                try {
                    await this.toggleLikePostItem({
                        subject_type: 'posts',
                        subject_id: postId,
                        action: 'add'
                    })
                } catch (error) {
                    this.showError(error.error)
                }
            }else{
                this.showRequireLogin()
            }
        },
        async unLikePost(postId){
            try {
                await this.toggleLikePostItem({
                    subject_type: 'posts',
                    subject_id: postId,
                    action: 'remove'
                })
            } catch (error) {
                this.showError(error.error)
            }
        },
        async bookmarkPost(postId){
            if(this.authenticated){
                try {
                    await this.toggleBookmarkPostItem({
                        subject_type: 'posts',
                        subject_id: postId,
                        action: 'add'
                    })
                } catch (error) {
                    this.showError(error.error)
                }
            }else{
                this.showRequireLogin()
            }       
        },
        async unBookmarkPost(postId){
            try {
                await this.toggleBookmarkPostItem({
                    subject_type: 'posts',
                    subject_id: postId,
                    action: 'remove'
                })
            } catch (error) {
                this.showError(error.error)
            }
        },
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
            })
        },
        openShareModal(type, subject){
            if(this.authenticated){
                this.$dialog.open(ShareOptionsMenu, {
                    data: {
                        subjectType: type,
                        subject: subject
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
                        this.setDynamicDialogShown(false)
                    }
                })
            }else{
                this.showRequireLogin()
            }
            this.setDynamicDialogShown(true)
        },
        openDropdownMenu(){
            this.$dialog.open(PostOptionsMenu, {
                data: {
                    post: this.post
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
                    this.setDynamicDialogShown(false)
                }
            });
            this.setDynamicDialogShown(true)
        },
        commentClick(){
            if(this.authenticated){
                this.$emit('comment_click')
            }else{
                this.showRequireLogin()
            }
        },
        boostPost() {
            if (this.user.id == this.post.user.id) {
                this.$router.push({ 'name': 'advertising_boost_post' ,params: {id : this.post.id}})
            } else {
                this.setSelectedPage(this.post.user)
                setTimeout(async() => {
                    try {
                        await switchPage(this.post.user.id)
                        let url = this.$router.resolve({
                            name: 'advertising_boost_post',
                            params: {id : this.post.id}
                        });
                        window.location.href = window.siteConfig.siteUrl + url.fullPath
                        
                    } catch (error) {
                        console.log(error)
                        this.showError(error.error)
                        this.setSelectedPage(null)
                    }
                }, 1500);
            }
        }
    },
    emits: ['comment_click']
}
</script>