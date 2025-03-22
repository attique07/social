<template>
    <div class="tabs-list flex gap-base-2 mb-4">
        <button @click="filterBlockUsersList('user')" class="tabs-list-item p-base-2 rounded-base-lg" :class="blockType === 'user' ? 'active bg-primary-color text-white dark:bg-dark-primary-color' : 'bg-web-wash dark:bg-dark-web-wash'">{{ $t('Users') }}</button>
        <button @click="filterBlockUsersList('page')" class="tabs-list-item p-base-2 rounded-base-lg" :class="blockType === 'page' ? 'active bg-primary-color text-white dark:bg-dark-primary-color' : 'bg-web-wash dark:bg-dark-web-wash'">{{ $t('Pages') }}</button>
    </div>
    <Error v-if="error">{{error}}</Error>
    <Loading v-if="loadingUsers"/>
    <UsersList v-else :users-list="blockUsersList" :list-type="'block'" :empty-message="blockType === 'user' ? $t('No Users') : $t('No Pages')" />
    <div v-if="loadmoreStatus" class="text-center">
        <BaseButton @click="getBlockUsersList(blockType, page)">{{$t('View more')}}</BaseButton>
    </div>
</template>
<script>
import { getBlockUsersList } from '@/api/user'
import Error from '@/components/utilities/Error.vue';
import Loading from '@/components/utilities/Loading.vue'
import UsersList from '@/components/lists/UsersList.vue';
import BaseButton from '@/components/inputs/BaseButton.vue'

export default{
    components: { Error, Loading, UsersList, BaseButton },
    data(){
        return{
            error: null,
            loadmoreStatus: false,
            loadingUsers: true,
            blockUsersList: [],
            blockType: 'user',
            page: 1
        }
    },
    mounted() {
        this.getBlockUsersList(this.blockType, this.page);
    },
    methods: {
        async getBlockUsersList(type, page){
            try {
				const response = await getBlockUsersList(type, page)
                if(page === 1){
                    this.blockUsersList = response.items
                }else{
                    this.blockUsersList = window._.concat(this.blockUsersList, response.items);
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
        filterBlockUsersList(type){
            this.blockType = type
            this.page = 1
            this.getBlockUsersList(this.blockType, this.page);
        }
    } 
}
</script>