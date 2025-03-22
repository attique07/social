<template>
	<div v-if="usersList.length > 0" class="boxes-list flex flex-wrap gap-x-base-1 gap-y-base-2">
		<div v-for="userItem in usersList" :key="userItem.id" class="boxes-list-item inline-flex items-center p-1 border border-secondary-box-color rounded-base dark:text-slate-400 dark:border-white/30">
            <Avatar :user="userItem" :activePopover="false" :size="'1.5rem'" :target="target"/>
            <router-link :to="{name: 'profile', params: {user_name: userItem.user_name}}" class="text-xs text-inherit font-bold ms-base-1 me-2" :target="target">{{userItem.name}}</router-link>
            <BaseIcon name="close" size="16" @click="unFollowUser(userItem)"></BaseIcon>
        </div>
	</div>
</template>

<script>
import { toggleFollowUser } from '../../api/follow';
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Avatar from '@/components/user/Avatar.vue'

export default {
    components: { BaseIcon, Avatar },
    props: ['usersList', 'target'],
    methods: {
        async unFollowUser(user) {
            try {
                await toggleFollowUser({
                    id: user.id,
                    action: "unfollow",
                    name: user.name,
                    avatar: user.avatar
                });
                this.$emit('unfollow_user', user)
            }
            catch (error) {
                this.showError(error.error)
            }
        }
    },
    emits: ['unfollow_user']
}
</script>