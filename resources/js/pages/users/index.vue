<template>
	<h1 class="page-title mb-base-2">{{ currentTab === 'suggest' ? $t('Users for you') : $t('Popular Users') }}</h1>
	<div class="bg-white p-5 mb-base-2 rounded-base-lg dark:bg-slate-800">
		<BaseInputText :placeholder="$t('Enter Name')" :left_icon="'pencil'" v-model="hashtagKeyword" @input="findUsers()" class="mb-3"/>
        <Loading v-if="loadingSearchUsers"/>
        <div v-else>
            <div v-if="searchUsers.length">
                <Error v-if="error">{{error}}</Error>
				<UsersList :usersList="searchUsers" />
				<InfiniteLoading v-if="loadmoreStatus" @infinite="loadSearchUsers">				
					<template #spinner>
						<Loading />
					</template>
					<template #complete><span></span></template>
				</InfiniteLoading>
            </div>
			<div v-else class="text-center">{{$t('No users found')}}</div>
        </div>
	</div>
</template>
<script>
import { getSuggestSearchUsers, getTrendingSearchUsers } from '@/api/user'
import Error from '@/components/utilities/Error.vue';
import Loading from '@/components/utilities/Loading.vue';
import UsersList from '@/components/lists/UsersList.vue';
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import InfiniteLoading from 'v3-infinite-loading'
var typingTimer = null;

export default {
	components:{ Loading, Error, InfiniteLoading, UsersList, BaseInputText },
	props: ['tab'],
	data(){
		return{
			currentTab: this.tab ? this.tab : '',
			hashtagKeyword: '',
			error: null,
			loadmoreStatus: false,
            loadingSearchUsers: true,
			searchUsers: [],
            currentPage: 1
		}
	},
	mounted(){
		switch(this.currentTab){
			case 'trending':
				this.getTrendingSearchUsersList(this.currentPage, this.hashtagKeyword)
				break
			default: 
				this.getSuggestSearchUsersList(this.currentPage, this.hashtagKeyword)
		}
    },
	methods: {
		findUsers(){
			this.currentPage = 1
			clearTimeout(typingTimer);
			typingTimer = setTimeout(() => 
				{
					switch(this.currentTab){
						case 'trending':
							this.getTrendingSearchUsersList(this.currentPage, this.hashtagKeyword)
							break
						default: 
							this.getSuggestSearchUsersList(this.currentPage, this.hashtagKeyword)
					}
				}
			, 400);
		},
		async getSuggestSearchUsersList(page, keyword){
			try {
				const response = await getSuggestSearchUsers(page, keyword)
				// Apply data to users list
                if(page === 1){
                    this.searchUsers = response.items
                }else{
                    this.searchUsers = window._.concat(this.searchUsers, response.items);
                }
				// check load more page
				if(response.has_next_page){
					this.loadmoreStatus = true
				}else{
					this.loadmoreStatus = false
				}
				this.loadingSearchUsers = false
				return response
			} catch (error) {
				this.error = error
				this.loadingSearchUsers = false
			}
		},
		async getTrendingSearchUsersList(page, keyword){
            try {
				const response = await getTrendingSearchUsers(page, keyword)
				// Apply data to users list
                if(page === 1){
                    this.searchUsers = response.items
                }else{
                    this.searchUsers = window._.concat(this.searchUsers, response.items);
                }
				// check load more page
				if(response.has_next_page){
					this.loadmoreStatus = true
				}else{
					this.loadmoreStatus = false
				}
                this.loadingSearchUsers = false
				return response
			} catch (error) {
                this.error = error
                this.loadingSearchUsers = false
			}
        },
		loadSearchUsers($state){
			switch(this.currentTab){
				case 'trending':
					this.getTrendingSearchUsersList(++this.currentPage, this.hashtagKeyword).then((response) => {
						if(response.has_next_page){
							this.loadmoreStatus = true
							$state.loaded()
						}else{
							this.loadmoreStatus = false
							$state.complete()
						}
					})		
					break
				default: 
					this.getSuggestSearchUsersList(++this.currentPage, this.hashtagKeyword).then((response) => {
						if(response.has_next_page){
							this.loadmoreStatus = true
							$state.loaded()
						}else{
							this.loadmoreStatus = false
							$state.complete()
						}
					})		
			}
		}
	}
}
</script>