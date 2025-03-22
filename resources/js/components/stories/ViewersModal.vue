<template>
    <Error v-if="error">{{error}}</Error>
    <Loading v-if="loading_users"/>
    <UsersList v-else :usersList="viewersList"/>
    <div v-if="loadmore_status" class="text-center">
        <BaseButton @click="getStoryViewers(dialogRef.data.itemId, page)">{{$t('View more')}}</BaseButton>
    </div>
</template>

<script>
import { getViewers } from '@/api/stories';
import UsersList from '@/components/lists/UsersList.vue'
import Error from '@/components/utilities/Error.vue'
import Loading from '@/components/utilities/Loading.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';

export default {
    components: { Error, UsersList, Loading, BaseButton },
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
            viewersList: [],
            page: 1
        }
    },
    mounted(){
        if(this.dialogRef.data.itemId){
            this.getStoryViewers(this.dialogRef.data.itemId, this.page)
        }
    },
    methods: {
        async getStoryViewers(itemId, page) {
            try {
                const response = await getViewers(itemId, page)
                if(page === 1){
                    this.viewersList = response.items
                }else{
                    this.viewersList = window._.concat(this.viewersList, response.items);
                }
                if(response.has_next_page){
                    this.loadmore_status = true
                    this.page++;
                }else{
                    this.loadmore_status = false
                }
                this.loading_users = false
            }
            catch (error) {
                this.error = error
                this.loading_users = false
            }
        }
    }
}
</script>