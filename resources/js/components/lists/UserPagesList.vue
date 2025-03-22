<template>
    <div v-if="pagesListData.length" class="flex flex-wrap -mx-base-1">
        <div v-for="page in pagesListData" :key="page.id" class="p-base-1 md:flex-[0_0_50%] md:max-w-1/2 w-full">
            <div class="rounded-lg border border-divider dark:border-white/10 h-full">
                <router-link :to="{name: 'profile', params: {user_name: page.user_name}}" class="block pb-[35%] bg-cover bg-center bg-no-repeat rounded-t-lg" :style="{ backgroundImage: `url(${page.cover})`}"></router-link>
                <div class="p-base-2">
                    <div class="flex gap-4">
                        <Avatar :user="page" :size="'3.25rem'"/>
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap gap-2 items-center mb-1"> 
                                <UserName :user="page" />
                            </div>
                            <div v-if="page.categories.length" class="text-xs mb-1 truncate text-sub-color dark:text-slate-400">
                                <span v-for="(category, index) in page.categories" :key="category.id">
                                    <router-link :to="{name: 'user_pages', query: {category_id: category.id}}" class="text-inherit">{{ category.name }}</router-link>
                                    {{ (index === page.categories.length - 1) ? '' : ' . '}}
                                </span>
                            </div>
                            <div v-if="page.show_follower" class="text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(page.follower_count, $t('[number] Follower'), $t('[number] Followers')) }}</div>
                        </div>
                    </div>
                    <div v-if="page.can_follow" class="mt-base-2">
                        <BaseButton v-if="page.is_followed" @click="unFollowPage(page)" type="outlined" class="w-full">{{$t('Unfollow')}}</BaseButton>
                        <BaseButton v-else @click="followPage(page)" type="outlined" class="w-full">{{$t('Follow')}}</BaseButton>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div v-else class="text-center">{{$t('No Pages')}}</div>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import { toggleFollowUser } from '@/api/follow'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import { useAuthStore } from '@/store/auth'
import { useActionStore } from '@/store/action'

export default {
    components: { Avatar, UserName, BaseButton },
    props: ['pagesList'],
    data(){
        return{
            pagesListData:  window._.clone(this.pagesList)
        }
    },
    computed: {
        ...mapState(useAuthStore, ["authenticated"]),
        ...mapState(useActionStore, ['userAction'])
    },
    watch: {
        userAction(){
            this.pagesListData.map(page => {
                if(page.id === this.userAction.pageId){
                    if(this.userAction.status === 'follow'){
                        page.is_followed = true
                    }else if(this.userAction.status === 'unfollow'){
                        page.is_followed = false
                    }
                }
                return page
            })
        },
        pagesList(){
            this.pagesListData = window._.clone(this.pagesList)
        }
    },
    methods: {
        ...mapActions(useActionStore, ['updateFollowStatus']),
        async followPage(page) {
            if(this.authenticated){
                try {
                    await toggleFollowUser({
                        id: page.id,
                        action: "follow"
                    });
                    this.pagesListData.map(pageItem => {
                        if (pageItem.id === page.id) {
                            pageItem.is_followed = true;
                        }
                        return pageItem;
                    });
                    this.updateFollowStatus({pageId: page.id, status: 'follow'})
                    this.$emit('follow_page', page)
                }
                catch (error) {
                    this.showError(error.error)
                }
            }else{
                this.showRequireLogin()
            }
        },
        async unFollowPage(page) {
            try {
                await toggleFollowUser({
                    id: page.id,
                    action: "unfollow"
                });
                this.pagesListData.map(pageItem => {
                    if (pageItem.id === page.id) {
                        pageItem.is_followed = false;
                    }
                    return pageItem;
                });
                this.updateFollowStatus({pageId: page.id, status: 'unfollow'})
                this.$emit('unfollow_page', page)
            }
            catch (error) {
                this.showError(error.error)
            }
        }
    },
    emits: ['follow_page', 'unfollow_page']
} 
</script>