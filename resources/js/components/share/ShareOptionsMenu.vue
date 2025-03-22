<template>
    <div class="options-menu-modal flex flex-col text-main-color dark:text-white">
        <button class="text-center p-4" @click="copyLink(subject.href)">{{$t('Copy link')}}</button>
        <button class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="shareToProfile(subject)">{{$t('Share to your profile')}}</button>
        <button class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="shareToEmails(subjectType, subject)">{{$t('Mail')}} </button>
        <button class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="shareToSocialNetworks(subject)">{{$t('Share to social networks')}}</button>
        <button class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="cancel()">{{$t('Cancel')}}</button>
    </div>
</template>

<script>
import { defineAsyncComponent } from 'vue'
import { checkPopupBodyClass } from '@/utility/index'

export default {
    data() {
        return {
            subjectType: this.dialogRef.data.subjectType,
            subject: this.dialogRef.data.subject
        }
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    methods: {
        copyLink(url) {
            navigator.clipboard.writeText(url)
            this.showSuccess(this.$t('This link copied!'))
            this.dialogRef.close()
        },
        shareToProfile(subject){
            this.dialogRef.close();
            const ShareToProfileModal = defineAsyncComponent(() =>
                import('./ShareToProfileModal.vue')
            )
            setTimeout(() =>this.$dialog.open(ShareToProfileModal, {
                data: {
                    subject: subject
                },
                props:{
                    header: this.$t('Share to your profile'),
                    class: 'share-modal p-dialog-lg',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            }), 300);
        },
        shareToEmails(subjectType, subject){
            this.dialogRef.close();
            const ShareToEmailsModal = defineAsyncComponent(() =>
                import('./ShareToEmailsModal.vue')
            )
            setTimeout(() =>this.$dialog.open(ShareToEmailsModal, {
                data: {
                    type: subjectType,
                    subject: subject
                },
                props:{
                    header: this.$t('Share to email'),
                    class: 'share-modal p-dialog-md',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            }), 300);
        },
        shareToSocialNetworks(subject){
            this.dialogRef.close();
            const ShareToSocialNetworksModal = defineAsyncComponent(() =>
                import('./ShareToSocialNetworksModal.vue')
            )
            setTimeout(() =>this.$dialog.open(ShareToSocialNetworksModal, {
                data: {
                    subject: subject
                },
                props:{
                    header: this.$t('Share to social networks'),
                    class: 'share-modal p-dialog-sm',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            }), 300);
        },
        cancel() {
            this.dialogRef.close();
        }
    }
}
</script>