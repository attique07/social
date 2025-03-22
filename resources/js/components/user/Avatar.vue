<template>
     <UserInfoPopover :userProps="user" :activePopover="activePopover">
        <div class="rounded-full mx-auto max-w-full" :class="{'border border-divider dark:border-slate-700': border}" :style="{width: size}">
            <router-link v-if="router" :to="{name: 'profile', params: {user_name: user.user_name}}" :target="target">
                <img :src="user.avatar" class="rounded-full w-full"/>
            </router-link>
            <div v-else>
                <img :src="user.avatar" class="rounded-full w-full"/>
            </div>
        </div>
    </UserInfoPopover>
</template>
<script>
import UserInfoPopover from '@/components/user/UserInfoPopover.vue';
export default {
    components: { UserInfoPopover },
    props: {
        user: {
            type: Object,
            default: null
        },
        size: {
            type: String,
            default: '2.5rem'
        },
        target: {
            type: String,
            default: ''
        },
        activePopover: {
            type: Boolean,
            default: true
        },
        border: {
            type: Boolean,
            default: true
        },
        router: {
            type: Boolean,
            default: true
        }
    }
}
</script>