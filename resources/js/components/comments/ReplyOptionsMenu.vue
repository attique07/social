<template>
    <div class="options-menu-modal flex flex-col text-main-color dark:text-white">
        <button class="options-menu-modal-text text-center p-4 text-red-600 font-bold" @click="reportReplyItem(reply.id)">{{$t('Report')}}</button>
        <button class="options-menu-modal-text options-menu-modal-border text-center p-4 border-t border-light-divider text-red-600 font-bold dark:border-white/10" v-if="reply.canDelete" @click="deleteReplyItem(comment.id, reply.id)">{{$t('Delete Reply')}}</button>
        <button v-if="reply.canEdit" class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="editReply(comment.id, reply)">{{$t('Edit Reply')}}</button>
        <button v-if="reply.isEdited" class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="viewEditHistory(reply.id)">{{$t('View Edit History')}}</button>
        <button class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="cancel()">{{$t('Cancel')}}</button>
    </div>
</template>

<script>
import { mapActions } from 'pinia';
import { checkPopupBodyClass } from '@/utility/index'
import ReportModal from '@/components/modals/ReportModal.vue';
import EditModal from '@/components/modals/EditModal.vue';
import EditHistoriesListModal from '@/components/modals/EditHistoriesListModal.vue'
import { useCommentStore } from '../../store/comment';

export default {
    data(){
        return{
            comment: this.dialogRef.data.comment,
            reply: this.dialogRef.data.reply
        }
    },
    inject: ['dialogRef'],
    methods:{
        ...mapActions(useCommentStore, [ 'deleteReply']),
        reportReplyItem(replyId){
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(ReportModal, {
                data: {
                    type: 'comment_replies',
                    id: replyId
                },
                props:{
                    header: this.$t('Report'),
                    class: 'reply-report-modal',
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                }
             }), 300);
        },
        deleteReplyItem(commentId, replyId){
            this.dialogRef.close();
            setTimeout(() => this.$confirm.require({
                message: this.$t('Are you sure you want to delete this reply?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: () => {
                    this.deleteReply({
                        comment_id: commentId,
                        id: replyId
                    })
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
        editReply(commentId, reply){
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(EditModal, {
                data: {
                    type: 'comment_replies',
                    id: reply.id,
                    content: reply.content,
                    comment_id: commentId
                },
                props:{
                    header: this.$t('Edit'),
                    class: 'reply-edit-modal',
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                }
             }), 300);
        },
        viewEditHistory(replyId){
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(EditHistoriesListModal, {
                data: {
                    type: 'comment_replies',
                    id: replyId
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
        cancel(){
            this.dialogRef.close();
        }
    }
}
</script>

<style>

</style>