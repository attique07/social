<template>
    <div class="profile-menu-list flex flex-wrap mb-base-2 gap-base-2">
        <router-link class="profile-menu-list-item flex flex-1 min-w-[200px] items-center justify-center rounded-none md:rounded-base-lg gap-2 p-3 text-base-sm bg-white text-main-color dark:text-white dark:bg-slate-800" :to="{ name: 'user_pages_overview'}">
            <BaseIcon name="info" />
			{{$t('Page Overview')}}
        </router-link>
        <router-link class="profile-menu-list-item flex flex-1 min-w-[200px] items-center justify-center rounded-none md:rounded-base-lg gap-2 p-3 text-base-sm bg-white text-main-color dark:text-white dark:bg-slate-800" :to="{ name: 'user_pages_manage_users'}">
            <BaseIcon name="users" />
			{{$t('Manage Users')}}
        </router-link>
        <router-link class="profile-menu-list-item flex flex-1 min-w-[200px] items-center justify-center rounded-none md:rounded-base-lg gap-2 p-3 text-base-sm bg-white text-main-color dark:text-white dark:bg-slate-800" :to="{ name: 'user_pages_notify_settings'}">
            <BaseIcon name="notification" />
			{{$t('Notify Settings')}}
        </router-link>
    </div>
    <router-view></router-view>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { BaseIcon }
}
</script>