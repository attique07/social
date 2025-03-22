<template>
	<div class="bg-white p-base-2 rounded-none md:rounded-base-lg mb-base-2 dark:bg-slate-800">
        <Error v-if="error">{{error}}</Error>
		<Loading v-if="loadingPages"/>
        <template v-else>
            <div v-if="searchPagesList.length > 0">
                <UserPagesList :pagesList="searchPagesList" />
                <InfiniteLoading @infinite="loadMorePages">	
                    <template #spinner>
                        <Loading />
                    </template>
                    <template #complete><span></span></template>
                </InfiniteLoading>
            </div>
            <div v-else class="text-center">{{$t('No pages found')}}</div>
        </template>
	</div>
</template>

<script>
import { getSearchResults } from '@/api/search';
import Error from '@/components/utilities/Error.vue'
import Loading from '@/components/utilities/Loading.vue';
import UserPagesList from '@/components/lists/UserPagesList.vue';
import InfiniteLoading from 'v3-infinite-loading'

export default {
	components:{ Error, Loading, UserPagesList, InfiniteLoading },
	props: ["search_type", "type", "query"],
	data(){
        return{
            error: null,
            queryData: this.query,
            loadingPages: true,
            searchPagesList: [],
            currentPage: 1
        }
    },
    mounted(){
        this.getSearchPagesList(this.queryData, this.currentPage)
    },
    watch: {
        '$route'() {
			this.queryData = !window._.isNil(this.$route.query.q) ? this.$route.query.q : ''
            this.currentPage = 1
            if(this.queryData){
                this.getSearchPagesList(this.queryData, this.currentPage)
            }
        }
    },
    methods: {
        async getSearchPagesList(query, page){
            try {             
				const response = await getSearchResults(this.search_type, query, this.type, page)

                // Apply data to users list
                if(this.currentPage === 1){
                    this.searchPagesList = response
                }else{
                    this.searchPagesList = window._.concat(this.searchPagesList, response);
                }

                this.loadingPages = false
                return response
			} catch (error) {
                this.error = error
                this.loadingPages = false
			}
        },
        loadMorePages($state) {
			this.getSearchPagesList(this.queryData, ++this.currentPage).then((response) => {
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