<template>
	<Loading v-if="loadingPostsList"/>
	<div v-else>
		<div v-if="postsList.length > 0" class="pb-5">
			<TransitionGroup name="fade">
				<PostListsItem v-for="post in postsList" :key="post.id" :post="post"></PostListsItem>
			</TransitionGroup>
			<InfiniteLoading @infinite="loadMoreDiscoveryFeeds">	
				<template #spinner>
					<Loading />
				</template>
				<template #complete><span></span></template>
			</InfiniteLoading>
		</div>
		<div v-else class="main-content-section bg-white border border-white text-main-color p-10 text-center rounded-none mb-base-2 md:rounded-base-lg dark:bg-slate-800 dark:border-slate-800 dark:text-white">
			<div class="text-base-lg font-bold mb-base-2">{{$t('Nothing to see here yet')}}</div>
			<div class="text-base-sm">{{$t('Start following people and tags to see updated posts')}}</div>
		</div>
	</div>
</template>

<script>
import {mapActions, mapState } from 'pinia'
import PostListsItem from '@/components/posts/PostListsItem.vue'
import Loading from '@/components/utilities/Loading.vue'
import InfiniteLoading from 'v3-infinite-loading'
import { useAuthStore } from '../../store/auth'
import { usePostStore } from '../../store/post'

export default {
    components: {
		PostListsItem,
		Loading,
		InfiniteLoading
	},
	props: ['user'],
	computed: {
		...mapState(useAuthStore, ['authenticated']),
		...mapState(usePostStore, ['postsList', 'loadingPostsList']),
    },
    data(){
		return{
			currentPage: 1
		}
    },
    mounted(){
		this.getWatchPostsList(this.currentPage)
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
		...mapActions(usePostStore, ['getWatchPostsList','unsetPostsList']),
		loadMoreDiscoveryFeeds($state) {
			this.getWatchPostsList(++this.currentPage).then((response) => {
				if(response.length === 0){
					$state.complete()
				}else{
					$state.loaded()
				}
			})
		}
	}
}
</script>