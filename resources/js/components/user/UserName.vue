<template>
    <div v-if="truncate" class="flex items-center gap-1 max-w-full">
        <UserInfoPopover :user-props="user" :active-popover="activePopover" class="inline-block min-w-0 max-w-full">
            <router-link v-if="router" :to="{name: 'profile', params: {user_name: user.user_name}}" :target="target" class="base-username flex font-semibold text-inherit">
                <span class="truncate">{{user.name}} </span>
            </router-link>
            <div v-else class="base-username flex font-semibold text-inherit">
                <span class="truncate">{{user.name}} </span>
            </div>
        </UserInfoPopover>
        <template v-if="config.userVerifyEnable && user.is_verify">
            <img v-if="!user.is_page" class="w-4 h-4" :src="config.userVerifyBadgeIcon" v-tooltip="{value: $t('Verified Account'), showDelay: 2500}"/>
            <img v-if="user.is_page" class="w-4 h-4" :src="config.userPageVerifyBadgeIcon" v-tooltip="{value: $t('Verified Page'), showDelay: 2500}"/>
        </template>
        <img v-if="user.is_page && user.is_page_feature" class="w-4 h-4" :src="config.user_page.featureBadgeIcon" v-tooltip="{value: $t('Featured Page'), showDelay: 2500}"/>
    </div>
    <div v-else class="inline break-word">
        <UserInfoPopover :user-props="user" :active-popover="activePopover" class="inline">
            <router-link v-if="router" :to="{name: 'profile', params: {user_name: user.user_name}}" :target="target" class="base-username inline font-semibold text-inherit">
                {{user.name}}
            </router-link>
            <div v-else class="base-username inline font-semibold text-inherit">
                {{user.name}}
            </div>
        </UserInfoPopover>
        <template v-if="config.userVerifyEnable && user.is_verify">
            <img v-if="!user.is_page" class="inline w-4 h-4 ms-1" :src="config.userVerifyBadgeIcon" v-tooltip="{value: $t('Verified Account'), showDelay: 2500}"/>
            <img v-if="user.is_page" class="inline w-4 h-4 ms-1" :src="config.userPageVerifyBadgeIcon" v-tooltip="{value: $t('Verified Page'), showDelay: 2500}"/>
        </template>
        <img v-if="user.is_page && user.is_page_feature" class="inline w-4 h-4 ms-1" :src="config.user_page.featureBadgeIcon" v-tooltip="{value: $t('Featured Page'), showDelay: 2500}"/>
    </div>
</template>

<script>
import { mapState } from 'pinia';
import UserInfoPopover from '@/components/user/UserInfoPopover.vue';
import { useAppStore } from '@/store/app';

export default {
    components: { UserInfoPopover },
    computed:{
		...mapState(useAppStore, ['config'])
	},
    props: {
        user: {
            type: Object,
            default: null
        },
        target: {
            type: String,
            default: ''
        },
        activePopover: {
            type: Boolean,
            default: true
        },
        router: {
            type: Boolean,
            default: true
        },
        truncate: {
            type: Boolean,
            default: true
        }
    }
}
</script>