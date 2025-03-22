<template>
    <div class="options-menu-modal flex flex-col text-main-color dark:text-white outline-none" tabindex="0" autofocus>
        <button class="options-menu-modal-text text-center p-4 text-red-600 font-bold" @click="reportPost(post.id)">{{$t('Report')}}</button>
        <button v-if="post.canDelete" class="options-menu-modal-text options-menu-modal-border text-center p-4 border-t border-light-divider text-red-600 font-bold dark:border-white/10" @click="deletePost(post.id)">{{$t('Delete Post')}}</button>
        <button v-if="post.canEdit" class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="editPost(post)">{{$t('Edit Post')}}</button>
        <button v-if="post.isEdited" class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="viewEditHistory(post.id)">{{$t('View Edit History')}}</button>
        <button v-if="!post.enable_notify" class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="turnOnNotification(post.id)">{{$t('Turn on Notifications')}}</button>
        <button v-else class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="stopPostNotification(post.id)">{{$t('Stop Notifications')}}</button>
        <button class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="cancel()">{{$t('Cancel')}}</button>
    </div>
</template>

<script>
import { mapActions } from 'pinia';
import { checkPopupBodyClass } from '@/utility/index'
import ReportModal from '@/components/modals/ReportModal.vue';
import EditModal from '@/components/modals/EditModal.vue'
import EditHistoriesListModal from '@/components/modals/EditHistoriesListModal.vue';
import { usePostStore } from '../../store/post';

export default {
    data(){
        return{
            post: this.dialogRef.data.post
        }
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    methods:{
        ...mapActions(usePostStore, ['deletePostItem', 'toggleEnableNotificationPostItem']),
        reportPost(postId){
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(ReportModal, {
                data: {
                    type: 'posts',
                    id: postId
                },
                props:{
                    header: this.$t('Report'),
                    class: 'post-report-modal',
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                }
            }), 300);
        },
        deletePost(postId){
            this.dialogRef.close();
            setTimeout(() => this.$confirm.require({
                message: this.$t('Are you sure you want to delete this post?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: () => {
                    this.deletePostItem({
                        id: postId
                    });
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
        editPost(post){
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(EditModal, {
                data: {
                    type: 'posts',
                    id: post.id,
                    content: post.content
                },
                props:{
                    header: this.$t('Edit'),
                    class: 'post-edit-modal',
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                }
             }), 300);
        },
        viewEditHistory(postId){
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(EditHistoriesListModal, {
                data: {
                    type: 'posts',
                    id: postId
                },
                props:{
                    header: this.$t('Edit History'),
                    class: 'edit-history-modal',
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                }
            }), 300);
        },
        stopPostNotification(postId){
            this.toggleEnableNotificationPostItem({
                subject_type: 'posts',
                subject_id: postId,
                action: 'remove'
            })
            this.dialogRef.close();
        },
        turnOnNotification(postId){
            this.toggleEnableNotificationPostItem({
                subject_type: 'posts',
                subject_id: postId,
                action: 'add'
            })
            this.dialogRef.close();
        },
        cancel(){
            this.dialogRef.close();
        }
    }
}
</script>