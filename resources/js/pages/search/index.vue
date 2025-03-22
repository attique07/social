<template>
    <Error v-if="error">{{error}}</Error>
    <h1 class="page-title mb-base-2">
        <div v-if="search_type === 'hashtag'">
            <Loading v-if="loading_query"/>
            <div v-else>
                {{hashtagChar + query}}
                <BaseButton v-if="queryInfo && authenticated" @click="toggleFollowHashtag()">
                    {{ queryInfo.is_followed ? $t('Unfollow') : $t('Follow') }}
                </BaseButton>
            </div>
        </div>
        <div v-else>{{$t('Search') + ': ' + query}}</div>
    </h1>
    <div>
        <div class="profile-menu-list flex flex-wrap mb-base-2 gap-base-2">
            <div class="profile-menu-list-item flex flex-1 min-w-[200px] items-center justify-center rounded-none md:rounded-base-lg gap-2 p-3 text-base-sm cursor-pointer" :class="(currentTab === 'post'?'active bg-primary-color text-white dark:bg-dark-primary-color':'bg-white dark:bg-slate-800')" @click="changeTab('post')">
                <BaseIcon name="lists" />
                {{$t('Post')}}
            </div>
            <div class="profile-menu-list-item flex flex-1 min-w-[200px] items-center justify-center rounded-none md:rounded-base-lg gap-2 p-3 text-base-sm cursor-pointer" :class="(currentTab === 'user'?'active bg-primary-color text-white dark:bg-dark-primary-color':'bg-white dark:bg-slate-800')" @click="changeTab('user')">
                <BaseIcon name="profile" />
                {{$t('People')}}
            </div>
            <div class="profile-menu-list-item flex flex-1 min-w-[200px] items-center justify-center rounded-none md:rounded-base-lg gap-2 p-3 text-base-sm cursor-pointer" :class="(currentTab === 'page'?'active bg-primary-color text-white dark:bg-dark-primary-color':'bg-white dark:bg-slate-800')" @click="changeTab('page')">
                <BaseIcon name="briefcase" />
                {{$t('Page')}}
            </div>
        </div>
        <Component :is="searchTypeComponent" :search_type="search_type" :type="currentTab" :query="query"></Component>
    </div>
</template>
<script>
import { mapState } from 'pinia';
import { changeUrl } from '@/utility';
import { toggleFollowHashtag } from '@/api/follow';
import { getHashtag } from '@/api/hashtag'
import PostsSearch from '@/pages/search/PostsSearch.vue'
import UsersSearch from '@/pages/search/UsersSearch.vue'
import PageSearch from '@/pages/search/PageSearch.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import Constant from '@/utility/constant';
import BaseButton from '@/components/inputs/BaseButton.vue';
import Error from '@/components/utilities/Error.vue';
import Loading from '@/components/utilities/Loading.vue';
import { useAuthStore } from '../../store/auth';

export default {
    components: { BaseIcon, BaseButton, Error, Loading },
    data(){
        return{
            currentTab: this.type,
            hashtagChar: Constant.HASHTAG,
            loading_query: this.search_type === 'hashtag' ? true : false,
            query: null,
            queryInfo: null,
            error: null
        }
    },
    created(){
        this.query = !window._.isNil(this.$route.query.q) ? this.$route.query.q : ''
        if(this.search_type === 'hashtag'){
            this.getHashtagInfo(this.query)
        }
    },
    watch: {
        '$route'() {
            this.query = !window._.isNil(this.$route.query.q) ? this.$route.query.q : ''
            if(this.search_type === 'hashtag' && this.query){
                this.getHashtagInfo(this.query)
            }
        }
    },
    props: ["search_type", "type"],
    computed: {
        ...mapState(useAuthStore, ['authenticated']),
        searchTypeComponent() {
            if (this.query != '') {
                switch(this.currentTab){
                    case 'user':
                        return UsersSearch;
                    case 'page':
                        return PageSearch;
                    default: 
                        return PostsSearch;
                }
            }
            return null;
		}
    },
    methods: {
        changeTab(name) {
			this.currentTab = name
			let searchUrl = this.$router.resolve({
				name: 'search',
                params: {'search_type': this.search_type, 'type': this.currentTab},
                query: {'q': this.query}
			});
			changeUrl(searchUrl.fullPath)
		},
        async getHashtagInfo(hashtagName){
            try {
                const response = await getHashtag(hashtagName)
                this.queryInfo = response
                this.loading_query = false
            } catch (error) {
                this.loading_query = false
                //this.error = error
            }
        },
        async toggleFollowHashtag(){
            try {
                await toggleFollowHashtag({name: this.queryInfo.name, action: this.queryInfo.is_followed ? 'unfollow' : 'follow'});
                this.queryInfo.is_followed = !this.queryInfo.is_followed;
            }
            catch (error) {
                this.showError(error.error)
            }
        },
    }
}
</script>