<template>
    <div class="main-content-section bg-white p-5 mb-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800">
        <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('Media') }}</h3>
        <Loading v-if="loadingPostsList"/>
        <div v-else>
            <div v-if="postsList.length > 0" class="pb-5">
                <div class="grid grid-cols-3 gap-base-2">
                    <MasonryPostListsItem v-for="postItem in postsList" :key="postItem.id" :post="postItem"/>
                </div>
                <InfiniteLoading @infinite="loadMoreMediaFeeds">	
                    <template #spinner>
                        <Loading />
                    </template>
                    <template #complete><span></span></template>
                </InfiniteLoading>
            </div>
            <div v-else class="main-content-section bg-white p-5 text-center rounded-none md:rounded-base-lg dark:bg-slate-800">
                <div class="text-base-lg font-bold">{{$t('Nothing to see here yet')}}</div>
            </div>
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
		this.getProfileMediaPostsList(this.userInfo.id, this.currentPage);
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
		...mapActions(usePostStore, ['getProfileMediaPostsList', 'unsetPostsList']),
		loadMoreMediaFeeds($state) {
			this.getProfileMediaPostsList(this.userInfo.id, ++this.currentPage).then((response) => {
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