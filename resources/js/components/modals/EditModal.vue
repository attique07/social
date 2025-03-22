<template> 
    <div class="relative bg-input-background-color border border-border-input-border-color mb-base-2 pt-2 px-base-2 pb-8 dark:bg-slate-800 dark:border-white/10">
        <Mentionable v-model="content" :rows="4" ref="mention" :hasEventEnter="false" :autofocus="true" className="max-h-72" />     
        <div class="absolute bottom-2 start-2 leading-none">
            <EmojiPicker @emoji_click="addEmoji"/>
        </div>   
    </div>
    <div class="text-end">
        <BaseButton :loading="loading" @click="editItem()">{{$t('Edit')}}</BaseButton>
    </div>
</template>
<script>
import { mapActions } from "pinia";
import EmojiPicker from "@/components/utilities/EmojiPicker.vue";
import BaseButton from '@/components/inputs/BaseButton.vue';
import Mentionable from "@/components/utilities/Mentionable.vue";
import { decodeHtml } from '@/utility/index'
import { usePostStore } from '../../store/post';
import { useCommentStore } from '../../store/comment';
import Constant from '@/utility/constant'

export default {
    components: { EmojiPicker, BaseButton, Mentionable },
    inject: ['dialogRef'],
    data(){
        return{
            id: this.dialogRef.data.id,
            content: decodeHtml(this.dialogRef.data.content),
            loading: false
        }
    },
    methods:{
        ...mapActions(usePostStore, ['editPost']),
        ...mapActions(useCommentStore, ['editComment', 'editReply']),
        addEmoji(emoji){		
			this.$refs.mention.addContent(emoji)
		},
        async editItem(){
            if (this.loading) {
                return
            }
            this.loading = true
            try {                    
                switch(this.dialogRef.data.type){
                    case 'posts':
                            await this.editPost({
                                id: this.id,
                                content: this.content
                            })
                        break
                    case 'comments':
                        await this.editComment({
                            id: this.id,
                            content: this.content
                        })
                        break
                    case 'comment_replies':
                        await this.editReply({
                            id: this.id,
                            content: this.content,
                            comment_id: this.dialogRef.data.comment_id
                        })
                        break
                }

                this.dialogRef.close()
                this.showSuccess(this.$t('Edit Successfully.'))
            } catch (error) {
                if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
					this.showPermissionPopup('post',error.error.message)
				} else {
					this.showError(error.error)
				}
            } finally {
                this.loading = false
            }
        }
    }
}
</script>