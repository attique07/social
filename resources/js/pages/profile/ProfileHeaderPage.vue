<template>
    <div v-if="userInfo" class="header-profile bg-white rounded-none md:rounded-base-lg dark:bg-slate-800 mb-base-2">
        <div class="relative overflow-hidden rounded-none md:rounded-t-base-lg pb-[38.5%]">
            <div class="absolute inset-0 h-full">
                <div class="bg-cover bg-center bg-no-repeat w-full h-full" :style="{ backgroundImage: `url(${userInfo.cover})`}"></div>
            </div>
            <button v-if="authenticated && user.id == userInfo.id" class="inline-flex items-center gap-1 bg-gray-trans-4 text-white rounded p-1 absolute top-base-2 end-base-2" @click="$refs.cover.click()">
                <BaseIcon name="camera"/>
                <input type="file" ref="cover" @change="uploadCover($event)" @click="onInputClick($event)" accept="image/*" class="hidden">
            </button>
        </div>
        <div class="relative inline-block p-base-1 ms-4 -mt-20 md:-mt-28 rounded-full" :class="(userInfo.story_id == 0) ? 'bg-white dark:bg-slate-800' : 'bg-gradient-story cursor-pointer'">
            <div class="w-28 h-28 md:w-base-36 md:h-base-36">
                <Avatar :user="userInfo" :size="'100%'" :activePopover="false" :border="false" @click="(userInfo.story_id == 0) ? '' : showStoryDetail(userInfo.story_id)"/>
            </div>
            <button v-if="authenticated && user.id == userInfo.id" class="inline-flex items-center gap-1 bg-gray-trans-4 text-white rounded-full p-1 absolute top-0 end-1 md:top-2 md:end-2" @click="$refs.avatar.click()">
                <BaseIcon name="camera"/>
                <input type="file" ref="avatar" @change="uploadAvatar($event)" @click="onInputClick($event)" accept="image/*" class="hidden">
            </button>
        </div>
        <div class="px-4 pb-4">
            <div class="flex flex-wrap justify-between items-start gap-x-5 gap-y-1 mb-base-1">
                <div class="flex-1 max-w-full whitespace-nowrap">
                    <UserName :user="userInfo" :activePopover="false" class="header-profile-name text-base-lg font-extrabold" />
                    <div class="header-profile-username text-base-sm text-sub-color dark:text-slate-400 truncate">{{mentionChar + userInfo.user_name}}</div>				
                </div>						
                <div class="flex flex-wrap items-center gap-2">
                    <template v-if="userInfo.is_page">
                        <template v-if="userInfo.id === user.id">
                            <BaseButton v-if="config.advertising.enable" type="outlined" :to="{ name: 'advertisings' }">{{ $t('Advertise') }}</BaseButton>
                            <BaseButton v-if="config.user_page.featureEnable && !userInfo.is_page_feature" type="outlined" :to="{name: 'user_pages_feature'}">{{ $t('Feature Page') }}</BaseButton>
                            <BaseButton type="outlined" :to="{ name: 'user_pages_overview' }">{{ $t('Manage') }}</BaseButton>
                        </template>
                        <BaseButton v-if="userInfo.is_page_admin && config.advertising.enable" type="outlined" @click="handleClickSwitchPage(userInfo, true)">{{ $t('Advertise') }}</BaseButton>
                        <BaseButton v-if="userInfo.is_page_admin" type="outlined" @click="handleClickSwitchPage(userInfo, false)">{{ $t('Switch Page') }}</BaseButton>
                    </template>
                    <template v-if="user.id !== userInfo.id">
                        <template v-if="userInfo.can_send_message || userInfo.chat_room_id">
                            <MessageButton :user_id="userInfo.id"></MessageButton>
                        </template>
                        <template v-if="userInfo.can_follow">
                            <BaseButton v-if="userInfo.is_followed" @click="unFollowUser(userInfo.id)" type="outlined">{{$t('Unfollow')}}</BaseButton>
                            <BaseButton v-else @click="followUser(userInfo.id)" type="outlined">{{$t('Follow')}}</BaseButton>
                        </template>
                        <button v-if="authenticated" @click="openProfileMenu()">
                            <BaseIcon name="more_horiz_outlined" />
                        </button>
                    </template>
                    <template v-else>
                        <BaseButton v-if="!userInfo.is_verify && config.userVerifyEnable" type="outlined" @click="clickVerify">{{ userInfo.is_page ? $t('Verify Page') : $t('Verify Profile') }}</BaseButton>
                        <BaseButton type="outlined" :to="{name: 'setting_index'}">{{ userInfo.is_page ? $t('Edit Page') : $t('Edit Profile')}}</BaseButton>
                    </template>
                </div>
            </div>
            <div v-if="userInfo.categories && userInfo.categories.length" class="flex flex-wrap mb-1">
                <span v-for="(category, index) in userInfo.categories" :key="category.id">
                    <router-link :to="{name: 'user_pages', query: {category_id: category.id}}">{{ category.name }}</router-link>
                    {{ (index === userInfo.categories.length - 1) ? '' : '.&nbsp;'}}
                </span>
            </div>
            <div v-if="!userInfo.check_private" class="header-profile-count text-base-sm mb-base-1">
                <button v-if="userInfo.show_following" @click="openFollowingModal()">{{ $filters.numberShortener(userInfo.following_count, $t('[number] Following'), $t('[number] Followings')) }}</button> <span v-if="userInfo.show_following && userInfo.show_follower">.</span> <button v-if="userInfo.show_follower" @click="openFollowerModal()">{{ $filters.numberShortener(userInfo.follower_count, $t('[number] Follower'), $t('[number] Followers')) }}</button>
            </div>						
            <div v-if="userInfo.check_privacy" class="text-base-sm break-word">{{userInfo.bio}}</div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { defineAsyncComponent } from 'vue'
