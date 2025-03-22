<template>
    <div class="main-content-section bg-white border border-white text-main-color p-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800 dark:border-slate-800 dark:text-white mb-base-2">
        <div class="main-content-menu lists-list flex whitespace-nowrap overflow-x-auto mb-4">
            <router-link class="main-content-menu-item flex items-center px-3 py-1 cursor-pointer border-b-2" :to="{ name: 'list_follower'}">
                {{$t('Followers')}}
            </router-link>
            <router-link class="main-content-menu-item flex items-center px-3 py-1 cursor-pointer border-b-2" :to="{ name: 'list_following'}">
                {{$t('Following')}}
            </router-link>
            <router-link class="main-content-menu-item flex items-center px-3 py-1 cursor-pointer border-b-2" :to="{ name: 'list_block_member'}">
                {{$t('Blocking')}}
            </router-link>
            <router-link class="main-content-menu-item flex items-center px-3 py-1 cursor-pointer border-b-2" :to="{ name: 'list_tag'}">
                {{$t('Tags')}}
            </router-link>
            <router-link class="main-content-menu-item flex items-center px-3 py-1 cursor-pointer border-b-2" :to="{ name: 'list_stories'}">
                {{$t('Stories')}}
            </router-link>
            <router-link v-if="!user.is_page" class="main-content-menu-item flex items-center px-3 py-1 cursor-pointer border-b-2" :to="{ name: 'list_page'}">
                {{$t('Pages')}}
            </router-link>
        </div>
        <router-view></router-view>
    </div>
</template>
<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'

export default {
    computed: {
        ...mapState(useAuthStore, ['user'])
    }
}
</script>