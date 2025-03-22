<template>
    <Error v-if="error">{{error}}</Error>
    <Loading v-if="loading_users"/>
    <UsersList v-else :usersList="followingUsersList"/>
    <div v-if="loadmore_status" class="text-center">
        <BaseButton @click="getFollowingUsersList(user.id, page)">{{$t('View more')}}</BaseButton>
    </div>
</template>
<script>
import { getFollowingUsers } from '../../api/follow';
import Error from '../utilities/Error.vue';
import Loading from '../utilities/Loading.vue';
import UsersList from '../lists/UsersList.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';

export default{
    components: { Error, Loading, UsersList, BaseButton },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            error: null,
            loadmore_status: false,
            loading_users: true,
            followingUsersList: [],
            page: 1
        }
    },
    mounted() {
        this.getFollowingUsersList(this.user.id, this.page)
    },
    computed: {
        user(){
            return this.dialogRef.data.user
        }
    },
    methods: {
        async getFollowingUsersList(userId, page){
            try {             
				const response = await getFollowingUsers(userId, page)
                if(page === 1){
                    this.followingUsersList = response.items
                }else{
                    this.followingUsersList = window._.concat(this.followingUsersList, response.items);
                }
                if(response.has_next_page){
                    this.loadmore_status = true
                    this.page++;
                }else{
                    this.loadmore_status = false
                }
                this.loading_users = false
			} catch (error) {
                this.error = error
                this.loading_users = false
			}
        }
    } 
}
</script>