import { changeUrl, checkPopupBodyClass } from '@/utility'
import { toggleFollowUser } from '@/api/follow'
import { switchPage } from '@/api/page'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { useProfileStore } from '@/store/profile'
import Constant from '@/utility/constant'
import BaseButton from '@/components/inputs/BaseButton.vue'
import MessageButton from '@/components/utilities/MessageButton.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import ProfileUserMenu from '@/pages/profile/ProfileUserMenu.vue'
import ListFollowingModal from '@/components/modals/ListFollowingModal.vue'
import ListFollowerModal from '@/components/modals/ListFollowerModal.vue'
import StoryDetailModal from '@/components/stories/StoryDetailModal.vue'
import { useUtilitiesStore } from '@/store/utilities'

export default {
    components: { BaseButton, MessageButton, BaseIcon, Avatar, UserName },
    props: ['data', 'params', 'position'],
    data() {
		return {
            mentionChar: Constant.MENTION
		}
	},
    computed: {
        ...mapState(useAppStore, ['config']),
        ...mapState(useAuthStore, ['user', 'authenticated']),
        ...mapState(useProfileStore, ['userInfo']),
    },
    methods:{
        ...mapActions(useUtilitiesStore, ['setSelectedPage']),
        openProfileMenu(){
            this.$dialog.open(ProfileUserMenu, {
                data: {
                    userInfo: this.userInfo
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
		openFollowingModal(){
            this.$dialog.open(ListFollowingModal, {
				data: {
                    user: this.userInfo
                },
                props:{
                    header: this.$t('Followings'),
                    class: 'follow-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }         
            });
        },
        openFollowerModal(){
            this.$dialog.open(ListFollowerModal, {
				data: {
                    user: this.userInfo
                },
                props:{
                    header: this.$t('Followers'),
                    class: 'follow-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }         
            });
        },
        showStoryDetail(storyId){
			if (storyId > 0) {
				let storyUrl = this.$router.resolve({
					name: 'story_view',
					params: { 'storyId': storyId }
				});
				changeUrl(storyUrl.fullPath)
				this.$dialog.open(StoryDetailModal, {
					data: {
						id: storyId,
						storiesList: [storyId]
					},
					props:{
						class: 'p-dialog-story p-dialog-story-detail p-dialog-no-header-title',
						modal: true,
						showHeader: false,
						draggable: false
					},
					onClose: () => {
						changeUrl(this.$router.currentRoute.value.fullPath)
						checkPopupBodyClass();
					}
				});
			}
        },
        uploadAvatar(event){
			var input = event.target;
			// Ensure that you have a file before attempting to read it
			if (input.files && input.files[0]) {
				// create a new FileReader to read this image and convert to base64 format
				var reader = new FileReader();
				// Define a callback function to run, when FileReader finishes its job
				reader.onload = e => {
					// Note: arrow function used here, so that "this.imageData" refers to the imageData of Vue component
					// Read image as base64 and set to imageData
					// Open modal to crop cover image
					const UploadAvatarModal = defineAsyncComponent(() =>
						import('@/components/modals/UploadAvatarModal.vue')
					)
					this.$dialog.open(UploadAvatarModal, {
						data: {
							imageData: e.target.result
						},
						props:{
							header: this.$t('Crop Avatar'),
							class: 'crop-avatar-modal p-dialog-md',
							modal: true
						},
						onClose: (options) => {
							if(options.data){
								this.userInfo.avatar = options.data.avatar
							}
							checkPopupBodyClass();
						}
					})
				};
				// Start the reader job - read file as a data url (base64 format)
				reader.readAsDataURL(input.files[0]);

			}
		},
		uploadCover(event){
			var input = event.target;
			// Ensure that you have a file before attempting to read it
			if (input.files && input.files[0]) {
				// create a new FileReader to read this image and convert to base64 format
				var reader = new FileReader();
				// Define a callback function to run, when FileReader finishes its job
				reader.onload = e => {
					// Note: arrow function used here, so that "this.imageData" refers to the imageData of Vue component
					// Read image as base64 and set to imageData
					// Open modal to crop cover image
					const UploadCoverModal = defineAsyncComponent(() =>
						import('@/components/modals/UploadCoverModal.vue')
					)
					this.$dialog.open(UploadCoverModal, {
						data: {
							imageData: e.target.result
						},
						props:{
							header: this.$t('Crop Cover'),
							class: 'crop-cover-modal p-dialog-lg',
							modal: true
						},
						onClose: (options) => {
							if(options.data){
								this.userInfo.cover = options.data.cover
							}
							checkPopupBodyClass();
						}
					})
				};
				// Start the reader job - read file as a data url (base64 format)
				reader.readAsDataURL(input.files[0]);

			}
		},
        onInputClick(event){
			event.target.value = null
		}, 
        async followUser(userId) {
            if(this.authenticated){
				try {
					await toggleFollowUser({
						id: userId,
						action: "follow"
					});
					this.userInfo.is_followed = true
					/*this.userInfo.check_privacy = true
					if (this.userInfo.chat_privacy == Constant.CHAT_PRIVACY_FOLLOWER) {
						this.userInfo.can_send_message = true
					}*/
					this.updateFollowStatus({userId, status: 'follow'})
				}
				catch (error) {
					this.showError(error.error)
				}
			}else{
				this.showRequireLogin()
			}
        },
        async unFollowUser(userId) {
            try {
                await toggleFollowUser({
                    id: userId,
                    action: "unfollow"
                });
                this.userInfo.is_followed = false
				/*if (this.userInfo.privacy == Constant.USER_PRIVACY_FOLLOWER) {
					this.userInfo.check_privacy = false
				}
				if (this.userInfo.chat_privacy == Constant.CHAT_PRIVACY_FOLLOWER) {
						this.userInfo.can_send_message = false
				}*/
				this.updateFollowStatus({userId, status: 'unfollow'})
            }
            catch (error) {
				this.showError(error.error)
            }
        },
        async handleClickSwitchPage(page, ads){
			this.setSelectedPage(page)
            setTimeout(async() => {
                try {  
                    await switchPage(page.id)
                    if (ads) {
                        let url = this.$router.resolve({
                            name: 'advertisings',
                        });
                        window.location.href = window.siteConfig.siteUrl + url.fullPath
                    } else {
                        window.location.reload()
                    }
                    
                } catch (error) {
                    this.showError(error.error)
                    this.setSelectedPage(null)
                }
            }, 1500);
		},
        clickVerify() {
            let permission = 'user_verify.send_request'
			if(this.checkPermission(permission)){
				this.$router.push({ name: 'verify_profile'})
			}
        }
    }
}
</script>