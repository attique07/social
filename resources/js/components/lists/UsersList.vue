<template>
    <div v-if="usersListData.length > 0" :class="`users_list_${positionWidget}`">
        <div v-if="listStyle === 'list'" class="users-list">
            <div v-for="userItem in usersListData" :key="userItem.id" class="users-list-item flex items-center gap-base-2 mb-base-2">
                <Avatar :user="userItem" :target="target" :activePopover="showPopover"/>
                <div class="flex-1 min-w-0">
                    <UserName :user="userItem" :activePopover="showPopover" :target="target" class="list_items_title_text_color" />
                    <p v-if="showUserName" class="list_items_sub_text_color text-xs text-sub-color mb-1 dark:text-slate-400 truncate">{{mentionChar + userItem.user_name}}</p>
                    <p v-if="showFollower && userItem.show_follower" class="list_items_sub_text_color flex items-center text-xs text-sub-color dark:text-slate-400"><BaseIcon name="people" size="16" class="me-1"/>{{ $filters.numberShortener(userItem.follower_count, $t('[number] follower'), $t('[number] followers')) }}</p> 
                </div>
                <template v-if="listType === 'block'">
                    <button class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color" @click="unBlock(userItem.id)">{{$t('Unblock')}}</button>
                </template>
                <template v-else>
                    <div v-if="(user.id !== userItem.id) && userItem.can_follow">
                        <button v-if="userItem.is_followed" class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color" @click="unFollowUser(userItem)">{{$t('Unfollow')}}</button>
                        <button v-else class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color" @click="followUser(userItem)">{{$t('Follow')}}</button>
                    </div>
                </template>
            </div>
        </div>
        <SlimScroll v-else :gap="2">
            <div v-for="userItem in usersListData" :key="userItem.id" class="users-list-item border border-divider rounded-base-lg text-center relative p-base-2 dark:border-white/10 w-32 md:w-44">
                <Avatar :user="userItem" :target="target" :size="'8.5rem'" :activePopover="showPopover"/>
                <div class="flex flex-col items-center my-base-2">
                    <UserName :user="userItem" :activePopover="showPopover" :target="target" class="list_items_title_text_color" />
                    <p v-if="showUserName" class="list_items_sub_text_color text-xs text-sub-color mb-1 dark:text-slate-400 truncate w-full">{{mentionChar + userItem.user_name}}</p>
                    <p v-if="showFollower && userItem.data.show_follower" class="list_items_sub_text_color flex items-center text-xs text-sub-color dark:text-slate-400"><BaseIcon name="people" size="16" class="me-1"/>{{ $filters.numberShortener(userItem.follower_count, $t('[number] follower'), $t('[number] followers')) }}</p> 
                </div>
                <div v-if="(user.id !== userItem.id) && userItem.can_follow">
                    <button v-if="userItem.is_followed" class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color" @click="unFollowUser(userItem)">{{$t('Unfollow')}}</button>
                    <button v-else class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color" @click="followUser(userItem)">{{$t('Follow')}}</button>
                </div>
            </div>
        </SlimScroll>
    </div>
    <div v-else class="text-center">{{ emptyMessage ? emptyMessage : $t('No Users') }}</div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { toggleFollowUser } from '@/api/follow'
import { toggleBlockUser } from '@/api/user'
import Constant from '@/utility/constant'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import { useAuthStore } from '../../store/auth'
import { useActionStore } from '../../store/action'
import SlimScroll from '@/components/utilities/SlimScroll.vue'

export default {
    components: { BaseIcon, UserName, Avatar, SlimScroll },
    data(){
        return{
            mentionChar: Constant.MENTION,
            usersListData:  window._.clone(this.usersList)
        }
    },
    props: {
        usersList: {
            type: Array,
            default: null
        },
        showUserName: {
            type: Boolean,
            default: true
        },
        showFollower: {
            type: Boolean,
            default: false
        },
        target: {
            type: String,
            default: ''
        },
        showPopover: {
            type: Boolean,
            default: true
        },
        listType: {
            type: String,
            default: ''
        },
        listStyle:{
            type: String,
            default: 'list'
        },
        positionWidget: {
            type: String,
            default: ''
        },
        emptyMessage: {
            type: String,
            default: ''
        }
    },
    computed: {
        ...mapState(useAuthStore, ["user", "authenticated"]),
        ...mapState(useActionStore, ['userAction'])
    },
    watch: {
        userAction(){
            this.usersListData.map(user => {
                if(user.id === this.userAction.userId){
                    if(this.userAction.status === 'follow'){
                        user.is_followed = true
                    }else if(this.userAction.status === 'unfollow'){
                        user.is_followed = false
                    }
                }
                return user
            })
        },
        usersList(){
            this.usersListData = window._.clone(this.usersList)
        }
    },
    methods: {
        ...mapActions(useActionStore, ['updateFollowStatus']),
        async followUser(user) {
            if(this.authenticated){
                try {
                    await toggleFollowUser({
                        id: user.id,
                        action: "follow",
                        name: user.name,
                        avatar: user.avatar
                    });
                    this.usersListData.map(userItem => {
                        if (userItem.id === user.id) {
                            userItem.is_followed = true;
                        }
                        return userItem;
                    });
                    this.$emit('follow_user', user)
                    this.updateFollowStatus({userId: user.id, status: 'follow'})
                }
                catch (error) {
                    this.showError(error.error)
                }
            }else{
                this.showRequireLogin()
            }
        },
        async unFollowUser(user) {
            try {
                await toggleFollowUser({
                    id: user.id,
                    action: "unfollow",
                    name: user.name,
                    avatar: user.avatar
                });
                this.usersListData.map(userItem => {
                    if (userItem.id === user.id) {
                        userItem.is_followed = false;
                    }
                    return userItem;
                });
                this.$emit('unfollow_user', user)
                this.updateFollowStatus({userId: user.id, status: 'unfollow'})
            }
            catch (error) {
                this.showError(error.error)
            }
        },
        async unBlock(userId){
            try {
                await toggleBlockUser({
                    id: userId,
                    action: "remove"
                });
                this.usersListData = this.usersListData.filter(user => user.id !== userId);
            } catch (error) {
                this.showError(error.error)
            }         
        }
    },
    emits: ['follow_user', 'unfollow_user']
}
</script>