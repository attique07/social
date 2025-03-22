<template>
    <UploadWrap @drop_data="dropChatImages" class="flex flex-col flex-1 w-full min-h-0">  
        <div class="flex-1 min-w-0 min-h-0 relative">
            <button v-if="showScrollToNewMessageBtn" :style="{boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1),0 8px 20px rgba(0, 0, 0, 0.1)'}" class="scroll-bottom-btn absolute bottom-4 left-1/2 -translate-x-1/2 bg-white text-primary-color rounded-full p-base-2 z-10 dark:bg-slate-700 dark:text-dark-primary-color" @click="scrollToEnd">
                <BaseIcon name="arrow_downward" />
            </button>
            <div class="flex flex-col messages-list relative px-5 h-full overflow-auto scrollbar-thin py-base-2" ref="messages_list" @scroll="onScroll">
                <span ref="top_messages_list" class="absolute top-5">&nbsp;</span>
                <div class="flex-1"></div>
                <Loading v-if="loadingRoomMessagesList"/>
                <template v-else>
                    <InfiniteLoading v-if="loadmoreRoomMessagesList" @infinite="loadMoreRoomMessages">				
                        <template #spinner>
                            <Loading />
                        </template>
                        <template #complete><span></span></template>
                    </InfiniteLoading>  
                    <div v-for="(message, index) in roomMessagesList" :key="message.id" class="w-full py-1">    
                        <div v-if="index === 0 || (message.created_at_time - roomMessagesList[index > 0 ? (index - 1) : 0].created_at_time > 1000 )" class="chat-date-time text-xs text-sub-color text-center font-medium mt-1 mb-3 dark:text-slate-400">
                            {{message.created_at}}
                        </div>
                        <ChatMessageItem :message="message" :owner="message.user_id === user.id" :room_info="roomInfo" :show-avatar="index === 0 || (message.user_id !== roomMessagesList[index > 0 ? (index - 1) : 0].user_id)"/>
                        <!-- Users have seen message -->
                        <template v-if="roomInfo && roomInfo.status == 'accepted' && roomInfo.user_status != 'sent'">
                            <template v-for="member in roomInfo.members">
                                <template v-if="member.id != user.id && message.id == member.message_seen_id"> <!-- Messages of current user -->
                                    <img class="w-5 h-5 rounded-full mt-2 ms-auto" :key="member.id" :src="member.avatar" :alt="member.name" />
                                </template>
                            </template>
                        </template>
                    </div>                                                         
                </template>       
                <div v-if="typing" class="text-xs text-sub-color dark:text-slate-400 mt-base-2">
                    {{$t('Typing ...')}}
                </div>
                <span ref="bottom_messages_list"></span>              
            </div>
        </div>
        <template v-if="roomInfo">
            <div v-if="roomInfo.enable && roomInfo.status !== 'accepted'" class="flex gap-base-2 p-base-2">
                <button @click="doActionRoom(roomInfo.id, 'accept')" class="accept-request-btn flex-1 p-base-2 border border-divider rounded-base-10xl text-base-green dark:border-gray-700">{{$t('Accept')}}</button>
                <button @click="doActionRoom(roomInfo.id, 'block')" class="decline-request-btn flex-1 p-base-2 border border-divider rounded-base-10xl text-base-red dark:border-gray-700">{{$t('Block')}}</button>
                <button @click="doActionRoom(roomInfo.id, 'delete')" class="decline-request-btn flex-1 p-base-2 border border-divider rounded-base-10xl text-base-red dark:border-gray-700">{{$t('Delete')}}</button>
            </div>
            <div v-if="roomInfo.enable && !roomInfo.is_group && roomInfo.user_status === 'sent'" class="flex gap-base-2 p-base-2">
                <span class="waiting-request flex-1 p-base-2 border border-divider rounded-base-10xl text-center bg-gray-6 text-main-color dark:bg-slate-600 dark:text-slate-300 dark:border-slate-600">{{$t('Wait for request to be accepted')}}</span>
            </div>
            <div v-if="roomInfo.enable && roomInfo.status === 'accepted'" class="relative px-base-2 py-base-2 rounded-b-none md:rounded-b-base-lg">
                <div v-if="chatImagesUpload.length || chatImagesUploadLoading.length || chatFilesUpload.length" class="flex gap-base-2 overflow-x-auto overflow-y-hidden whitespace-nowrap py-base-2">
                    <template v-if="chatImagesUpload.length">
                        <div v-for="image in chatImagesUpload" :key="image.subject.id" class="flex-shrink-0 border border-divider inline-block w-16 h-16 bg-cover bg-center relative rounded-md dark:border-white/10" :style="{ backgroundImage: `url(${image.subject.url})`}">
                            <button class="shadow-md inline-flex items-center justify-center absolute -top-2 -end-2 bg-white border border-divider text-main-color rounded-full w-5 h-5" @click="removeChatImage(image.id)">
                                <BaseIcon name="close" size="16" />
                            </button>
                        </div>                     
                    </template>
                    <div v-for="index in chatImagesUploadLoading" :key="index" class="flex-shrink-0 inline-flex items-center justify-center w-16 h-16 bg-cover bg-center border border-divider dark:border-white/10 rounded-md">
                        <span class="loading-icon">
                            <div class="loader"></div>
                        </span>
                    </div>
                    <button v-if="chatImagesUpload.length || chatImagesUploadLoading.length" class="flex-shrink-0 inline-flex items-center justify-center w-16 h-16 text-main-color border border-divider dark:text-white/50 dark:border-white/10 rounded-md hover:bg-hover" @click="this.$refs.uploadChatImages.open()">
                        <BaseIcon name="photo" />
                    </button>
                    <template v-if="chatFilesUpload.length">
                        <div v-for="file in chatFilesUpload" :key="file.id" class="bg-web-wash border border-divider p-base-2 rounded-md relative max-w-[200px] w-full dark:bg-dark-web-wash dark:border-slate-700">
                            <div class="flex items-center gap-2">
                                <BaseIcon name="file"></BaseIcon>
                                <span class="truncate">{{ file.subject.name }}</span>
                            </div>
                            <button class="shadow-md inline-flex items-center justify-center absolute -top-2 -end-2 bg-white border border-divider text-main-color rounded-full w-5 h-5" @click="removeChatFile(file.id)">
                                <BaseIcon name="close" size="16" />
                            </button>
                        </div>
                    </template>
                </div>
                <div v-if="replyMessage" class="reply-status-item bg-web-wash p-base-2 absolute bottom-full left-0 right-0 w-full dark:bg-dark-web-wash z-10">
                    <div class="flex justify-between gap-base-2 text-sm font-bold mb-1">
                        {{$t('Replying to')}} {{ user.id === replyMessage.user.id ? $t('yourself') : replyMessage.user.name }}
                        <button @click="cancelReplyMessage()">
                            <BaseIcon name="close" size="16" class="reply-status-item-icon"/>
                        </button>
                    </div>
                    <div class="text-xs text-sub-color dark:text-slate-400">{{ replyType }}</div>
                </div>
                <div class="flex items-start gap-2 leading-none">
                    <div class="chat-form w-full flex gap-base-1 leading-none bg-white dark:bg-slate-800 border border-divider dark:border-white/10 rounded-md">
                        <EmojiTextField :placeholder="$t('Write Message')" :rows="1" ref="chat_content" class="max-h-24 border-0" @content_change="inputChange" @paste_content="pasteImage" @press_enter="sendMessages(roomInfo.id)" :autofocus="true"/>
                        <div class="flex items-center gap-base-1 py-base-1 pe-2 self-start">
                            <button v-if="config.wallet.enable" @click="handleClickSendFund(getOthersMemberInRoom(roomInfo.members)[0])" v-tooltip.top="{value: $t('Send Fund'), showDelay: 1000}">
                                <BaseIcon name="coin" />
                            </button>
                            <UploadFiles v-if="chatImagesUpload.length === 0" ref="uploadChatFiles" @upload="uploadChatFiles" v-tooltip.top="{value: $t('Attach Files'), showDelay: 1000}" />
                            <UploadImages v-if="chatFilesUpload.length === 0" ref="uploadChatImages" @upload="uploadChatImages" v-tooltip.top="{value: $t('Attach Photos'), showDelay: 1000}" />
                            <EmojiPicker ref="emojiPicker" @emoji_click="addEmoji" v-tooltip.top="{value: $t('Add Emoji'), showDelay: 1000}" appendTo="self" />
                            <TenorGifs v-if="chatFilesUpload.length === 0" @upload="uploadChatGif" v-tooltip.top="{value: $t('Choose a GIF'), showDelay: 1000}" appendTo="self" />
                        </div>
                    </div>
                    <div>
                        <BaseButton v-if="isMobile" @touchend.prevent="sendMessages()" class="send-message-btn h-[38px] w-[38px] rtl:rotate-180" :loading="loadingSendMessage">
                            <BaseIcon name="send_message" class="text-white" size="20" />
                        </BaseButton>
                        <BaseButton v-else @click.prevent="sendMessages()" class="send-message-btn h-[38px] w-[38px] rtl:rotate-180" :loading="loadingSendMessage">
                            <BaseIcon name="send_message" class="text-white" size="20" />
                        </BaseButton>
                    </div>
                </div>
            </div>
        </template>
    </UploadWrap>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { isVisible } from '@/utility';
