<template>
	<Loading v-if="loadingPostsList"/>
	<div v-else>
		<div v-if="postsList.length > 0" class="pb-5">
			<TransitionGroup name="fade">
				<PostListsItem v-for="post in postsList" :key="post.id" :post="post"></PostListsItem>
			</TransitionGroup>
			<InfiniteLoading @infinite="loadMoreUserFeeds">	
				<template #spinner>
					<Loading />
				</template>
				<template #complete><span></span></template>
			</InfiniteLoading>
		</div>
		<div v-else class="main-content-section bg-white border border-white text-main-color p-5 text-center rounded-none mb-base-2 md:rounded-base-lg dark:bg-slate-800 dark:border-slate-800 dark:text-white">
			<div class="text-base-sm">{{$t('Nothing to see here yet')}}</div>
		</div>
	</div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import PostListsItem from '../../components/posts/PostListsItem.vue'
import Loading from '../../components/utilities/Loading.vue'
import InfiniteLoading from 'v3-infinite-loading'
import { usePostStore } from '../../store/post'

export default {
    components: {
		PostListsItem,
		Loading,
		InfiniteLoading
	},
	props: ['userInfo'],
	computed: {
		...mapState(usePostStore, ['postsList', 'loadingPostsList']),
    },
    data(){
		return{
			currentPage: 1
		}
    },
    mounted(){
		this.getUserPostsList(this.userInfo.id, this.currentPage);
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
		...mapActions(usePostStore, ['getUserPostsList', 'unsetPostsList']),
		loadMoreUserFeeds($state) {
			this.getUserPostsList(this.userInfo.id, ++this.currentPage).then((response) => {
				if(response.length === 0){
					$state.complete()
				}else{
					$state.loaded()
				}
			})
		}
	},
	emits: ['change_tab', 'update_user_info']
}
</script>