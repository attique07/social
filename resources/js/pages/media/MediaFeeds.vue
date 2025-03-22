<template>
	<Loading v-if="loadingPostsList"/>
	<div v-else>
		<div v-if="postsList.length > 0" class="pb-5">
			<div class="grid grid-cols-3 gap-1 md:gap-base-2">
				<MasonryPostListsItem v-for="postItem in postsList" :key="postItem.id" :post="postItem"/>
			</div>
			<InfiniteLoading @infinite="loadMoreMediaFeeds">	
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
import { mapState, mapActions } from 'pinia'
import { usePostStore } from '@/store/post'
import Loading from '@/components/utilities/Loading.vue'
import InfiniteLoading from 'v3-infinite-loading'
import MasonryPostListsItem from '@/components/posts/MasonryPostListsItem.vue'

export default {
    components: {
		Loading,
		InfiniteLoading,
		MasonryPostListsItem
	},
	props: ['user'],
	computed: {
		...mapState(usePostStore, ['postsList', 'loadingPostsList']),
    },
    data(){
		return{
			currentPage: 1
		}
    },
    mounted(){
		this.getMediaPostsList(this.currentPage);
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
		...mapActions(usePostStore, ['getMediaPostsList', 'unsetPostsList']),
		loadMoreMediaFeeds($state) {
			this.getMediaPostsList(++this.currentPage).then((response) => {
				if(response.length === 0){
					$state.complete()
				}else{
					$state.loaded()
				}
			})
		}
	},
}
</script>