<template>
	<form @submit.prevent="saveAccount">
		<div class="flex flex-wrap gap-x-5 mb-3">
			<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{ $t('Email') }}</label></div>
			<div class="md:flex-2 w-full">
				<BaseInputText v-model="email" :error="error.email" />
			</div>
		</div>
		<div class="flex flex-wrap gap-x-5 mb-3">
			<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{ $t('Username') }}</label></div>
			<div class="md:flex-2 w-full">
				<BaseInputText v-model="user_name" :error="error.user_name" />
			</div>
		</div>
		<div class="text-center mt-8">
			<BaseButton>{{ $t('Save') }}</BaseButton>
		</div>
	</form>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'

export default {
	components: { BaseButton, BaseInputText },
	data() {
		return {
			email: null,
			user_name: null,
			error: {
				email: null,
				user_name: null
			}
		}
	},
	mounted() {
		this.email = this.user.email
		this.user_name = this.user.user_name
	},
	computed:{
        ...mapState(useAuthStore, ['user'])
    },
	methods: {
		...mapActions(useAuthStore, ['storeAccountSettings']),
		async saveAccount() {
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
								await this.storeAccountSettings({
									email: this.email,
									password: data.password,
									user_name: this.user_name
								})
								this.showSuccess(this.$t('Your changes have been saved.'))
								this.resetErrors(this.error)
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