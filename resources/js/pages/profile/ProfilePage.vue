<template>
	<div v-if="userInfo">
		<template v-if="userInfo.check_privacy">
			<div class="profile-menu-list flex flex-wrap mb-base-2 gap-base-2">
				<div class="profile-menu-list-item flex flex-1 min-w-[140px] items-center justify-center rounded-none md:rounded-base-lg gap-2 p-3 text-base-sm cursor-pointer" :class="(currentTab === ''?'active bg-primary-color text-white dark:bg-dark-primary-color':'bg-white dark:bg-slate-800')" @click="changeTab('')">
					<BaseIcon name="feeds" />
					{{$t('Feeds')}}
				</div>
				<template v-if="userInfo.is_page">
					<div class="profile-menu-list-item flex flex-1 min-w-[140px] items-center justify-center rounded-none md:rounded-base-lg gap-2 p-3 text-base-sm cursor-pointer" :class="(currentTab === 'page_info'?'active bg-primary-color text-white dark:bg-dark-primary-color':'bg-white dark:bg-slate-800')" @click="changeTab('page_info')">
						<BaseIcon name="info" />
						{{$t('Info')}}
					</div>
					<div class="profile-menu-list-item flex flex-1 min-w-[140px] items-center justify-center rounded-none md:rounded-base-lg gap-2 p-3 text-base-sm cursor-pointer" :class="(currentTab === 'media'?'active bg-primary-color text-white dark:bg-dark-primary-color':'bg-white dark:bg-slate-800')" @click="changeTab('media')">
						<BaseIcon name="media" />
						{{$t('Media')}}
					</div>
					<div v-if="isOwnerPage || userInfo.review_enable" class="profile-menu-list-item flex flex-1 min-w-[140px] items-center justify-center rounded-none md:rounded-base-lg gap-2 p-3 text-base-sm cursor-pointer" :class="(currentTab === 'review'?'active bg-primary-color text-white dark:bg-dark-primary-color':'bg-white dark:bg-slate-800')" @click="changeTab('review')">
						<BaseIcon name="star" />
						{{$t('Reviews')}}
					</div>		
				</template>
				<div v-else class="profile-menu-list-item flex flex-1 min-w-[140px] items-center justify-center rounded-none md:rounded-base-lg gap-2 p-3 text-base-sm cursor-pointer" :class="(currentTab === 'info'?'active bg-primary-color text-white dark:bg-dark-primary-color':'bg-white dark:bg-slate-800')" @click="changeTab('info')">
					<BaseIcon name="info" />
					{{$t('Info')}}
				</div>
			</div>
			<Component :is="profileComponent" :userInfo="userInfo" @change_tab="changeTab" @update_user_info="updateUserInfo"></Component>
		</template>
		<template v-else>
			<div class="main-content-section bg-white text-center p-5 mb-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800">
				<template v-if="isPrivacyFollower(userInfo.privacy)">
					<div class="text-base-lg font-bold mb-1">{{ userInfo.is_page ? $t('This Page is Private') : $t('This Account is Private') }}</div>
					<div class="text-base-sm">{{ $t('Follow to see their posts.') }}</div>
				</template>
				<template v-if="isPrivacyOnlyme(userInfo.privacy)">
					<div class="text-base-lg font-bold">{{ userInfo.is_page ? $t('This Page is Private') : $t('This Account is Private') }}</div>
				</template>
			</div>
		</template>	
	</div>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { changeUrl } from '@/utility'
import Constant from '@/utility/constant'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import UserFeeds from '@/pages/profile/UserFeeds.vue'
import UserInfo from '@/pages/profile/UserInfo.vue'
import UserPageInfo from '@/pages/profile/UserPageInfo.vue'
import UserPageMedia from '@/pages/profile/UserPageMedia.vue'
import UserPageReview from '@/pages/profile/UserPageReview.vue'
import { useAuthStore } from '@/store/auth'
import { useActionStore } from '@/store/action'
import { useAppStore } from '@/store/app'
import { usePostStore } from '@/store/post'
import { useProfileStore } from '@/store/profile'

export default {
	components: {
		BaseIcon
	},
	props: ['data', 'params', 'position'],
	data() {
		return {
			currentTab: this.params.tab ? this.params.tab : ''
		}
	},
	mounted(){
		this.getUserInfo(this.params.user_name)
		if(this.user.user_name === this.params.user_name){
			this.setIsMyProfilePage(true)
		}
	},
	unmounted() {
		this.setIsMyProfilePage(false)
		this.setUserInfo()
	},
	computed: {
		...mapState(useProfileStore, ['userInfo']),
		...mapState(useAuthStore, ['user', 'authenticated']),
		isOwnerPage(){
            return this.userInfo.id === this.user.id
        },
		profileComponent() {
			switch(this.currentTab){
				case 'info':
					return UserInfo;
				case 'page_info':
					return UserPageInfo;
				case 'media':
					return UserPageMedia
				case 'review':
					return UserPageReview
				default: 
					return UserFeeds;
			}
		}
	},
	methods: {
		...mapActions(useActionStore, ['updateFollowStatus']),
		...mapActions(useAppStore, ['setErrorLayout']),
		...mapActions(usePostStore, ['setIsMyProfilePage']),
		...mapActions(useProfileStore, ['getUserInfo', 'setUserInfo']),
		changeTab(name) {
			this.currentTab = name
			let userUrl = this.$router.resolve({
				name: 'profile',
				params: { 'user_name': this.userInfo.user_name }
			});
			changeUrl(userUrl.fullPath + (name != '' ? '/' + name : ''))
		},
		updateUserInfo(value){
			this.userInfo[Object.keys(value)] = value[Object.keys(value)]
		},
		isPrivacyFollower(privacy){
			return privacy == Constant.USER_PRIVACY_FOLLOWER
		},
		isPrivacyOnlyme(privacy){
			return privacy == Constant.USER_PRIVACY_ONLYME
		}
	}
}
</script>