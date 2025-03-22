<template>
	<form @submit.prevent="saveAccount">
		<div class="flex flex-wrap gap-x-5 mb-3">
			<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{ $t('Email') }}</label></div>
			<div class="md:flex-2 w-full">
				<BaseInputText v-model="email" class="w-full" :error="error.email" />
			</div>
		</div>
		<div class="flex flex-wrap gap-x-5 mb-3">
			<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{ $t('Password') }}</label></div>
			<div class="md:flex-2 w-full">
				<BasePassword v-model="password" :error="error.password" />
			</div>
		</div>
		<div class="flex flex-wrap gap-x-5 mb-3">
			<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{ $t('Confirm Password') }}</label></div>
			<div class="md:flex-2 w-full">
				<BasePassword v-model="password_confirmed" :error="error.password_confirmed" />
			</div>
		</div>
		<div class="text-center mt-8">
			<BaseButton :loading=loading>{{ $t('Save') }}</BaseButton>
		</div>
	</form>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '../../store/auth'
import { addVerifyEmailPassword } from '@/api/user'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BasePassword from '@/components/inputs/BasePassword.vue'
import VerificationCodeModal from '@/components/modals/VerificationCodeModal.vue'
const authStore = useAuthStore()

export default {
	components: { BaseButton, BaseInputText, BasePassword },
	data() {
		return {
			loading: false,
			email: null,
			password: null,
			password_confirmed: null,
			error: {
				email: null,
				password: null,
				password_confirmed: null
			}
		}
	},
	computed: {
		...mapState(useAuthStore, ['user'])
	},
	methods: {
		async saveAccount() {
			this.loading = true
			try {
				await addVerifyEmailPassword(this.email, this.password, this.password_confirmed)
				this.$dialog.open(VerificationCodeModal, {
					data: {
						email: this.email,
						password: this.password,
						password_confirmed: this.password_confirmed
					},
					props: {
						header: this.$t('Enter verification code'),
						class: 'verification-modal',
						modal: true,
						dismissableMask: true,
						draggable: false
					},
					onClose: async() => {
						await authStore.me()
						this.$router.push({ name: 'setting_account'})
					}
				})
				this.loading = false
			} catch (error) {
				this.loading = false
				this.handleApiErrors(this.error, error)
			}
		}
	}
}
</script>