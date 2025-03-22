<template>
    <div v-if="story" class="flex h-full lg:rounded-base-lg relative">
        <div class="absolute top-8 left-2 right-2 lg:left-4 lg:right-4 z-10">
            <div class="flex justify-between items-center gap-base-2">
                <router-link v-if="owner" :to="{name: 'profile', params: {user_name: owner.user_name}}" class="flex-1 min-w-0">
                    <div class="flex items-center gap-base-2">
                        <Avatar :user="owner" :size="isMobile ? '40px' : '50px'" :activePopover="false" :border=false />
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center leading-none"><UserName :user="owner" :activePopover="false" class="story-name text-sm text-white min-w-0"/><span class="story-date text-xs leading-tight text-white ms-base-1 whitespace-nowrap">{{ story.created_at }}</span></div>
                            <span v-if="story.song" class="text-white text-xs inline-flex items-center gap-1 w-full">
                                <BaseIcon name="music_note" size="16" />
                                <div class="truncate">{{ story.song.name }}</div>
                            </span>
                        </div>
                    </div>
                </router-link>
                <div class="flex gap-2 text-white story-icon">
                    <button v-if="run" @click="stopPlayStateStory">
                        <BaseIcon name="pause" />
                    </button>
                    <button v-else @click="doPlayStateStory">
                        <BaseIcon name="play" />
                    </button>
                    <button v-if="mute" @click="doMuteSong" v-tooltip.bottom="isMobile ? '' : $t('Unmute')">
                        <BaseIcon name="mute" />
                    </button>
                    <button v-else @click="doUnmuteSong" v-tooltip.bottom="isMobile ? '' : $t('Mute')">
                        <BaseIcon name="unmute" />
                    </button>
                    <button v-if="user.id" @click="openDropdownMenu">
                        <BaseIcon name="more_horiz_outlined" />
                    </button>
                    <button v-if="!isPage" class="block lg:hidden" @click="closeStoryModal">
                        <BaseIcon name="close" />
                    </button>           
                </div>
            </div>        
        </div>
        <audio v-if="story.song" :src="story.song.file_url" ref="audio" autoplay loop :muted="mute"></audio>
        <StoryContent :story="story" :limit="250" class="text-xl" @click_read_more="stopPlayStateStory"/>
        <div class="flex justify-between items-end gap-5 absolute bottom-4 left-2 right-2 lg:left-4 lg:right-4 z-10">
            <button v-if="owner && user.id == owner.id" class="story-viewers border-b border-white pb-1 flex items-center text-xs text-white whitespace-nowrap" @click="openViewersModal(story.id)"><BaseIcon name="people" size="16" class="me-1"/>{{ $filters.numberShortener(story.count, $t('[number] viewer'), $t('[number] viewers')) }}</button>     
        </div>
    </div>
    <div v-if="canMessage" class="absolute bottom-4 left-2 right-2 lg:left-4 lg:right-4 story-detail-comment">
        <div class="flex gap-base-1 leading-none bg-white dark:bg-slate-800 border border-divider dark:border-white/10 rounded-xl">
            <EmojiTextField ref="story_message" :placeholder="$t('Enter your Message')" :rows="1" @content_change="inputChange" @press_enter="sendStoryMessage(story.id)" @focus="handleFocusMessage" @focusout="handleFocusoutMessage" class="max-h-14 border-0 py-base-2"/>
            <div class="flex items-center gap-base-1 py-base-1 pe-2 self-start">
                <EmojiPicker ref="emoji_picker" class="text-main-color dark:text-white" @emoji_click="addEmoji" @open_emoji="handleOpenEmoji" @close_emoji="handleCloseEmoji"/>
                <BaseButton @click.prevent="sendStoryMessage(story.id)" :loading="loading" :disabled="!message_content" class="h-[32px] w-[32px] rounded-full rtl:rotate-180">
                    <BaseIcon name="send_message" class="text-white" size="14"></BaseIcon>
                </BaseButton>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { checkPopupBodyClass } from '@/utility/index'
import { storeMessages } from '@/api/stories'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import StoryOptionsMenu from '@/components/stories/StoryOptionsMenu.vue'
import ViewersModal from '@/components/stories/ViewersModal.vue'
import StoryContent from '@/components/stories/StoryContent.vue'
import EmojiTextField from '@/components/utilities/EmojiTextField.vue'
import EmojiPicker from '@/components/utilities/EmojiPicker.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import { useAppStore } from '../../store/app'
import { useAuthStore } from '../../store/auth'
import PermissionModal from '@/components/modals/PermissionModal.vue';

