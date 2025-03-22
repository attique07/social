<template>
    <div class="px-base-2 py-5 text-center">
        <p class="font-bold mb-base-2">{{$t('Invite by link')}}</p>
        <p class="mb-base-2">{{$t('Copy the link below to invite people')}}</p>
        <div class="flex justify-between items-center gap-2 bg-web-wash px-5 py-base-2 rounded-base-10xl border border-dashed border-secondary-box-color dark:bg-dark-web-wash">
            <input :value="user.ref_url" id="myUrl" class="flex-1 truncate bg-transparent focus:outline-none">
            <button class="flex align-items-center gap-2 whitespace-nowrap" @click="copyURL()">
                <BaseIcon name="copy_link" class="text-primary-color dark:text-dark-primary-color"/>
                <span>{{$t('Copy link')}}</span>
            </button>
        </div>
        <p class="font-bold mt-base-7 mb-base-2">{{$t('Invite by email')}}</p>
        <form @submit.prevent="inviteEmails">
            <div class="mb-base-2">
                <BaseInputText v-model="emails" :placeholder="$filters.numberShortener(config.inviteMax, $t('Add up to [number] email address, separated by commas'), $t('Add up to [number] email addresses, separated by commas'))" :tooltip_mobile="$filters.numberShortener(config.inviteMax, $t('Add up to [number] email address, separated by commas'), $t('Add up to [number] email addresses, separated by commas'))"/>
            </div>
            <div class="mb-base-2">         
                <BaseTextarea v-model="message" :placeholder="$t('Message')" :rows="5" />
            </div>
            <BaseButton :loading="emailLoading" :disabled="disableEmail">{{$t('Send')}}</BaseButton>
        </form>
        <p class="font-bold mt-base-7 mb-base-2">{{$t('Invite by CSV')}}</p>
        <div class="mb-1">
            <BaseInputFile ref="BaseInputFile" @upload-file="selectFile" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">{{$t('Import Contact')}}</BaseInputFile>
        </div>
        <div class="text-start mb-base-2">
            <a :href="asset('files/invite_example.csv')" download>{{$t('Download csv sample')}}</a>
        </div>
        <BaseButton :loading="csvLoading" :disabled="disableCsv" @click="inviteCsvFile()" class="mb-base-2">{{$t('Send')}}</BaseButton>
    </div> 
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '../../store/auth'
import { useAppStore } from '../../store/app'
import { inviteByEmails, inviteByCsvFile } from '@/api/invite'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputFile from '@/components/inputs/BaseInputFile.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { BaseIcon, BaseInputText, BaseTextarea, BaseButton, BaseInputFile },
    data(){
        return{
            emails: null,
            message: null,
            csv_file: null,
            token: null,
            emailLoading: false,
            disableEmail: false,
            csvLoading: false,
            disableCsv: false,
        }
    },
    mounted(){
        if(this.config.inviteEmailEnableRecapcha && window.siteConfig.recapchaPublicKey){
            this.disableEmail = true
            this.disableCsv = true
            setTimeout(() => {
                const recaptcha = this.$recaptchaInstance.value
                recaptcha.showBadge()
                this.disableEmail = false
                this.disableCsv = false
            }, 3000)
        }
    },
    unmounted(){
        if(this.config.inviteEmailEnableRecapcha && window.siteConfig.recapchaPublicKey){          
            const recaptcha = this.$recaptchaInstance.value
            recaptcha.hideBadge()
        }
    },
    computed: {
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config'])
    },
    methods: {
        copyURL () {
            let url = document.querySelector('#myUrl')
            url.setAttribute('type', 'text')  
            url.select()
            document.execCommand('copy');
            this.showSuccess(this.$t('This link copied!'))
        },
        async inviteEmails(){
            if (this.emailLoading) {
                return
            }

            try {
                this.emailLoading = true
                if(this.config.inviteEmailEnableRecapcha && window.siteConfig.recapchaPublicKey){
                    await this.$recaptcha("invite").then(token => {
                        this.token = token
                    })
                }
                
                await inviteByEmails({
                    token: this.token,
                    emails: this.emails,
                    message: this.message
                })
                this.emails = null
                this.message = null
                this.showSuccess(this.$t('Your invitation has been sent.'))
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.emailLoading = false
            }
        },
        selectFile(files) {
            this.csv_file = files.target.files[0]
        },
        async inviteCsvFile(){
            if (this.csvLoading) {
                return
            }

            try {
                this.csvLoading = true
                if(this.config.inviteEmailEnableRecapcha && window.siteConfig.recapchaPublicKey){
                    await this.$recaptcha("invite").then(token => {
                        this.token = token
                    })
                }
                let formData = new FormData()
                if (this.csv_file) {
                    if(! this.checkUploadedData(this.csv_file, 'csv')){
                        return
                    }
                    formData.append('csv_file', this.csv_file)
                }				
                formData.append('token', this.token)
                await inviteByCsvFile(formData)
                this.csv_file = null
                this.$refs.BaseInputFile.clearSelectedFile()
                this.showSuccess(this.$t('Your invitation has been sent.'))
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.csvLoading = false
            }
        }
    }
}
</script>