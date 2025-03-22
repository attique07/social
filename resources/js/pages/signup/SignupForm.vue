<template>
    <div class="widget-box bg-white rounded-base mb-base-2 max-w-full w-full sm:w-96 p-7 mx-auto dark:bg-slate-800"> 
        <h3 class="text-base-lg font-extrabold mb-5 dark:text-white">{{$t('Sign up')}}</h3>
        <BaseInputText v-model="user.name" :placeholder="$t('Name')" :error="error.name" :left_icon="'profile'" :autocomplete="'name'" @keyup.enter="signup" class="mb-base-2" />
        <BaseInputText v-model="user.user_name" :placeholder="$t('Username')" :error="error.user_name" :left_icon="'profile'" @keyup.enter="signup" :autocomplete="'nickname'" class="mb-base-2" />
        <BaseInputText v-model="user.email" :placeholder="$t('Email')" :error="error.email" :left_icon="'invite'" @keyup.enter="signup" :autocomplete="'on'" class="mb-base-2" />
        <BasePassword v-model="user.password" :error="error.password" :placeholder="$t('Password')" @keyup.enter="signup" class="mb-base-2" />
        <BaseButton :loading="loadingSignup" :disabled="disableSignup" @click="signup" class="w-full">{{$t('Sign up')}}</BaseButton>
        <div class="mt-base-2 text-center">{{ $t('By clicking Sign Up, you agree to our ') }}<router-link :to="{name: 'sp_detail', params: {slug: 'terms-of-service'}}" class="text-primary-color dark:text-dark-primary-color">{{ $t('Terms of Service') }}</router-link> {{ $t('and') }} <router-link :to="{name: 'sp_detail', params: {slug: 'privacy-policy'}}" class="text-primary-color dark:text-dark-primary-color">{{ $t('Privacy Policy') }}</router-link>.</div>
        <div class="mt-4 border-t border-divider pt-4">
            <template v-if="config.openidProviders.length > 0">
                <div class="text-center">{{$t('Or Sign up using')}}</div>
                <div class="flex justify-center flex-wrap gap-3 my-4">
                    <a v-for="(openId, index) in config.openidProviders" :key="index" :href="openId.href">
                        <img class="max-w-[2rem]" :src="openId.photo" :alt="openId.name">
                    </a>
                </div>
            </template>
            <div class="text-center">{{$t('Already had an account?')}}&nbsp;<router-link :to="{name: 'login'}" class="text-primary-color dark:text-dark-primary-color">{{$t('Login')}}</router-link></div>
        </div>
    </div>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BasePassword from '@/components/inputs/BasePassword.vue'
import { useAppStore } from '../../store/app'
import { useAuthStore } from '../../store/auth'
import localData from '@/utility/localData';

export default {
    components: { BaseButton, BaseInputText, BasePassword },
    data() {
        return {
            user: {
                name: null,
                user_name: null,
                email: null,
                password: null,
                ref_code : null
            },
            loadingSignup: false,
            disableSignup: false,
            error: {
				name: null,
				user_name: null,
				email: null,
				password: null
			}
        }
    },
    mounted(){
        if(this.config.signupEnableRecapcha && window.siteConfig.recapchaPublicKey){
            this.disableSignup = true
            setTimeout(() => {
                const recaptcha = this.$recaptchaInstance.value
                recaptcha.showBadge()
                this.disableSignup = false
            }, 3000)
        }
    },
    unmounted(){
        if(this.config.signupEnableRecapcha && window.siteConfig.recapchaPublicKey){          
            const recaptcha = this.$recaptchaInstance.value
            recaptcha.hideBadge()
        }
    },
    computed: {
        ...mapState(useAppStore, ['config'])
    },
    methods: {
        ...mapActions(useAuthStore, ['signupUser']),
        async signup(){
            if (this.loadingSignup) {
                return
            }
            this.loadingSignup = true
            try {
                if(this.config.signupEnableRecapcha && window.siteConfig.recapchaPublicKey){
                    await this.$recaptcha("signup").then(token => {
                        this.user.token = token
                    })
                }
                
				// Check invite
				const refCode = localData.get('ref_code', null)
                if (refCode) {
                    this.user.ref_code = refCode
                }
                await this.signupUser(this.user)
                this.resetErrors(this.error)
                if (refCode) {
					localData.remove('ref_code')
				}
                window.location.href = window.siteConfig.siteUrl
            } catch (error) {
                this.resetErrors(this.error)
                if(error.error.code == 'error_validate'){
                    Object.keys(this.error).forEach((key) => this.error[key] = error.error.detail[key] ? error.error.detail[key] : null)
                }else if(error.error.code == 'inactive'){
                    this.showError(error.error.message)
                    this.$router.push({
                        name: 'home',
                    });
                }else {
                    this.showError(error.error)
                }
            } finally {
                this.loadingSignup = false
            }
        }
    }
}
</script>