<template>
	<div v-if="isMobile" class="flex main-content-section bg-white rounded-none md:rounded-lg dark:bg-slate-800">
		<div v-if="!currentTab" class="flex-1 py-base-2 lg:border-r border-divider dark:border-white/10">
			<div class="settings-list flex flex-col">
				<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_profile'}">
					{{ user.is_page ? $t('General Settings') : $t('Profile')}}
					<BaseIcon name="caret_right" size="16"/>
				</router-link>
				<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_subscriptions'}">
					{{ $t('Subscriptions') }}
					<BaseIcon name="caret_right" size="16"/>
				</router-link>
				<template v-if="!user.is_page">
					<template v-if="user.has_email">
						<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_account'}">
							{{$t('Account')}}
							<BaseIcon name="caret_right" size="16"/>
						</router-link>
						<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_password'}">
							{{$t('Change password')}}
							<BaseIcon name="caret_right" size="16"/>
						</router-link>
					</template>
					<router-link v-else class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_add_email_password'}">
						{{$t('Add email and password')}}
						<BaseIcon name="caret_right" size="16"/>
					</router-link>
				</template>
				<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_privacy'}">
					{{$t('Privacy')}}
					<BaseIcon name="caret_right" size="16"/>
				</router-link>
				<router-link v-if="!user.is_page" class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_email'}">
					{{$t('Email Notifications')}}
					<BaseIcon name="caret_right" size="16"/>
				</router-link>
				<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_notifications'}">
					{{$t('Push Notifications')}}
					<BaseIcon name="caret_right" size="16"/>
				</router-link>
				<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_display'}">
					{{$t('Display')}}
					<BaseIcon name="caret_right" size="16"/>
				</router-link>
				<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_download'}">
					{{$t('Download your Data')}}
					<BaseIcon name="caret_right" size="16"/>
				</router-link>
				<div v-if="user.can_delete" class="px-base-2 mt-6">
					<BaseButton type="danger-outlined" class="w-full" @click="deleteUser">{{$t('Delete Account')}}</BaseButton>
				</div>
			</div>
		</div>
		<div v-if="currentTab" class="flex-2 min-w-0 p-5 lg:p-10">
			<div class="flex items-center gap-3 mb-5">
				<router-link :to="{ name: 'setting_index'}" class="text-inherit">
					<BaseIcon name="arrow_left" class="align-middle" />
				</router-link>
				<h2 class="text-2xl capitalize font-bold font-workSans">{{getTitle()}}</h2>
			</div>
			<router-view></router-view>
		</div>
	</div>
	<div v-else class="flex main-content-section bg-white border border-white text-main-color dark:bg-slate-800 dark:border-slate-800 dark:text-white rounded-base-lg mb-base-2">
		<div class="flex-1 py-base-7 md:border-e border-divider dark:border-white/10 rounded-s-base-lg">
			<div class="settings-list flex flex-col">
				<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_index'}">
					{{ user.is_page ? $t('General Settings') : $t('Profile')}}
				</router-link>
				<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_subscriptions'}">
					{{ $t('Subscriptions') }}
				</router-link>
				<template v-if="!user.is_page">
					<template v-if="user.has_email">
						<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_account'}">
							{{$t('Account')}}
						</router-link>
						<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_password'}">
							{{$t('Change password')}}
						</router-link>
					</template>
					<router-link v-else class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_add_email_password'}">
						{{$t('Add email and password')}}
					</router-link>
				</template>
				<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_privacy'}">
					{{$t('Privacy')}}
				</router-link>
				<router-link v-if="!user.is_page" class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_email'}">
					{{$t('Email Notifications')}}
				</router-link>
				<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_notifications'}">
					{{$t('Push Notifications')}}
				</router-link>
				<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_display'}">
					{{$t('Display')}}
				</router-link>
				<router-link class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white" :to="{ name: 'setting_download'}">
					{{$t('Download your Data')}}
				</router-link>
				<div v-if="user.can_delete" class="px-base-2 mt-6">
					<BaseButton type="danger-outlined" class="btn-block" @click="deleteUser">{{ user.is_page ? $t('Delete Page') : $t('Delete Account')}}</BaseButton>
				</div>
			</div>
		</div>
		<div class="flex-2 min-w-0 p-5 lg:p-10">
			<router-view></router-view>
		</div>
	</div>
</template>
<script>
import { mapState } from 'pinia';
import { deleteUserAccount } from '@/api/user'
import { deletePageAccount } from '@/api/page'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import { useAppStore } from '@/store/app';
import { useAuthStore } from '@/store/auth';


export default {
    components: { BaseIcon, BaseButton },
	props: ["tab"],
	data(){
		return{
			currentTab: this.$route.path.split("/")[2],
			error: {
				password: null
			}
		}
	},
	computed: {
		...mapState(useAppStore, ['isMobile']),
		...mapState(useAuthStore, ['user'])
	},
	methods:{
		getTitle() {
			switch (this.currentTab) {
                case "profile":
                    return this.user.is_page ? this.$t('General Settings') : this.$t('Profile');
                case "account":
                    return this.$t('Account');
				case "password":
                    return this.$t('Change password');
                case "privacy":
                    return this.$t('Privacy');
                case "email":
                    return this.$t('Email');
				case "notifications":
					return this.$t('Notifications');
				case "display":
					return this.$t('Display');
				case "download":
					return this.$t('Download');
				case "add_email_password":
					return this.$t('Add email and password');
				case "subscriptions":
					return this.$t('Subscriptions');
                default:
                    return this.$t('Profile');
            }
		},
		async deleteUser(){
			const passwordDialog = this.$dialog.open(PasswordModal, {
				props: {
					header: this.$t('Enter Password'),
					class: 'password-modal',
					modal: true,
					dismissableMask: true,
					draggable: false
				},
				emits: {
                    onConfirm: async (data) => {
                        if (data.password) {
                            try {
								if (this.user.is_page) {
									await deletePageAccount({
										password: data.password
									})
								} else {
									await deleteUserAccount({
										password: data.password
									})
									useAuthStore().remove()
								}							
								window.location.href = window.siteConfig.siteUrl;
								passwordDialog.close()
							} catch (error) {
								this.handleApiErrors(this.error, error)
								passwordDialog.close()
							}
                        }
                    }
                }
			})
		}
	}
}
</script>