export default {
    components: { BaseIcon, StoryContent, EmojiTextField, EmojiPicker, Avatar, BaseButton, UserName },
    props: {
        story: {
            type: Object,
            default: null
        },
        owner: {
            type: Object,
            default: null,
        },
        runningStory: {
            type: Boolean,
            default: false
        },
        canMessage: {
            type: Boolean,
            default: false
        },
        isPage: {
            type: Boolean,
            default: false
        },
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data() {    
        return {
            mute: true,
            run: this.runningStory,            
            message_content: null,
            runState: this.runningStory,
            emojiOpened: false,
            focusMessage: false,
            loading: false
		}
	},
    watch: {
        time(newTime) {
            if (parseInt(newTime) == this.config.storyTimeout) {
                this.run = false
            }
        },
        runningStory(){
            this.run = this.runningStory
            if(!this.run){
                this.stopPlayStory()
            }
        },
        story() {
            this.runState = this.runningStory
            if(this.story.song){
                this.$nextTick(() => {
                    this.$refs.audio.currentTime = 0;
                    this.$refs.audio.play()
                })              
            }
            this.$refs.story_message?.resetContent()
        },
        focusMessage(){
            if(this.focusMessage){
                this.stopPlayStory()
            }else{
                if(this.emojiOpened){
                    this.stopPlayStory()
                }else{
                    this.doPlayStory()
                }
            }
        },
        emojiOpened(){
            if(this.emojiOpened){
                this.stopPlayStory()
            }else{
                if(this.focusMessage){
                    this.stopPlayStory()
                }else{
                    this.doPlayStory()
                }
            }
        }
    },
    computed: {
		...mapState(useAppStore, ['config', 'isMobile']),
        ...mapState(useAuthStore, ['user'])
	},
    methods: {
        doPlayStory(){
            if (! this.runState) {
                return 
            }
            this.run = true
            if(this.$refs.audio){
                this.$refs.audio.play()
            }
            this.$emit('play_story')
        },
        stopPlayStory(){
            this.run = false
            if(this.$refs.audio){
                this.$refs.audio.pause()
            }
            this.$emit('stop_story')
        },
        doUnmuteSong(){
            this.mute = true
        },
        doMuteSong(){
            this.mute = false
        },
        openDropdownMenu(){
            this.$dialog.open(StoryOptionsMenu, {
                data: {
                    story: this.story,
                    canDelete: this.story.canDelete
                },
                props:{
                    showHeader: false,
                    class: 'dropdown-menu-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                    this.doPlayStory()
                }
            });
            this.stopPlayStory()
        },
        openViewersModal(id){
            this.$dialog.open(ViewersModal, {
                data: {
                    itemId: id
                },
                props:{
                    header: this.$t('Viewers'),
                    class: 'likers-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                    this.doPlayStory()
                }
            });
            this.stopPlayStory()
        },
        inputChange(content){
            this.message_content = content;
        },
        addEmoji(emoji){		
			this.$refs.story_message.addContent(emoji)
		},
        async sendStoryMessage(storyId){
            let permission = 'chat.allow'
            if (! window._.has(this.user.permissions, permission) || ! this.user.permissions[permission]) {
                this.$dialog.open(PermissionModal, {
                    props:{
                        header: this.$t('Permission'),
                        modal: true,
                        draggable: false
                    },
                    data: {
                        permission: permission
                    },
                    onClose: () => {
                        this.doPlayStateStory();
                    }
                })
                this.$refs.story_message.setContent('')
                this.$refs.story_message.blurTextarea()
                this.$refs.emoji_picker.closeEmoji()
                this.stopPlayStateStory()
                return
            }

            try {
                if (this.loading) {
					return
				}
                if (this.message_content.trim() == '') {
                    return
                }

                this.loading = true

                await storeMessages({
                    id: storyId,
                    content: this.message_content
                })
                this.$refs.story_message.setContent('')
                this.$refs.story_message.blurTextarea()
                this.$refs.emoji_picker.closeEmoji()
                this.showSuccess(this.$t('Your message has been sent.'))
                this.doPlayStateStory()
                this.loading = false
            } catch (error) {
				this.loading = false
                this.showError(error.error)
            }
        },
        doPlayStateStory(){
            this.runState = true
            this.doPlayStory()
        },
        stopPlayStateStory(){
            this.runState = false
            this.stopPlayStory()
        },
        closeStoryModal(){
            this.$emit('close_story_modal')
        },
        handleOpenEmoji(){
            this.emojiOpened = true
        },
        handleCloseEmoji(){
            this.emojiOpened = false
        },
        handleFocusMessage(){
            setTimeout(() => {
                this.focusMessage = true
            }, 200);
        },
        handleFocusoutMessage(){
            setTimeout(() => {
                this.focusMessage = false
            }, 200);
        }
    },
    emits: ['play_story', 'stop_story', 'close_story_modal']
}
</script>