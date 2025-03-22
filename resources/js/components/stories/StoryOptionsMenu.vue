<template>
    <div class="options-menu-modal flex flex-col text-main-color dark:text-white">
        <button class="options-menu-modal-text text-center p-4 text-red-600 font-bold" @click="reportStory(story.id)">{{$t('Report')}}</button>
        <button v-if="canDelete" class="options-menu-modal-text options-menu-modal-border text-center p-4 border-t border-light-divider text-red-600 font-bold dark:border-white/10" @click="deleteStory(story.id)">{{$t('Delete Story')}}</button>
        <button class="options-menu-modal-border options-menu-modal-sub-text text-center p-4 border-t border-light-divider dark:border-white/10" @click="cancel()">{{$t('Cancel')}}</button>
    </div>
</template>

<script>
import { mapActions } from 'pinia';
import { checkPopupBodyClass } from '@/utility/index'
import ReportModal from '@/components/modals/ReportModal.vue';
import { useStoriesStore } from '../../store/stories';

export default {
    data(){
        return{
            story: this.dialogRef.data.story,
            canDelete: this.dialogRef.data.canDelete
        }
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    methods:{
        ...mapActions(useStoriesStore, ['doDeleteStoryItem']),
        reportStory(storyItemId){
            setTimeout(() => this.$dialog.open(ReportModal, {
                data: {
                    type: 'story_items',
                    id: storyItemId
                },
                props:{
                    header: this.$t('Report'),
                    class: 'post-report-modal',
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                    this.dialogRef.close();
                }
            }), 300);
        },
        deleteStory(storyItemId){
            setTimeout(() => this.$confirm.require({
                message: this.$t('Are you sure you want to delete this story?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: () => {
                    this.doDeleteStoryItem({
                        id: storyItemId,
                        storyId: this.story.story_id
                    });
                    checkPopupBodyClass()
                    this.dialogRef.close();
                },
                reject: () => {
                    checkPopupBodyClass()
                },
                onHide: () => {
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