<template>
    <div class="widget-box bg-white rounded-base max-w-full w-full sm:w-96 p-7 mx-auto mb-base-2 dark:bg-slate-800"> 
        <h3 class="text-base-lg font-extrabold mb-5 dark:text-white">{{$t('Login')}}</h3>
        <BaseInputText v-model="user.email" :placeholder="$t('Email')" :error="error.email" :left_icon="'invite'" @keyup.enter="login" class="mb-base-2" />
        <BasePassword v-model="user.password" :placeholder="$t('Password')" autocomplete="current-password" :error="error.password" @keyup.enter="login" class="mb-base-2" />
        <BaseButton @click="login" :loading="loadingLogin" :disabled="disableLogin" class="w-full">{{$t('Login')}}</BaseButton>
        <template v-if="config.openidProviders.length > 0">
            <div class="my-5 text-center">{{$t('Or Sign in using')}}</div>
            <div class="flex flex-wrap gap-3 justify-center mb-5">
                <a v-for="(openId, index) in config.openidProviders" :key="index" :href="openId.href">
                    <img class="max-w-[2rem]" :src="openId.photo" :alt="openId.name">
                </a>
            </div>
        </template>
        <div class="mt-5 text-center"><router-link :to="{ name: 'recover' }" class="text-primary-color dark:text-dark-primary-color">{{$t('Forgot password')}}</router-link></div>
        <div v-if="this.config.signupEnable" class="mt-5 text-center">{{$t("Don't have an account?")}}&nbsp;<router-link :to="{ name: 'signup' }" class="text-primary-color dark:text-dark-primary-color">{{$t('Sign up')}}</router-link></div>
    </div>
</template>
<script>
import { mapState } from 'pinia'
import { useAppStore } from '../../store/app'
import { useAuthStore } from '../../store/auth'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BasePassword from '@/components/inputs/BasePassword.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'

export default {
    components: { BaseButton, BasePassword, BaseInputText },
    data() {
        return {
            user: {
                email: null,
                password: null
            },
            loadingLogin: false,
            disableLogin: false,
            error: {
				email: null,
				password: null
			}
        }
    },
    mounted(){
        if(this.config.loginEnableRecapcha && window.siteConfig.recapchaPublicKey){
            this.disableLogin = true
            setTimeout(() => {
                const recaptcha = this.$recaptchaInstance.value
                recaptcha.showBadge()
                this.disableLogin = false
            }, 3000)
        }
    },
    unmounted(){
        if(this.config.loginEnableRecapcha && window.siteConfig.recapchaPublicKey){          
            const recaptcha = this.$recaptchaInstance.value
            recaptcha.hideBadge()
        }
    },
    computed: {
        ...mapState(useAppStore, ['config'])
    },
    methods: {
        async login() {
            if (this.loadingLogin) {
                return
            }
            this.loadingLogin = true
            try {
                if(this.config.loginEnableRecapcha && window.siteConfig.recapchaPublicKey){
                    await this.$recaptcha("login").then(token => {
                        this.user.token = token
                    })
                }
                await useAuthStore().login(this.user);
                var link = window.siteConfig.siteUrl;
                if (this.$route.query.redirect) {
                    var redirectLink = atob(this.$route.query.redirect);
                    if (redirectLink.search(window.siteConfig.siteUrl) !== -1) {
                        link = redirectLink;
                    }
                }
            
                window.location.href = link;
                
                this.resetErrors(this.error)
            }
            catch (error) {
                this.handleApiErrors(this.error, error)
            }
            finally {
                this.loadingLogin = false;
            }
        }
    }
}
</script>