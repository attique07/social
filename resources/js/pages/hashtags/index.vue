<template>
	<h1 class="page-title mb-base-2">{{ currentTab === 'suggest' ? $t('Tags for you') : $t('Trending Tags') }}</h1>
	<div class="main-content-section bg-white border border-divider p-5 mb-base-2 rounded-base-lg dark:bg-slate-800 dark:border-slate-800">	
        <div class="search-hashtag mb-3">
            <div class="relative">
                <BaseIcon name="pencil" size="16" class="absolute top-3 start-2"/>
                <input type="text" @input="findHashtag()" v-model="hashtagKeyword" :placeholder="$t('Enter tags')" class="w-full text-sm bg-input-background-color text-input-text-color border border-input-border-color rounded py-2 ps-8 pe-2 placeholder:text-input-placeholder-color outline-none appearance-none dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:placeholder:text-slate-300">
            </div>
        </div>
        <Loading v-if="loadingSearchHashtags"/>
        <div v-else>
            <div v-if="searchHashtags.length > 0">
                <Error v-if="error">{{error}}</Error>	
                <HashtagsList :hashtagsList="searchHashtags" />
				<InfiniteLoading v-if="loadmoreStatus" @infinite="loadSearchHashtags">	
					<template #spinner>
						<Loading />
					</template>
					<template #complete><span></span></template>
				</InfiniteLoading>
            </div>
        </div>
	</div>
</template>
<script>
import { getSuggestSearchHashtags, getTrendingSearchHashtags } from '@/api/hashtag'
import Error from '@/components/utilities/Error.vue';
import Loading from '@/components/utilities/Loading.vue';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import HashtagsList from '@/components/lists/HashtagsList.vue';
import InfiniteLoading from 'v3-infinite-loading'
var typingTimer = null;

export default {
	components:{ BaseIcon, HashtagsList, Loading, Error, InfiniteLoading },
	props: ['tab', 'title'],
	data(){
		return{
			currentTab: this.tab ? this.tab : '',
			hashtagKeyword: '',
			error: null,
			loadmoreStatus: false,
            loadingSearchHashtags: true,
			searchHashtags: [],
            currentPage: 1
		}
	},
	mounted(){
		switch(this.currentTab){
			case 'trending':
				this.getTrendingSearchHashtagsList(this.currentPage, this.hashtagKeyword)
				break
			default: 
				this.getSuggestSearchHashtagsList(this.currentPage, this.hashtagKeyword)
		}
    },
	methods: {
		findHashtag(){
			this.currentPage = 1
			clearTimeout(typingTimer);
			typingTimer = setTimeout(() => 
				{
					switch(this.currentTab){
						case 'trending':
							this.getTrendingSearchHashtagsList(this.currentPage, this.hashtagKeyword)
							break
						default: 
							this.getSuggestSearchHashtagsList(this.currentPage, this.hashtagKeyword)
					}
				}
			, 400);
		},
		async getSuggestSearchHashtagsList(page, keyword){
			try {
				const response = await getSuggestSearchHashtags(page, keyword)
				// apply data to hashtags list
                if(page === 1){
                    this.searchHashtags = response.items
                }else{
                    this.searchHashtags = window._.concat(this.searchHashtags, response.items);
                }
				// check load more page
				if(response.has_next_page){
					this.loadmoreStatus = true
				}else{
					this.loadmoreStatus = false
				}
                this.loadingSearchHashtags = false
				return response
			} catch (error) {
				this.error = error
				this.loadingSearchHashtags = false
			}
		},
		async getTrendingSearchHashtagsList(page, keyword){
            try {
				const response = await getTrendingSearchHashtags(page, keyword)
				// apply data to hashtags list
                if(page === 1){
                    this.searchHashtags = response.items
                }else{
                    this.searchHashtags = window._.concat(this.searchHashtags, response.items);
                }
				// check load more page
				if(response.has_next_page){
					this.loadmoreStatus = true
				}else{
					this.loadmoreStatus = false
				}
                this.loadingSearchHashtags = false
				return response
			} catch (error) {
                this.error = error
                this.loadingSearchHashtags = false
			}
        },
		loadSearchHashtags($state){
			switch(this.currentTab){
				case 'trending':
					this.getTrendingSearchHashtagsList(++this.currentPage, this.hashtagKeyword).then((response) => {
						if(response.has_next_page){
							this.loadmoreStatus = true
							$state.loaded()
						}else{
							this.loadmoreStatus = false
							$state.complete()
						}
					})		
					break
				default: 
					this.getSuggestSearchHashtagsList(++this.currentPage, this.hashtagKeyword).then((response) => {
						if(response.has_next_page){
							this.loadmoreStatus = true
							$state.loaded()
						}else{
							this.loadmoreStatus = false
							$state.complete()
						}
					})		
			}
		}
	}
}
</script>