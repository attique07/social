<template>
    <Loading v-if="loading_status" />
    <form v-else @submit.prevent="saveEmailSettings">
        <div class="flex gap-5 justify-between mb-5">
            <h4>{{$t('Unsubscribe from all mailings')}}</h4>
            <div><BaseSwitch v-model="email_enable" /></div>
        </div>
        <div v-if="email_enable">
            <div class="flex gap-5 justify-between mb-5">
                <h4>{{$t('Daily email notification (Get emails to find out what’s going on when you’re not on system. You can turn them off anytime)')}}</h4>
                <div><BaseSwitch v-model="daily_email_enable" /></div>
            </div>
        </div>
        <div class="text-center mt-8">
            <BaseButton :loading="loading">{{$t('Save')}}</BaseButton>
        </div>
    </form>
</template>

<script>
import { getEmailSetting, saveEmailSetting } from '@/api/setting';
import Loading from '@/components/utilities/Loading.vue';
import BaseSwitch from '@/components/inputs/BaseSwitch.vue'
import BaseButton from '@/components/inputs/BaseButton.vue';

export default {
    components: { BaseSwitch, Loading, BaseButton },
    mounted(){
        this.getEmailSettings()
    },
    data() {
        return {
            loading_status : true,
            email_enable : true,
            daily_email_enable : true,
            loading: false
        }
    },
    methods: {
        async getEmailSettings() {
            try {
				const response = await getEmailSetting()
                this.email_enable = response.email_enable
                this.daily_email_enable = response.daily_email_enable
                this.loading_status = false
                return response
			} catch (error) {
                this.loading_status = false
			}
        },
        async saveEmailSettings() {
            if (this.loading) {
                return
            }

            this.loading = true
            try {
                var data = {
                    'email_enable': this.email_enable,
                    'daily_email_enable': this.daily_email_enable
                }

				await saveEmailSetting(data)
                this.showSuccess(this.$t('Your changes have been saved.'))
			} catch (error) {
                this.showError(error.error)
			} finally {
                this.loading = false
            }
        }
    }
}
</script>

<style>

</style>