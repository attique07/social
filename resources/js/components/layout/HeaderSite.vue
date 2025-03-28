<template>
	<header class="main-header hidden lg:block bg-body sticky top-0 z-[998] dark:bg-dark-body">
		<Container class="p-base-2 lg:pt-6 lg:px-6 lg:pb-4">
			<div class="flex flex-wrap justify-between items-center">
				<GlobalSearch/>
				<div class="header-icons-list flex justify-center items-center space-s-4">
					<DropdownMenu v-if="enableDarkmode" appendTo="self">     
						<template v-slot:dropdown-button>
							<BaseIcon v-if="darkmode === 'on'" name="dark" class="align-middle"></BaseIcon>
							<BaseIcon v-else-if="darkmode === 'off'" name="light" class="align-middle"></BaseIcon> 
							<template v-else>
								<BaseIcon v-if="systemMode === 'light'" name="light" class="align-middle"></BaseIcon>
								<BaseIcon v-else-if="systemMode === 'dark'" name="dark" class="align-middle"></BaseIcon> 
							</template> 
						</template>                      
						<div class="px-5 py-3 w-36">
							<div class="flex flex-col gap-4">
								<div class="flex items-center gap-3 cursor-pointer" :class="darkmode === 'off' ? 'text-primary-color dark:text-dark-primary-color' : ''" @click="toggleDarkmode('off')">
									<BaseIcon name="light"></BaseIcon>  
									{{ $t('Light') }}
								</div>
								<div class="flex items-center gap-3 cursor-pointer" :class="darkmode === 'on' ? 'text-primary-color dark:text-dark-primary-color' : ''" @click="toggleDarkmode('on')">
									<BaseIcon name="dark"></BaseIcon>  
									{{ $t('Dark') }}
								</div>
								<div class="flex items-center gap-3 cursor-pointer" :class="darkmode === 'auto' ? 'text-primary-color dark:text-dark-primary-color' : ''" @click="toggleDarkmode('auto')">
									<BaseIcon name="desktop"></BaseIcon>  
									{{ $t('System') }}
								</div>
							</div>
						</div>
					</DropdownMenu>
					<router-link :to="{ name: 'discovery' }" class="inline-block text-main-color dark:text-white">
						<BaseIcon name="discovery" class="align-middle"></BaseIcon>
					</router-link>
					<DropdownMenu appendTo="self">     
						<template v-slot:dropdown-button>
							<BaseIcon name="notification"></BaseIcon>
							<span v-if="pingNotificationCount > 0" class="header-icons-badge absolute -top-1 -right-1 flex items-center justify-center w-[18px] h-[18px] bg-base-red rounded-full text-[10px] text-white cursor-pointer">{{pingNotificationCount > 9 ? '9+' : pingNotificationCount}}</span>
						</template>                      
						<div class="dropdown-menu-box-notification p-5 w-96">
							<NotificationsList :viewAllBtn="true"/>
						</div>
					</DropdownMenu>
					<button @click="clickChat" class="inline-block text-main-color dark:text-white relative">
						<BaseIcon name="message" class="align-middle"></BaseIcon>
						<span v-if="pingChatCount > 0" class="header-icons-badge absolute -top-1 -right-1 flex items-center justify-center w-[18px] h-[18px] bg-base-red rounded-full text-[10px] text-white cursor-pointer">{{pingChatCount > 9 ? '9+' : pingChatCount}}</span>
					</button>
				</div>  
			</div>
		</Container>
	</header>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { storeDarkmode } from '@/api/user'
import { useUtilitiesStore } from '@/store/utilities';
import { useAppStore } from '@/store/app';
import BaseIcon from '@/components/icons/BaseIcon.vue'
import DropdownMenu from '@/components/utilities/DropdownMenu.vue'
import NotificationsList from '@/components/notifications/NotificationsList.vue'
import GlobalSearch from '@/components/layout/GlobalSearch.vue';
import Container from '@/components/article/Container.vue';
import Constant from '@/utility/constant'

export default {
	components: {
		BaseIcon,
		DropdownMenu,
		NotificationsList,
		GlobalSearch,
		Container
	},
	data(){
		return {
			enableDarkmode: Constant.ENABLE_DARKMODE
		}
	},
	mounted() {
		this.updateSystemMode();
		window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', this.updateSystemMode);
    },
	beforeUnmount() {
		window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', this.updateSystemMode);
	},
	watch: {
		darkmode() {
			if (this.darkmode === 'on') {
				document.documentElement.classList.add('dark')
			}else if (this.darkmode === 'off') {
				document.documentElement.classList.remove('dark')
			}else{
				if (this.systemMode === 'dark') {
					document.documentElement.classList.add('dark')
				} else if (this.systemMode === 'light') {
					document.documentElement.classList.remove('dark')
				}
			}
		},
		systemMode() {
			if (this.darkmode === 'auto'){
				if (this.systemMode === 'dark') {
					document.documentElement.classList.add('dark')
				} else if (this.systemMode === 'light') {
					document.documentElement.classList.remove('dark')
				}
			}
		}
	},
	computed:{
		...mapState(useUtilitiesStore, ['pingNotificationCount', 'pingChatCount']),
		...mapState(useAppStore, ['config', 'darkmode', 'systemMode'])
	},
	methods: {
		...mapActions(useAppStore, ['setDarkmode', 'updateSystemMode']),
		async toggleDarkmode(status){
            try {
                await storeDarkmode(status)
                this.setDarkmode(status);
            } catch (error) {
                this.showError(error.error)
            }
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