<template>
	<Logo />
	<div class="bg-white rounded-base w-full sm:w-96 p-7 mx-auto mb-base-2 dark:bg-slate-800 dark:text-white">	
		<div v-if="currentStep === 1">
			<div class="flex items-center justify-between mb-4">
				<h3 class="text-base-lg font-extrabold">{{$t('Forgot password')}}</h3>
			</div>
            <p class="mb-4">{{$t('Please enter your email address to search for your account.')}}</p>
			<BaseInputText v-model="email" :placeholder="$t('Email')" :error="error.email" :left_icon="'invite'" autocomplete="username" @keyup.enter="nextStep" class="mb-base-2" />
            <BaseButton :loading="loading_send" class="w-full" @click="nextStep()">{{$t('Continue')}}</BaseButton>
            <div class="mt-5 text-center">{{$t('Already had an account?')}}&nbsp;<router-link :to="{name: 'login'}">{{$t('Login')}}</router-link></div>
		</div>
		<div v-if="currentStep === 2">
			<div class="flex items-center justify-between mb-4">
				<h3 class="text-base-lg font-extrabold">{{$t('Enter security code')}}</h3>
			</div>
            <p class="mb-4">{{$t('Please check your email for a message with your code.')}}</p>
			<div class="relative mb-1">
				<BaseInputText v-model="code" :placeholder="$t('Code')" :error="error.code" @keyup.enter="nextStep" class="mb-base-2" />
				<small v-if="error.code" class="p-error">{{error.code}}</small>
			</div>
            <p>{{$t('We sent your code to')}}:&nbsp;{{ email }}</p>	
            <BaseButton :loading="loading" class="w-full" @click="nextStep()">{{$t('Continue')}}</BaseButton>
			<BaseButton type="transparent" :loading="loading_send" class="w-full" @click="sendCodeForEmail()">{{$t('Resend code')}}</BaseButton>
		</div>
        <div v-if="currentStep === 3">
			<div class="flex items-center justify-between mb-4">
				<h3 class="text-base-lg font-extrabold">{{$t('Change Password')}}</h3>
			</div>
			<div class="relative mb-base-2">
                <BasePassword v-model="password" :placeholder="$t('Password')" @keyup.enter="nextStep" :error="error.password"/>
			</div>
            <div class="relative mb-base-2">
                <BasePassword v-model="password_confirmed" :placeholder="$t('Confirm Password')" @keyup.enter="nextStep" :error="error.password_confirmed"/>
			</div>
            <BaseButton :loading="loading" class="w-full" @click="nextStep()">{{$t('Save')}}</BaseButton>
		</div>
		<div v-if="currentStep === 4">
			<div class="flex items-center justify-between mb-4">
				<h3 class="text-base-lg font-extrabold">{{$t('Password Updated')}}</h3>
			</div>
			<p class="mb-4">{{$t('Your password was changed')}}</p>
			<BaseButton :to="{ name: 'login' }" class="w-full">{{$t('Login')}}</BaseButton>
		</div>
	</div>
</template>
<script>
import { sendCodeForgotPassword, checkCodeForgotPassword, storeForgotPassword } from '@/api/user'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BasePassword from '@/components/inputs/BasePassword.vue'
import Logo from '@/components/utilities/Logo.vue'

export default {
	components:{ BaseButton, BaseInputText, BasePassword, Logo },
    data(){
		return{
            currentStep: 1,
            email: null,
            code: null,
			password: null,
            password_confirmed: null,
			error: {
				email: null,
				code: null,
				password: null,
				password_confirmed: null
			},
			loading: false,
			loading_send: false
		}
	},
	methods: {
		async sendCodeForEmail(){
			if (this.loading_send) {
				return
			}
			this.loading_send = true
			try {
				await sendCodeForgotPassword({
					email: this.email
				})

				if(this.currentStep === 2){
					this.showSuccess(this.$t('Your code has been sent.'))
				}

				this.currentStep = 2
			} catch (error) {
				this.error.email = error.error.detail.email
			} finally {
				this.loading_send = false
			}
		},
		async checkValidateCode(){
			if (this.loading) {
				return
			}
			this.loading = true
			try {
				await checkCodeForgotPassword({
					email: this.email,
					code: this.code
				})
				this.currentStep = 3
			} catch (error) {
				this.error.code = error.error.detail.code
			} finally {
				this.loading = false
			}
		},
		async updatepassword(){
			if (this.loading) {
				return
			}
			this.loading = true
			try {
				await storeForgotPassword({
					password: this.password,
					password_confirmed: this.password_confirmed,
					email: this.email,
					code: this.code
				})
				this.currentStep = 4
			} catch (error) {
				this.resetErrors(this.error)
			} finally {
				this.loading = false
			}
		},
		nextStep(){
			if(this.currentStep === 1){
				this.sendCodeForEmail()
			}else if(this.currentStep === 2){
				this.checkValidateCode()
			}else if(this.currentStep === 3){
				this.updatepassword()
			}
		}
	}
}
</script>