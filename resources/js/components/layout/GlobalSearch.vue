<template>
    <div class="global-search-header relative w-full max-w-md">
        <form autocomplete="off" @submit.prevent="searchText()">
            <BaseInputText class="global-search-header-input" :placeholder="$t('Search')" :autofocus="autofocus" :left_icon="'search'" v-model="searchModal" @input="onEnterSearch()" @focus="onEnterSearch()" autocomplete="off"/>
        </form>
        <div v-if="isShownSearchBox" class="dropdown-menu-box max-h-96 overflow-y-auto absolute left-0 right-0 top-10 z-[9000] bg-white shadow-md rounded-base-lg dark:bg-slate-800 dark:shadow-slate-500" v-click-outside="closeSearchBox">     					                   
            <div class="global-search-suggestion-box p-5" @click="closeSearchBox()">
                <div v-if="searchContent">
                    <router-link :to="{name: 'search', params: {search_type: 'text', type: 'post'}, query:{q: searchContent}}" class="flex items-center mb-4 text-sm last:mb-0 text-inherit">				
                        <div class="global-search-suggestion-box-icon flex items-center justify-center w-base-13 h-base-13 text-main-color dark:text-white"><BaseIcon name="search"/></div>
                        <div class="global-search-suggestion-box-text flex-1 mx-base-2">
                            <div v-html="matchingSearch(searchResultsList['text'])"></div>
                        </div>
                    </router-link>							
                    <router-link v-for="(searchHashtagResultItem, index) in searchResultsList.hashtags" :key="index" :to="{name: 'search', params: {search_type: 'hashtag', type: type}, query: {q: searchHashtagResultItem.name}}" class="flex items-center mb-4 text-sm last:mb-0 text-inherit">
                        <div class="global-search-suggestion-box-icon flex items-center justify-center w-base-13 h-base-13 text-main-color dark:text-white"><BaseIcon name="search"/></div>
                        <div class="flex-1 mx-base-2 global-search-suggestion-box-title min-w-0">
                            <div v-html="hashtagChar + matchingSearch(searchHashtagResultItem.name)"></div>
                            <div class="global-search-suggestion-box-sub text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(searchHashtagResultItem.post_count, $t('[number] Post'), $t('[number] Posts')) }}</div>
                        </div>
                    </router-link>
                    <router-link :to="{name: 'profile', params: {user_name: searchUserResultItem.user_name}}" v-for="searchUserResultItem in searchResultsList.users" :key="searchUserResultItem.id" class="flex items-center mb-4 last:mb-0 text-inherit">
                        <Avatar :user="searchUserResultItem" :size="'3.125rem'" :activePopover="false"/>
                        <div class="flex-1 mx-base-2 global-search-suggestion-box-title min-w-0">
                            <UserName :user="searchUserResultItem" :activePopover="false"/>
                            <p v-if="searchUserResultItem.show_follower" class="global-search-suggestion-box-sub flex items-center text-xs text-sub-color dark:text-slate-400"><BaseIcon name="people" size="16" class="me-1"/>{{ $filters.numberShortener(searchUserResultItem.follower_count, $t('[number] Follower'), $t('[number] Followers')) }}</p>
                        </div>
                    </router-link>
                    <router-link :to="{name: 'profile', params: {user_name: searchPageResultItem.user_name}}" v-for="searchPageResultItem in searchResultsList.pages" :key="searchPageResultItem.id" class="flex items-center mb-4 last:mb-0 text-inherit">
                        <Avatar :user="searchPageResultItem" :size="'3.125rem'" :activePopover="false"/>
                        <div class="flex-1 mx-base-2 global-search-suggestion-box-title min-w-0">
                            <UserName :user="searchPageResultItem" :activePopover="false"/>
                            <div v-if="searchPageResultItem.categories.length" class="global-search-suggestion-box-sub flex flex-wrap items-center text-xs text-sub-color dark:text-slate-400">
                                <span v-for="(category, index) in searchPageResultItem.categories" :key="category.id">
                                    <router-link :to="{name: 'user_pages', query: {category_id: category.id}}" class="text-sub-color dark:text-slate-400">{{ category.name }}</router-link>
                                    {{ (index === searchPageResultItem.categories.length - 1) ? '' : '.&nbsp;'}}
                                </span>
                            </div>
                        </div>
                    </router-link>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { getSearchSuggest } from '@/api/search'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import Constant from '@/utility/constant'
import BaseIcon from '@/components/icons/BaseIcon.vue'
var typingTimer = null

export default {
    components:{ BaseIcon, Avatar, UserName, BaseInputText },
    props: {
        autofocus: {
            type: Boolean,
			default: false
        }
    },
    data(){
        return{
            type: null,
            isShownSearchBox: false,
			searchContent: null,
			searchResultsList: {},
            mentionChar: Constant.MENTION,
			hashtagChar: Constant.HASHTAG,
            searchModal: this.$route.query.q ? this.$route.query.q : null
        }
    },
    mounted(){
        this.type = !window._.isNil(this.$route.params.type) ? this.$route.params.type : 'post'
    },
    watch: {
        '$route'() {
            if(this.$route.name != 'search'){
                this.searchModal = null
            }else{
                this.searchModal = this.$route.query.q ? this.$route.query.q : null
            }
        },
        searchModal() {
            this.searchContent = this.searchModal ? this.searchModal.replace('#','') : ''
        }
    },
    methods:{
        openSearchBox(){
			if (!this.isShownSearchBox) this.isShownSearchBox = true
		},
		closeSearchBox(){
			if (this.isShownSearchBox) this.isShownSearchBox = false
		},
		onEnterSearch(){
			if(this.searchContent){
				clearTimeout(typingTimer);
				typingTimer = setTimeout(() => 
					this.getSearchSuggestsList(this.searchContent)
				, 400);
			}else{
				this.closeSearchBox()
			}
		},
        searchText(){
            if(this.searchContent){
                this.$router.push({name: 'search', params: {search_type: 'text', type: 'post'}, query:{q: this.searchContent}})
                this.closeSearchBox()
            }
        },
		async getSearchSuggestsList(keyword){
            if(keyword){
                try {
                    this.searchResultsList['text'] = keyword
                    this.searchResultsList['hashtags'] = null
                    this.searchResultsList['users'] = null
                    this.searchResultsList['pages'] = null
                    const response = await getSearchSuggest(keyword)
                    this.openSearchBox()
                    if(response.hashtags.length || response.users.length || response.pages.length){
                        this.searchResultsList['hashtags'] = response.hashtags
                        this.searchResultsList['users'] = response.users
                        this.searchResultsList['pages'] = response.pages
                    }
                } catch (error) {
                    console.log(error)
                }
            }
		},
		matchingSearch(keyword){
			let words = this.searchContent
			keyword = window._.replace(window._.lowerCase(keyword), window._.lowerCase(words), '<span class="font-bold">'+words+'</span>')
			return keyword
		}
    }
}
</script>