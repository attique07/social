<template>
	<div class="main-content-section bg-white border border-divider p-5 rounded-base-lg dark:bg-slate-800 dark:border-slate-800">	
		<!-- Select Tags Section -->
		<div v-if="currentStep === 1">
			<div class="flex items-center justify-between mb-3">
				<h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{$t('Select Tags to Follow')}}</h3>
			</div>
			<HashtagsBoxList :hashtagsList="followingHashtags" target="_blank" @unfollow_signup_hashtag="removeFollowingHashtags"/>
			<div class="mt-base-2 mb-3">
				<div class="relative">
					<BaseIcon name="pencil" size="16" class="text-input-icon-color absolute top-3 start-2"/>
					<input type="text" @input="findHashtag()" v-model="hashtagKeyword" :placeholder="$t('Enter topics')" class="w-full text-sm bg-input-background-color text-input-text-color border border-input-border-color rounded px-8 py-2 placeholder:text-input-placeholder-color outline-none appearance-none dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:placeholder:text-slate-300">
					<button v-if="hashtagKeyword != ''" class="absolute top-3 end-2" @click="deleteHashtagSearch()">
						<BaseIcon name="close" size="16" class="text-input-icon-color"/>
					</button>
				</div>
			</div>
			<Loading v-if="loading_suggest_hashtags"/>
			<div v-else>
				<div v-if="suggestHashtags.length > 0">
					<Error v-if="error">{{error}}</Error>	
					<HashtagsList :hashtagsList="suggestHashtags" target="_blank" @follow_hashtag="followHashtag" @unfollow_hashtag="unFollowHashtag"/>
				</div>
			</div>
		</div>
		<!-- End Select Tags Section -->

		<!-- Select Users Section -->
		<div v-if="currentStep === 2">
			<div class="flex items-center justify-between mb-3">
				<h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{$t('Select Users to Follow')}}</h3>
			</div>
			<UsersBoxList :usersList="followingUsersList" target="_blank" @unfollow_user="removeFromFollowingUsers"/>					
			<div class="mt-base-2 mb-3">
				<div class="relative">
					<BaseIcon name="pencil" size="16" class="text-input-icon-color absolute top-3 start-2"/>
					<input type="text" @input="findUser()" v-model="UserKeyword" :placeholder="$t('Enter name')" class="w-full text-sm bg-input-background-color text-input-text-color border border-input-border-color rounded px-8 py-2 placeholder:text-input-placeholder-color outline-none appearance-none dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:placeholder:text-slate-300">
					<button v-if="UserKeyword != ''" @click="deleteUserSearch()" class="absolute top-3 end-2">
						<BaseIcon name="close" size="16" class="text-input-icon-color"/>				
					</button>
				</div>
			</div>
			<Loading v-if="loading_suggest_users"/>
			<UsersList v-else :usersList="suggestUserLists" target="_blank" @follow_user="followUser" @unfollow_user="unFollowUser" :showFollower="true" :showUserName="false" :showPopover="false"/>
		</div>
		<!-- End Select Users Section -->

		<div class="text-center mt-10">
			<BaseButton class="font-bold" @click="nextStep()">{{$t('Continue')}}</BaseButton>
		</div>
	</div>
</template>
<script>
import { mapState } from 'pinia';
import { getSuggestSignupHashtags } from '@/api/hashtag'
import { getSuggestUsers } from '@/api/follow';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import HashtagsList from '@/components/lists/HashtagsList.vue';
import UsersList from '@/components/lists/UsersList.vue';
import HashtagsBoxList from '@/components/lists/HashtagsBoxList.vue';
import UsersBoxList from '@/components/lists/UsersBoxList.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import Loading from '@/components/utilities/Loading.vue';
import Error from '@/components/utilities/Error.vue';
import { useAuthStore } from '../../store/auth';
var typingTimer = null;
const authStore = useAuthStore()

export default {
	components:{ BaseIcon, BaseButton, HashtagsList, Loading, Error, HashtagsBoxList, UsersList, UsersBoxList },
	computed: {
		...mapState(useAuthStore, ['user'])
	},
	data(){
		return{
			error: null,
			hashtagKeyword: '',
			UserKeyword: '',
			loading_suggest_hashtags: true,
            followingHashtags: [],
			suggestHashtags: null,
			loading_suggest_users: true,
            suggestUserLists: null,
			followingUsersList: [],
			currentStep: 1
		}
	},
	mounted(){
		this.getSuggestHashtagsList('')
		this.getSuggestUsersList('')
		authStore.loginFirst()
    },
	methods: {
		findHashtag(){
			clearTimeout(typingTimer);
			typingTimer = setTimeout(() => 
				this.getSuggestHashtagsList(this.hashtagKeyword)
			, 400);
		},
		deleteHashtagSearch(){
			this.hashtagKeyword = ''
			this.findHashtag()
		},
		findUser(){
			clearTimeout(typingTimer);
			typingTimer = setTimeout(() => 
				this.getSuggestUsersList(this.UserKeyword)
			, 400);
		},
		deleteUserSearch(){
			this.UserKeyword = ''
			this.findUser()
		},
		async getSuggestHashtagsList(keyword){
			try {
				const response = await getSuggestSignupHashtags(keyword)
				this.suggestHashtags = response
				this.loading_suggest_hashtags = false
			} catch (error) {
				this.error = error
				this.loading_suggest_hashtags = false
			}
		},
		async getSuggestUsersList(keyword){
            try {
				const response = await getSuggestUsers(keyword)
                this.suggestUserLists = response
                this.loading_suggest_users = false
			} catch (error) {
                this.loading_suggest_users = false
			}
        },
		removeFollowingHashtags(hashtagName){
			this.followingHashtags = this.followingHashtags.filter(followingHashtagItem => followingHashtagItem.name !== hashtagName)
			this.suggestHashtags.map(suggestHashtagItem => {
				if (suggestHashtagItem.name === hashtagName) {
					suggestHashtagItem.is_followed = false
				}
				return suggestHashtagItem
			});
		},
		followHashtag(hashtagName){
			this.followingHashtags.push({name: hashtagName})
		},
		unFollowHashtag(hashtagName){
			this.followingHashtags = this.followingHashtags.filter(followingHashtagItem => followingHashtagItem.name !== hashtagName)
		},
		removeFromFollowingUsers(user){
			this.followingUsersList = this.followingUsersList.filter(followingUsersItem => followingUsersItem.id !== user.id)
			this.suggestUserLists.map(suggestUserItem => {
				if (suggestUserItem.id === user.id) {
					suggestUserItem.is_followed = false
				}
				return suggestUserItem
			});
		},
		followUser(user){
			this.followingUsersList.push({
				id: user.id,
				name: user.name,
				user_name: user.user_name,
				avatar: user.avatar
			})
		},
		unFollowUser(user){
			this.followingUsersList = this.followingUsersList.filter(followingUsersItem => followingUsersItem.id !== user.id)
		},
		nextStep(){
			if(this.currentStep === 2){
				authStore.me()
				this.$router.push({ name: "home" });
			}else{
				this.currentStep++;
			}
		}
	}
}
</script>