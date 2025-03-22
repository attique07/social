<template>
    <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ $t('Pages') }}</h3>
            <BaseButton v-if="! user.is_page" @click="createPage">{{ $t('Create new page') }}</BaseButton>
        </div>
        <div class="main-content-menu user-pages-list flex whitespace-nowrap overflow-x-auto mb-4">
            <div class="main-content-menu-item flex items-center px-3 py-1 cursor-pointer border-b-2" :class="(currentTab === '' ? 'active border-primary-color text-primary-color font-semibold dark:border-dark-primary-color dark:text-dark-primary-color' : 'border-transparent')" @click="changeTab('')">
                {{$t('All')}}
            </div>
            <div class="main-content-menu-item flex items-center px-3 py-1 cursor-pointer border-b-2" :class="(currentTab === 'trending' ? 'active border-primary-color text-primary-color font-semibold dark:border-dark-primary-color dark:text-dark-primary-color' : 'border-transparent')" @click="changeTab('trending')">
                {{$t('Trending')}}
            </div>
            <div class="main-content-menu-item flex items-center px-3 py-1 cursor-pointer border-b-2" :class="(currentTab === 'suggest' ? 'active border-primary-color text-primary-color font-semibold dark:border-dark-primary-color dark:text-dark-primary-color' : 'border-transparent')" @click="changeTab('suggest')">
                {{$t('For you')}}
            </div>
            <div class="main-content-menu-item flex items-center px-3 py-1 cursor-pointer border-b-2" :class="(currentTab === 'following' ? 'active border-primary-color text-primary-color font-semibold dark:border-dark-primary-color dark:text-dark-primary-color' : 'border-transparent')" @click="changeTab('following')">
                {{$t('Following')}}
            </div>
        </div>
        <Component :is="pageComponent" :categoryId="$route.query.category_id"/>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { changeUrl } from '@/utility'
import BaseButton from '@/components/inputs/BaseButton.vue'
import UserPagesAll from '@/pages/user_pages/UserPagesAll.vue'
import UserPagesTrending from '@/pages/user_pages/UserPagesTrending.vue'
import UserPagesSuggest from '@/pages/user_pages/UserPagesSuggest.vue'
import UserPagesFollowing from '@/pages/user_pages/UserPagesFollowing.vue'

export default {
    props: ['data', 'params', 'position'],
    components: { BaseButton },
    data(){
        return{
            currentTab: this.params.tab ? this.params.tab : '',
        }
    },
    computed: {
		...mapState(useAuthStore, ['user']),
        pageComponent() {
			switch(this.currentTab){
				case 'trending':
					return UserPagesTrending;
				case 'suggest':
					return UserPagesSuggest
				case 'following':
					return UserPagesFollowing
				default: 
					return UserPagesAll;
			}
		}
    },
    methods:{
        changeTab(name) {
			this.currentTab = name
			let pageUrl = this.$router.resolve({
				name: 'user_pages'
			});
			changeUrl(pageUrl.fullPath + (name != '' ? '/' + name : ''))
		},
        createPage() {
            if (this.user) {
				let permission = 'user_page.allow_create'
                if(this.checkPermission(permission)){
                    this.$router.push({ 'name': 'user_pages_create' })
                }
			}
        }
    }
}
</script>