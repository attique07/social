<template>
    <div class="tabs-list flex gap-base-2 mb-4">
        <button @click="filterFollowerUsersList('user')" class="tabs-list-item p-base-2 rounded-base-lg" :class="followerType === 'user' ? 'active bg-primary-color text-white dark:bg-dark-primary-color' : 'bg-web-wash dark:bg-dark-web-wash'">{{ $t('Users') }}</button>
        <button @click="filterFollowerUsersList('page')" class="tabs-list-item p-base-2 rounded-base-lg" :class="followerType === 'page' ? 'active bg-primary-color text-white dark:bg-dark-primary-color' : 'bg-web-wash dark:bg-dark-web-wash'">{{ $t('Pages') }}</button>
    </div>
    <Error v-if="error">{{error}}</Error>
    <Loading v-if="loadingUsers"/>
    <UsersList v-else :users-list="followerUsersList" :empty-message="followerType === 'user' ? $t('No Users') : $t('No Pages')" />
    <div v-if="loadmoreStatus" class="text-center">
        <BaseButton @click="getFollowerUsersList(followerType, page)">{{$t('View more')}}</BaseButton>
    </div>
</template>
<script>
import { getMyFollowerUsers } from '@/api/follow';
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
            followerUsersList: [],
            followerType: 'user',
            page: 1
        }
    },
    mounted() {
        this.getFollowerUsersList(this.followerType, this.page);
    },
    methods: {
        async getFollowerUsersList(type, page){
            try {
				const response = await getMyFollowerUsers(type, page)
                if(page === 1){
                    this.followerUsersList = response.items
                }else{
                    this.followerUsersList = window._.concat(this.followerUsersList, response.items);
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
        filterFollowerUsersList(type){
            this.followerType = type
            this.page = 1
            this.getFollowerUsersList(this.followerType, this.page);
        }
    } 
}
</script>