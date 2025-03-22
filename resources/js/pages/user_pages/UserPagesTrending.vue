<template>
    <div>
        <Loading v-if="loadingPagesList"/>
        <UserPagesList v-else :pagesList="pagesList"/>
        <div v-if="loadMoreStatus" class="text-center mt-5">
            <BaseButton @click="getPagesList(searchPage)">{{$t('View more')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import Loading from '@/components/utilities/Loading.vue';
import UserPagesList from '@/components/lists/UserPagesList.vue';
import { getTrendingUserPages } from '@/api/page'
import BaseButton from '@/components/inputs/BaseButton.vue';

export default {
    components: { Loading, UserPagesList, BaseButton },
    data(){
        return {
            searchPage: 1,
            loadingPagesList: true,
            loadMoreStatus: false,
            pagesList: []
        }
    },
    mounted() {
        this.getPagesList(this.searchPage)
    },
    methods: {
        async getPagesList(page){
            try {
                const response = await getTrendingUserPages(page)
                if(page === 1){
                    this.pagesList = []
                }
                this.pagesList = window._.concat(this.pagesList, response.items);
                if(response.has_next_page){
                    this.loadMoreStatus = true
                    this.searchPage++;
                }else{
                    this.loadMoreStatus = false
                }
                this.loadingPagesList = false
            } catch (error) {
                console.log(error)
                this.loadingPagesList = false
            }
        }
    }
}
</script>