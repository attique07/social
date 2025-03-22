<template>
    <div class="mb-base-2">{{$t('Email')}}</div>
    <BaseInputText v-model="emails" :placeholder="$filters.numberShortener(config.shareEmailMax, $t('Add up to [number] email address, separated by commas'), $t('Add up to [number] email addresses, separated by commas'))" class="mb-5"/>
    <div class="mb-base-2">{{$t('Message')}}</div>
    <BaseTextarea :rows="5" :placeholder="$t('Message')" v-model="message" class="mb-base-2"/>
    <div class="text-end">
        <BaseButton @click="shareToEmail()" :loading="loadingShare" :disabled="disableShare">{{$t('Share')}}</BaseButton>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { shareToEmails } from '@/api/utility'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import { useAppStore } from '@/store/app';

export default {
    components: { BaseButton, BaseInputText, BaseTextarea },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            loadingShare: false,
            token: null,
            emails: null,
            message: null,
            type: this.dialogRef.data.type,
            subject: this.dialogRef.data.subject,
            disableShare: false
        }
    },
    mounted(){
        if(this.config.shareEmailEnableRecapcha && window.siteConfig.recapchaPublicKey){
            this.disableShare = true
            setTimeout(() => {
                const recaptcha = this.$recaptchaInstance.value
                recaptcha.showBadge()
                this.disableShare = false
            }, 3000)
        }
    },
    unmounted(){
        if(this.config.shareEmailEnableRecapcha && window.siteConfig.recapchaPublicKey){          
            const recaptcha = this.$recaptchaInstance.value
            recaptcha.hideBadge()
        }
    },
    computed: {
        ...mapState(useAppStore, ['config'])
    },
    methods:{
        async shareToEmail(){
            this.loadingShare = true
            try {
                if(this.config.shareEmailEnableRecapcha && window.siteConfig.recapchaPublicKey){
                    await this.$recaptcha("share").then(token => {
                        this.token = token
                    })
                }
                await shareToEmails({
                    token: this.token,
                    subject_type: this.type,
                    subject_id: this.subject.id,
                    emails: this.emails,
                    message: this.message
                })
                this.dialogRef.close()
                this.showSuccess(this.$t('Shared Successfully.'))
                this.loadingShare = false
            } catch (error) {
                this.showError(error.error)
                this.loadingShare = false
            }
        }
    }
}
</script>