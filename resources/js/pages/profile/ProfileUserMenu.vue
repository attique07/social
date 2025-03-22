<template>
    <div class="options-menu-modal flex flex-col text-main-color dark:text-white">
        <button class="options-menu-modal-text text-center p-4 text-red-600 font-bold" @click="reportUser(userInfo.id)">{{$t('Report')}}</button>
        <button v-if="user.id !== userInfo.id" class="options-menu-modal-text options-menu-modal-border text-center p-4 border-t border-light-divider text-red-600 font-bold dark:border-white/10" @click="blockUser(userInfo)">{{$t('Block')}}</button>
        <button v-if="userInfo.check_privacy && userInfo.is_followed && userInfo.enable_notify" class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="stopNotificationUser(userInfo.id)">{{$t('Stop Notifications')}}</button>
        <button v-if="userInfo.check_privacy && userInfo.is_followed && !userInfo.enable_notify" class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="turnOnNotificationUser(userInfo.id)">{{$t('Turn on Notifications')}}</button>
        <button class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="cancel()">{{$t('Cancel')}}</button>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { checkPopupBodyClass } from '../../utility'
import { toggleBlockUser } from '../../api/user'
import { toggleStopNotificationUser } from '../../api/follow'
import ReportModal from '@/components/modals/ReportModal.vue'
import { useAuthStore } from '../../store/auth'

export default {
    data() {
        return {
            userInfo: this.dialogRef.data.userInfo
        };
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    computed: {
        ...mapState(useAuthStore, ["user"])
    },
    methods: {
        reportUser(userId) {
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(ReportModal, {
                data: {
                    type: "users",
                    id: userId
                },
                props: {
                    header: this.$t("Report"),
                    class: "user-report-modal",
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            }), 300);
        },
        blockUser(user) {
            this.dialogRef.close();
            setTimeout(() => this.$confirm.require({
                message: user.is_page ? this.$t('Are you sure you want to block this page?') : this.$t('Are you sure you want to block this user?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
                        await toggleBlockUser({
                            id: user.id,
                            action: "add"
                        });
                        this.$router.push({ name: "home" });
                    }
                    catch (error) {
                        this.showError(error.error)
                    }
                    checkPopupBodyClass()
                },
                reject: () => {
                    checkPopupBodyClass()
                },
                onHide: () => {
                    checkPopupBodyClass()
                }
            }), 300);
        },
        async stopNotificationUser(userId) {
            try {
                await toggleStopNotificationUser({
                    id: userId,
                    action: "remove"
                });
                this.userInfo.enable_notify = 0
                this.dialogRef.close();
            }
            catch (error) {
                this.showError(error.error)
            }
        },
        async turnOnNotificationUser(userId) {
            try {
                await toggleStopNotificationUser({
                    id: userId,
                    action: "add"
                });
                this.userInfo.enable_notify = 1
                this.dialogRef.close();
            }
            catch (error) {
                this.showError(error.error)
            }
        },
        cancel() {
            this.dialogRef.close();
        }
    }
}
</script>

<style>

</style>