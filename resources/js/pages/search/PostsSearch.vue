<template>
    <Loading v-if="loadingPostsList" />
    <div v-else>
		<div v-if="postsList.length > 0" class="pb-5">
			<TransitionGroup name="fade">
				<PostListsItem v-for="post in postsList" :key="post.id" :post="post"></PostListsItem>
			</TransitionGroup>
			<InfiniteLoading v-if="loadmore_status" @infinite="loadSearchPostsList(this.search_type, this.queryData, this.type, this.currentPage)">	
				<template #spinner>
					<Loading />
				</template>
				<template #complete><span></span></template>
			</InfiniteLoading>
		</div>
		<div v-else class="widget-box bg-white p-base-2 text-center rounded-none mb-base-2 md:rounded-base-lg dark:bg-slate-800">
			<div class="text-base-sm">{{$t('No posts found')}}</div>
		</div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import PostListsItem from '@/components/posts/PostListsItem.vue'
import Loading from '@/components/utilities/Loading.vue'
import InfiniteLoading from 'v3-infinite-loading'
import { usePostStore } from '../../store/post'

export default {
    components: { PostListsItem, Loading, InfiniteLoading },
    props: ["search_type", "type", "query"],
    data(){
		return{
			queryData: this.query,
			loadmore_status: false,
			currentPage: 1
		}
    },
    computed: {
        ...mapState(usePostStore, ['postsList', 'loadingPostsList'])
    },
    mounted(){
		this.loadSearchPostsList(this.search_type, this.queryData, this.type, this.currentPage);
    },
	watch: {
        '$route'() {
			this.queryData = !window._.isNil(this.$route.query.q) ? this.$route.query.q : ''
			this.currentPage = 1
			if(this.queryData){
				this.loadSearchPostsList(this.search_type, this.queryData, this.type, this.currentPage)
			}
        }
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
        ...mapActions(usePostStore, ['getSearchPostsList', 'unsetPostsList']),
		loadSearchPostsList(search_type, query, type, page) {		
			this.getSearchPostsList({search_type, query, type, page}).then((response) => {
				if(response.length === 0){
					this.loadmore_status = false
				}else{
					this.loadmore_status = true
					this.currentPage++
				}
			})	
		}
    }
}
</script>