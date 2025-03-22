<template>
    <div class="tabs-list flex gap-base-2 mb-4">
        <button @click="filterFollowingUsersList('user')" class="tabs-list-item p-base-2 rounded-base-lg" :class="followingType === 'user' ? 'active bg-primary-color text-white dark:bg-dark-primary-color' : 'bg-web-wash dark:bg-dark-web-wash'">{{ $t('Users') }}</button>
        <button @click="filterFollowingUsersList('page')" class="tabs-list-item p-base-2 rounded-base-lg" :class="followingType === 'page' ? 'active bg-primary-color text-white dark:bg-dark-primary-color' : 'bg-web-wash dark:bg-dark-web-wash'">{{ $t('Pages') }}</button>
    </div>
    <Error v-if="error">{{error}}</Error>
    <Loading v-if="loadingUsers"/>
    <UsersList v-else :users-list="followingUsersList" :empty-message="followingType === 'user' ? $t('No Users') : $t('No Pages')" />
    <div v-if="loadmoreStatus" class="text-center">
        <BaseButton @click="getFollowingUsersList(followingType, page)">{{$t('View more')}}</BaseButton>
    </div>
</template>
<script>
import { getMyFollowingUsers } from '@/api/follow';
import Error from '@/components/utilities/Error.vue';
import Loading from '@/components/utilities/Loading.vue';
import UsersList from '@/components/lists/UsersList.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';

export default{
    components: { Error, Loading, UsersList, BaseButton },
    data(){
        return{
            error: null,
            loadmoreStatus: false,
            loadingUsers: true,
            followingUsersList: [],
            followingType: 'user',
            page: 1
        }
    },
    mounted() {
        this.getFollowingUsersList(this.followingType, this.page);
    },
    methods: {
        async getFollowingUsersList(type, page){
            try {             
				const response = await getMyFollowingUsers(type, page)
                if(page === 1){
                    this.followingUsersList = response.items
                }else{
                    this.followingUsersList = window._.concat(this.followingUsersList, response.items);
                }
                if(response.has_next_page){
                    this.loadmoreStatus = true
                    this.page++;
                }else{
                    this.loadmoreStatus = false
                }
                this.loadingUsers = false
			} catch (error) {
                this.error = error
                this.loadingUsers = false
			}
        },
        filterFollowingUsersList(type){
            this.followingType = type
            this.page = 1
            this.getFollowingUsersList(this.followingType, this.page);
        }
    } 
}
</script>