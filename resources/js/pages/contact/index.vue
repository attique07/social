<template>
	<div class="main-content-section bg-white rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800">
        <div class="flex flex-wrap items-center justify-between gap-1 mb-5">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{$t('Contact Us')}}</h3>
        </div>
        <div>
            <div class="flex flex-wrap gap-x-5 mb-3"> 
                <div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Name')}}</label></div>
                <div class="md:flex-4 w-full">
                    <BaseInputText v-model="data.name" :placeholder="$t('Enter your name')" :error="error.name"/>
                </div>  
            </div>
            <div class="flex flex-wrap gap-x-5 mb-3"> 
                <div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Email')}}</label></div>
                <div class="md:flex-4 w-full">
                    <BaseInputText v-model="data.email" :placeholder="$t('Enter your email')" :error="error.email"/>
                </div>  
            </div>
            <div class="flex flex-wrap gap-x-5 mb-3"> 
                <div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Subject')}}</label></div>
                <div class="md:flex-4 w-full">
                    <BaseInputText v-model="data.subject" :placeholder="$t('Enter your subject')" :error="error.subject"/>
                </div>  
            </div>
            <div class="flex flex-wrap gap-x-5 mb-3"> 
                <div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Message')}}</label></div>
                <div class="md:flex-4 w-full">
                    <BaseTextarea v-model="data.message" :placeholder="$t('Enter your message')" :autoResize="true" :error="error.message" />
                </div>  
            </div>
            <div class="text-center">
                <BaseButton @click="send()" :disabled="disableContact">{{$t('Send')}}</BaseButton>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { storeContact } from '@/api/utility'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import { useAppStore } from '../../store/app'

export default {
    data(){
		return {
            data: {
                email: null,
                message: null,
                subject: null,
                name: null
            },
			error: {
				name: null,
				email: null,
				subject: null,
				message: null
			},
            disableContact: false
		}
	},
    components: { BaseInputText, BaseTextarea, BaseButton },
    mounted(){
        if(this.config.contactEnableRecapcha && window.siteConfig.recapchaPublicKey){
            this.disableContact = true
            setTimeout(() => {
                const recaptcha = this.$recaptchaInstance.value
                recaptcha.showBadge()
                this.disableContact = false
            }, 3000)
        }
    },
    unmounted(){
        if(this.config.contactEnableRecapcha && window.siteConfig.recapchaPublicKey){          
            const recaptcha = this.$recaptchaInstance.value
            recaptcha.hideBadge()
        }
    },
    computed: {
        ...mapState(useAppStore, ['config'])
    },
    methods:{
        async send(){
            try {
                if(this.config.contactEnableRecapcha && window.siteConfig.recapchaPublicKey){
                    await this.$recaptcha("contact").then(token => {
                        this.data.token = token
                    })
                }
                await storeContact(this.data)
                this.showSuccess(this.$t('Thank you! Your message has been sent'))
                Object.keys(this.data).forEach((key) => this.data[key] = null)
                this.resetErrors(this.error)
            } catch (error) {
                this.handleApiErrors(this.error, error)
            }
        }
    }
}
</script>