<template>
    <div class="main-content-section bg-white border border-white rounded-none md:rounded-base-lg dark:bg-slate-800 dark:border-slate-800">
        <div v-if="isMobile">
            <div v-if="roomId" class="fixed inset-0 z-[999] flex flex-col h-[100svh] bg-white dark:bg-slate-800">
                <ChatRoomHeader :room-info="roomInfo"/>
                <ChatRoomDetail :key="roomId" :room_id="roomId" />
            </div>
            <div v-else class="chat-rooms-list p-base-2 h-full w-full dark:border-gray-700 scrollbar-thin">
                <div class="flex items-center mb-base-2">
                    <router-link :to="{ name: 'chat', force: true}" v-if="this.$route.name === 'chat_requests'" class="text-main-color dark:text-white">
                        <BaseIcon name="arrow_left" class="align-middle" />						
                    </router-link>
                    <h1 class="page-title flex-1">{{this.$route.name === 'chat_requests' ? $t('Requests') : $t('Chat')}}</h1>
                    <router-link v-if="this.$route.name !== 'chat_requests' && requestsCount > 0" class="font-bold" :to="{name: 'chat_requests'}">{{ $filters.numberShortener(requestsCount, $t('[number] request'), $t('[number] requests')) }}</router-link>
                </div>
                <ChatRoomsList :type="this.$route.name" :room_id="roomId" @updateRoomId="updateRoomId"/>
            </div>
        </div>
        <div v-else class="flex h-chat">
            <div class="chat-rooms-list flex flex-col w-80 px-base-2 pb-base-2 border-e border-divider h-full dark:border-gray-700 relative">
                <div class="flex items-center gap-base-2 py-base-2 main-content-section bg-white dark:bg-slate-800">
                    <router-link :to="{ name: 'chat', force: true}" v-if="this.$route.name === 'chat_requests'" class="text-main-color dark:text-white">
                        <BaseIcon name="arrow_left" class="align-middle" />					
                    </router-link>
                    <h1 class="page-title flex-1">{{this.$route.name === 'chat_requests' ? $t('Requests') : $t('Chat')}}</h1>
                    <router-link v-if="this.$route.name !== 'chat_requests' && requestsCount > 0" class="font-bold" :to="{name: 'chat_requests'}">{{ $filters.numberShortener(requestsCount, $t('[number] request'), $t('[number] requests')) }}</router-link>
                </div>
                <ChatRoomsList :type="this.$route.name" :room_id="roomId" @updateRoomId="updateRoomId"/>
            </div>
            <div class="flex flex-col flex-[0_0_calc(100%-20rem)] max-w-[calc(100%-20rem)] relative">
                <template v-if="roomId">
                    <ChatRoomHeader :room-info="roomInfo" class="pt-base-2"/>
                    <ChatRoomDetail :key="roomId" :room_id="roomId" />
                </template>
                <div v-else class="flex items-center justify-center h-full p-5">
                    <div>
                        <div class="flex items-center justify-center w-24 h-24 mx-auto mb-2 rounded-full bg-gray-6 text-main-color dark:bg-slate-600 dark:text-slate-300 dark:border-slate-600">
                            <BaseIcon name="chats" size="32"/>
                        </div>
                        <span class="text-xl font-semibold">{{$t('No messages')}}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { getRequestsCount } from '@/api/chat'
import ChatRoomsList from './ChatRoomsList.vue';
import ChatRoomDetail from './ChatRoomDetail.vue';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import { changeUrl } from '@/utility';
import { useAppStore } from '@/store/app';
import { useChatStore } from '@/store/chat';
import ChatRoomHeader from './ChatRoomHeader.vue'

export default {
    components: { ChatRoomsList, ChatRoomDetail, BaseIcon, ChatRoomHeader },
    props: ['room_id'],
    data(){
        return{
            requestsCount: 0,
            roomId: this.room_id
        }
    },
    mounted(){
        this.setRouterName(this.$route.name)
        if (this.$route.name !== 'chat_requests') {
            this.getRequestsCount()
        }
    },
    watch: {
        '$route'() {
            this.roomId = 0
        },
        removeRoomIdTime() {
            this.roomId = 0
            let roomUrl = this.$router.resolve({
                name: this.$route.name,
                params: {
                    'room_id': ''
                }
            })
            changeUrl(roomUrl.fullPath)
            
        }
    },
    computed:{
        ...mapState(useAppStore, ['isMobile']),
        ...mapState(useChatStore, ['removeRoomIdTime', 'roomInfo'])
    },
    methods:{
        ...mapActions(useChatStore, ['setRouterName']),
        async getRequestsCount(){
            try {
                const response = await getRequestsCount()
                this.requestsCount = response.request_count
            } catch (error) {
                console.log(error)
            }
        },
        updateRoomId(roomId){
            this.roomId = roomId
        }
    }
}
</script>