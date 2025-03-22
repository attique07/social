<template>
    <div class="flex justify-between gap-4">
        <EmojiPicker @emoji_click="addContent"/>
        <div class="flex-1 text-base-none">
            <Mentionable v-model="content" :placeholder="$t('Add comment')" :rows="1" ref="mention" @press_enter="postComment()" className="max-h-16"/>
        </div>
        <div>
            <button :disabled="!content" class="feed-comment-info-comment-holder-btn text-primary-color font-bold text-xs uppercase disabled:opacity-50 dark:text-dark-primary-color" @click="postComment()">{{$t('Post')}}</button>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import Mentionable from '@/components/utilities/Mentionable.vue'
import EmojiPicker from '@/components/utilities/EmojiPicker.vue'
import { useAuthStore } from '../../store/auth'

export default {
    components: {
        Mentionable,
        EmojiPicker
    },
    data(){
        return{
            content: ''
        }
    },
    computed:{
        ...mapState(useAuthStore, ['authenticated'])
    },
    methods:{
        addContent(content) {
            this.$refs.mention.addContent(content)
        },
        postComment() {         
            if(this.authenticated){
                if (this.content.trim() != '') {
                    this.$emit('post_comment', this.content)
                    this.setContent('')
                    this.content = ''
                }
            }else{
                this.showRequireLogin()
            }
        },
        setContent(content) {
            this.content = content;
            this.$refs.mention.setContent(content)
        }
    },
    emits: ['post_comment']
}
</script>