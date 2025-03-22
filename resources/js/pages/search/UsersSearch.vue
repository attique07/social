<template>
	<div class="bg-white p-base-2 rounded-none md:rounded-base-lg mb-base-2 dark:bg-slate-800">
        <Error v-if="error">{{error}}</Error>
		<Loading v-if="loading_users"/>
        <template v-else>
            <div v-if="searchUsersList.length > 0">
                <UsersList :usersList="searchUsersList" :showFollower="true"/>
                <InfiniteLoading @infinite="loadMorePosts">	
                    <template #spinner>
                        <Loading />
                    </template>
                    <template #complete><span></span></template>
                </InfiniteLoading>
            </div>
            <div v-else class="text-center">{{$t('No users found')}}</div>
        </template>
	</div>
</template>

<script>
import { getSearchResults } from '@/api/search';
import Error from '@/components/utilities/Error.vue'
import Loading from '@/components/utilities/Loading.vue';
import UsersList from '@/components/lists/UsersList.vue';
import InfiniteLoading from 'v3-infinite-loading'

export default {
	components:{ Error, Loading, UsersList, InfiniteLoading },
	props: ["search_type", "type", "query"],
	data(){
        return{
            error: null,
            queryData: this.query,
            loading_users: true,
            searchUsersList: [],
            currentPage: 1
        }
    },
    mounted(){
        this.getSearchUsersList(this.queryData, this.currentPage)
    },
    watch: {
        '$route'() {
			this.queryData = !window._.isNil(this.$route.query.q) ? this.$route.query.q : ''
            this.currentPage = 1
            if(this.queryData){
                this.getSearchUsersList(this.queryData, this.currentPage)
            }
        }
    },
    methods: {
        async getSearchUsersList(query, page){
            try {             
				const response = await getSearchResults(this.search_type, query, this.type, page)

                // Apply data to users list
                if(this.currentPage === 1){
                    this.searchUsersList = response
                }else{
                    this.searchUsersList = window._.concat(this.searchUsersList, response);
                }

                this.loading_users = false
                return response
			} catch (error) {
                this.error = error
                this.loading_users = false
			}
        },
        loadMorePosts($state) {
			this.getSearchUsersList(this.queryData, ++this.currentPage).then((response) => {
				if (response.length === 0) {
					$state.complete()
				} else {
					$state.loaded()
				}
			})
		}
    } 
}
</script>

<style>

</style>