import { uploadChatImages, uploadChatFiles, deleteChatItem } from '@/api/chat';
import Constant from '@/utility/constant'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import Loading from '@/components/utilities/Loading.vue';
import EmojiPicker from '@/components/utilities/EmojiPicker.vue';
import EmojiTextField from '@/components/utilities/EmojiTextField.vue';
import InfiniteLoading from 'v3-infinite-loading'
import { useAuthStore } from '@/store/auth';
import { useChatStore } from '@/store/chat';
import { useAppStore } from '@/store/app';
import { useUtilitiesStore } from '@/store/utilities';
import UploadWrap from '@/components/layout/UploadWrap.vue';
import BaseButton from '@/components/inputs/BaseButton.vue'
import ChatMessageItem from './ChatMessageItem.vue';
import SendFundChatModal from '@/components/modals/SendFundChatModal.vue'
import TenorGifs from '@/components/utilities/TenorGifs.vue'
import UploadFiles from '@/components/utilities/UploadFiles.vue'
import UploadImages from '@/components/utilities/UploadImages.vue'
var checkTypingTimer = null

export default {
    components: { Loading, BaseIcon, InfiniteLoading, EmojiPicker, EmojiTextField, UploadWrap, BaseButton, ChatMessageItem, TenorGifs, UploadFiles, UploadImages },
    props: ['room_id'],
    data(){
        return{
            currentPage: 1,
            chatImagesUpload: [],
			chatImagesUploadLoading: [],
            chat: {
				type: 'text',
				content: '',
				items: null
			},
            typing: false,
            typingTimer: false,
            typingSend: false,
            messages: [],
            loadingSendMessage: false,
            showScrollToNewMessageBtn: false,
            chatFilesUpload: []
        }
    },
    mounted(){
        this.getRoomMessages({roomId: this.room_id, page: this.currentPage})
        this.getRoomDetail(this.room_id)
        this.scrollToEnd()
    },
    unmounted(){
        this.clearRoomDetail();
        this.clearRoomMessagesList();
        if (this.config.broadcastEnable && this.roomInfo && this.roomInfo.status == 'accepted') {
            window.Echo.leave(Constant.CHANNEL_ROOM + this.room_id)
        }
        this.setReplyMessage(null)
    },
    watch: {
        roomMessagesList: {
            handler: function() {
                if(isVisible(this.$refs.top_messages_list, this.$refs.messages_list)){
                    this.resetScroll()
                }
                if(isVisible(this.$refs.bottom_messages_list, this.$refs.messages_list)){
                    this.scrollToEnd()
                }
            },
            deep: true
        },
        roomInfo(roomInfo){
            if (roomInfo) {
                this.markSeenRoom(roomInfo.id)
                if (! this.config.broadcastEnable) {
                    this.pingNotification()
                }
                let self = this;
                if (this.config.broadcastEnable && roomInfo.status == 'accepted') {
                    window.Echo.private(Constant.CHANNEL_ROOM + roomInfo.id)
                        .listenForWhisper('typing', (e) => {                        
                        if (e.user_id == self.user.id) {
                            return
                        }
                        self.typing = true;

                        if (this.typing) {
                            if(isVisible(self.$refs.bottom_messages_list, self.$refs.messages_list)){
                                self.scrollToEnd()
                            }                            
                        }
                        
                        clearTimeout(checkTypingTimer)
                        checkTypingTimer = setTimeout(function() {
                            self.typing = false
                        }, 3000);
                    });
                }
                this.scrollToEnd()
            }
        },
        replyMessage(){
            this.$refs.chat_content.setContent(this.chat.content)
        },
        room_id(_, previousRoomId){
            this.getRoomMessages({roomId: this.room_id, page: this.currentPage})
            this.getRoomDetail(this.room_id)
            this.scrollToEnd()
            if (this.config.broadcastEnable && this.roomInfo && this.roomInfo.status == 'accepted') {
                window.Echo.leave(Constant.CHANNEL_ROOM + previousRoomId)
            }
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useChatStore, ['loadingRoomMessagesList', 'roomMessagesList', 'roomInfo', 'loadmoreRoomMessagesList', 'replyMessage']),
        ...mapState(useAppStore, ['isMobile', 'config']),       
        replyType(){
            if(this.replyMessage.content){
                return this.replyMessage.content
            }
            switch (this.replyMessage.type) {
                case 'photo':
                    return this.$t('Images')
                case 'file':
                    return this.$t('Files')
                case 'send_fund':
                    return this.$t('Send Funds')
                default:
                    return null
            }
        }
    },
    methods:{
        ...mapActions(useChatStore, ['getRoomMessages', 'getRoomDetail', 'doActionChatRoom', 'sendMessage','clearRoomDetail', 'markSeenRoom', 'clearRoomMessagesList', 'setRoomUserStatus', 'removeRoomId', 'setReplyMessage']),
        ...mapActions(useUtilitiesStore, ['pingNotification', 'setShowChatBoxBottom']),
        loadMoreRoomMessages($state){
            this.getRoomMessages({roomId: this.room_id, page: ++this.currentPage}).then((response) => {
                if(response.has_next_page){
                    $state.loaded()
                }else{
                    $state.complete()
                }
            })								
        },
        async doActionRoom(roomId, action){
            await this.doActionChatRoom({roomId, action})
            switch (action) {
                case 'accept':
                    if(this.showMiniChatBox()){
                        this.setShowChatBoxBottom(true, roomId, 'chat')
                    } else {
                        this.$router.push({
                            name: 'chat',
                            params: {
                                'room_id': roomId
                            }
                        });
                    }
                    break
                case 'block':
                case 'delete': {    
                    this.removeRoomId(roomId)    
                    if(this.showMiniChatBox()){
                        this.setShowChatBoxBottom(true, null, 'chat_requests')
                    }         
                    break
                }
            }
        },
        checkType() {
            if(this.chatImagesUpload.length){					
				this.chat.type = 'photo'
			}else if(this.chatFilesUpload.length){
                this.chat.type = 'file'
            }else{
				this.chat.type = 'text'
			}
		},
        uploadChatImages(event){
			this.startUploadChatImages(event.target.files)
		},
        dropChatImages(event){
			this.startUploadChatImages(event.dataTransfer.files)
		},
        pasteImage(event){
            this.startUploadChatImages(event.clipboardData.items, true)			
		},
        async startUploadChatImages(uploadedFiles, clipboard){
            if (typeof clipboard === 'undefined') {
                clipboard = false
            }
			for( var i = 0; i < uploadedFiles.length; i++ ){
                if (clipboard) {
					// Skip content if not image
					if (uploadedFiles[i].type.indexOf("image") == -1) continue;
				}
				var checkUpload = true
				if (! clipboard) {
					checkUpload = this.checkUploadedData(uploadedFiles[i])
				}
				if(checkUpload){
					let formData = new FormData()
                    var blob = uploadedFiles[i]
                    if (clipboard) {
                        blob = uploadedFiles[i].getAsFile();
                    }
                    formData.append('file', blob)
                    this.chatImagesUploadLoading.unshift(i)
                    try {
                        const response = await uploadChatImages(formData);
                        this.chatImagesUpload.push(response);
                        this.checkType()
                        this.chatImagesUploadLoading.shift()
                        this.$refs.chat_content.focus()
                    } catch (error) {
                        this.showError(error.error)
                        this.chatImagesUploadLoading.shift()
                        this.$refs.uploadChatImages.reset()
                    }	
                }
			}
            this.$refs.uploadChatImages.reset()
		},
        async removeChatImage(imageId) {
            try {
                await deleteChatItem({
                    id: imageId
                });
                this.chatImagesUpload = this.chatImagesUpload.filter(image => image.id !== imageId);
                this.checkType()
                this.$refs.chat_content.focus()
                this.$refs.uploadChatImages.reset()
            } catch (error) {
                this.showError(error.error)
            }
        },
        uploadChatFiles(event){
			this.startUploadChatFiles(event.target.files)
		},
        async startUploadChatFiles(uploadedFiles, clipboard){
            if (typeof clipboard === 'undefined') {
                clipboard = false
            }
			for( var i = 0; i < uploadedFiles.length; i++ ){
                if (clipboard) {
					// Skip content if not image
					if (uploadedFiles[i].type.indexOf("image") == -1) continue;
				}
				var checkUpload = true
				if (! clipboard) {
					checkUpload = this.checkUploadedData(uploadedFiles[i], 'chat')
				}
				if(checkUpload){
					let formData = new FormData()
                    var blob = uploadedFiles[i]
                    if (clipboard) {
                        blob = uploadedFiles[i].getAsFile();
                    }
                    formData.append('file', blob)
                    try {
                        const response = await uploadChatFiles(formData);
                        this.chatFilesUpload.push(response);
                        this.checkType()
                        this.$refs.chat_content.focus()
                    } catch (error) {
                        this.showError(error.error)
                        this.$refs.uploadChatFiles.reset()
                    }	
                }
			}
            this.$refs.uploadChatFiles.reset()
		},
        async removeChatFile(fileId) {
            try {
                await deleteChatItem({
                    id: fileId
                });
                this.chatFilesUpload = this.chatFilesUpload.filter(file => file.id !== fileId);
                this.checkType()
                this.$refs.chat_content.focus()
                this.$refs.uploadChatFiles.reset()
            } catch (error) {
                this.showError(error.error)
            }
        },
        addEmoji(emoji){
            this.$refs.chat_content.addContent(emoji)	
		},
        uploadChatGif(file) {
            this.startUploadChatImages([file]);
        },
        inputChange(content){
            this.chat.content = content;

            if (this.config.broadcastEnable) {
                let channel = window.Echo.private(Constant.CHANNEL_ROOM + this.room_id);
                let self = this
                if (! this.typingSend) {
                    if (self.chat.content != '') {
                        channel.whisper('typing', {
                            user_id: self.user.id
                        });
                        this.typingSend = true
                    }
                    setTimeout(function() {
                        self.typingSend = false
                    }, 2000);
                }
            }
        },
        async sendMessages() {
            this.$refs.emojiPicker.closeEmoji()
            try {
                if (this.loadingSendMessage)  {
                    return;
                }

                //check chat message empty
                let check = false;
                if (this.chat.content != '' || this.chatImagesUpload.length || this.chatFilesUpload.length) {
                    check = true;
                }

                if (! check) {                         
                    return;
                }
                this.loadingSendMessage = true

                let idItems = null;
                if (this.chatImagesUpload.length) {
                    idItems = this.chatImagesUpload.map(image => image.id);
                } else if(this.chatFilesUpload.length) {
                    idItems = this.chatFilesUpload.map(file => file.id);
                }

                let data = {
                    room_id: this.room_id,
                    type: this.chat.type,
                    content: this.chat.content,
                    items: idItems,
                    parent_message_id: this.replyMessage ? this.replyMessage.id : 0
                }

                await this.sendMessage(data)

                this.chat.type = 'text'
                this.chatImagesUpload = []
                this.chatFilesUpload = []
                this.chat.content = null
                this.$refs.chat_content.setContent('')
                this.$refs.uploadChatImages.reset()
                this.$refs.uploadChatFiles.reset()
                if (! this.roomInfo.is_group && (this.roomInfo.user_status === 'created' || this.roomInfo.user_status === 'cancelled')) {
                    this.setRoomUserStatus('sent');
                }  
                this.loadingSendMessage = false
            } catch (error) {
                this.loadingSendMessage = false
                this.showError(error.error)
            }
            this.scrollToEnd()
            this.cancelReplyMessage()
        },
        scrollToEnd(){
            setTimeout(() => {
                this.$nextTick(() => {
                    if (this.$refs.messages_list) {
                        this.$refs.messages_list.scrollTop = this.$refs.messages_list.scrollHeight
                    }
                })
            }, 100);
        },
        resetScroll(){
            var parentEl = this.$refs.messages_list
            var prevHeight = this.$refs.messages_list.scrollHeight
            this.$nextTick(() => {
                if (this.$refs.messages_list) {
                    this.$refs.messages_list.scrollTop = parentEl.scrollHeight - prevHeight
                }
            })
        },
        
        onScroll(){
            if(isVisible(this.$refs.bottom_messages_list, this.$refs.messages_list)){
                this.showScrollToNewMessageBtn = false
            }else{
                this.showScrollToNewMessageBtn = true
            }
        },
        cancelReplyMessage(){
            this.setReplyMessage(null)
        },
        handleClickSendFund(selectedUser){
            if (this.user) {
                let permission = 'wallet.send_fund'
                if(this.checkPermission(permission)){
                    this.$dialog.open(SendFundChatModal, {
                        data: {
                            selectedUser: [selectedUser],
                            messageData: {
                                room_id: this.room_id,
                                type: 'send_fund',
                                content: this.chat.content,
                                items: [],
                                parent_message_id: 0
                            }
                        },
                        props: {
                            header: this.$t('Send funds to') + ' ' + selectedUser.name,
                            class: 'send-fund-modal',
                            modal: true,
                            dismissableMask: true,
                            draggable: false
                        }
                    })
                }
            }
        }
    }
}
</script>