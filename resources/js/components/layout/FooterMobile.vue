<template>
    <div class="footer-mobile flex items-center justify-between lg:hidden fixed inset-x-0 bottom-0 bg-white px-5 py-4 z-[998] dark:bg-slate-800">
        <router-link :to="{ name: 'home' }" class="text-inherit">
            <BaseIcon name="home" class="align-middle"/>               
        </router-link> 
        <router-link :to="{ name: 'discovery' }" class="text-inherit">
            <BaseIcon name="discovery" class="align-middle"/>               
        </router-link>
        <button @click="openStatusBox()">
            <BaseIcon name="new_post_mobile" size="32" class="create-post-btn-mobile text-base-red"/>
        </button>
        <button @click="clickChat" class="relative text-inherit">
            <BaseIcon name="message" class="align-middle"/>
            <span v-if="pingChatCount > 0" class="footer-icons-badge absolute -top-1 -right-1 flex items-center justify-center w-[18px] h-[18px] bg-base-red rounded-full text-[10px] text-white">{{pingChatCount > 9 ? '9+' : pingChatCount}}</span>
        </button>  
        <router-link :to="{ name: 'notifications' }" class="relative text-inherit">
            <BaseIcon name="notification" class="align-middle"/>
            <span v-if="pingNotificationCount > 0" class="footer-icons-badge absolute -top-1 -right-1 flex items-center justify-center w-[18px] h-[18px] bg-base-red rounded-full text-[10px] text-white">{{pingNotificationCount > 9 ? '9+' : pingNotificationCount}}</span>
        </router-link>  
    </div>
</template>
<script>
import { mapState } from 'pinia';
import { checkPopupBodyClass } from '@/utility';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import PostStatusBox from '@/components/posts/PostStatusBox.vue';
import { useUtilitiesStore } from '../../store/utilities';

export default {
    name: "FooterMobile",
    components: { BaseIcon },
    computed:{
		...mapState(useUtilitiesStore, ['pingNotificationCount', 'pingChatCount']),
	},
    methods:{
        openStatusBox(){
			this.$dialog.open(PostStatusBox, {
                props:{
					class: 'post-status-modal p-dialog-lg',
                    modal: true,
					showHeader: false
                },
				onClose: () => {
                    checkPopupBodyClass();
                }
            });
		},
        clickChat() {
			let permission = 'chat.allow'
			if(this.checkPermission(permission)){
				this.$router.push({ name: 'chat', force: true})
			}
		}
    }
}
</script>