<template>
    <Component v-if="chatTypeComponent" :is="chatTypeComponent" :message="message" :owner="owner" :room_info="room_info"/>
</template>

<script>
import ChatPhotos from './types/ChatPhotos.vue'
import ChatShareLink from './types/ChatShareLink.vue'
import ChatText from './types/ChatText.vue'
import ChatStories from './types/ChatStories.vue'
import ChatFile from './types/ChatFile.vue'
import ChatSendFund from './types/ChatSendFund.vue'

export default {
    props: ['message', 'owner', 'room_info'],
    computed: {
        chatTypeComponent(){
            switch(this.message.type) {
                case 'photo':
                    return ChatPhotos
                case 'link':
                    return ChatShareLink
                case 'story_reply':
                    return ChatStories
                case 'file':
                    return ChatFile
                case 'send_fund':
                    return ChatSendFund
                default:
                    return ChatText;
            }
        }
    }
}
</script>