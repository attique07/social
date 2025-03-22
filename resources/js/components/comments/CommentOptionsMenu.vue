<template>
    <div class="options-menu-modal flex flex-col text-main-color dark:text-white">
        <button class="options-menu-modal-text text-center p-4 text-red-600 font-bold" @click="reportCommentItem(comment.id)">{{$t('Report')}}</button>
        <button class="options-menu-modal-text options-menu-modal-border text-center p-4 border-t border-light-divider text-red-600 font-bold dark:border-white/10" v-if="comment.canDelete" @click="deleteCommentItem(comment.id)">{{$t('Delete Comment')}}</button>
        <button v-if="comment.canEdit" class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="editComment(comment)">{{$t('Edit Comment')}}</button>
        <button v-if="comment.isEdited" class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="viewEditHistory(comment.id)">{{$t('View Edit History')}}</button>
        <button class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="cancel()">{{$t('Cancel')}}</button>
    </div>
</template>

<script>
import { mapActions } from 'pinia';
import { checkPopupBodyClass } from '@/utility/index'
import ReportModal from '@/components/modals/ReportModal.vue';
import EditModal from '@/components/modals/EditModal.vue'
import EditHistoriesListModal from '@/components/modals/EditHistoriesListModal.vue'
import { useCommentStore } from '../../store/comment';

export default {
    data(){
        return{
            comment: this.dialogRef.data.comment
        }
    },
    inject: ['dialogRef'],
    methods:{
        ...mapActions(useCommentStore, ['deleteComment']),
        reportCommentItem(commentId){
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(ReportModal, {
                data: {
                    type: 'comments',
                    id: commentId
                },
                props:{
                    header: this.$t('Report'),
                    class: 'comment-report-modal',
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                }
             }), 300);
        },
        deleteCommentItem(commentId){
            this.dialogRef.close();
            setTimeout(() => this.$confirm.require({
                message: this.$t('Are you sure you want to delete this comment?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: () => {
                    this.deleteComment({
                        id: commentId
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
        editComment(comment){
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(EditModal, {
                data: {
                    type: 'comments',
                    id: comment.id,
                    content: comment.content
                },
                props:{
                    header: this.$t('Edit'),
                    class: 'comment-edit-modal',
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                }
             }), 300);
        },
        viewEditHistory(commentId){
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(EditHistoriesListModal, {
                data: {
                    type: 'comments',
                    id: commentId
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