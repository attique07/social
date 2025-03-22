<template>
    <Loading v-if="loading_notifications_list"/>
    <div v-else>
        <div v-if="notificationsList.length > 0">
            <NotificationItem v-for="(notificationItem, key) in notificationsList" :key="key" :notificationItem="notificationItem"/>
            <router-link v-if="viewAllBtn" :to="{ name: 'notifications' }" class="notification-view-all block">{{$t('View all notifications')}}</router-link>                      
            <div v-if="loadmore_status && !viewAllBtn" class="text-center mt-3">
                <BaseButton @click="getNotificationsList(page)">{{$t('View more')}}</BaseButton>
            </div>
        </div>
        <div v-else class="text-center">
            {{$t('No Notifications')}}
        </div>
    </div>
</template>
<script>
import { mapActions } from 'pinia';
import { getUserNotifications } from '@/api/notification';
import NotificationItem from "@/components/notifications/NotificationItem.vue";
import Loading from '@/components/utilities/Loading.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import { useUtilitiesStore } from '../../store/utilities';

export default {
    components: { NotificationItem, Loading, BaseButton },
    props: {
        viewAllBtn: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            notificationsList: [],
            loadmore_status: false,
            loading_notifications_list: true,
            error: null,
            page: 1
        };
    },
    created() {
        this.getNotificationsList(this.page);
    },
    methods: {
        ...mapActions(useUtilitiesStore, ['setNotificationCount']),
        async getNotificationsList(page) {
            try {
                const response = await getUserNotifications(page);
                if(page === 1){
                    this.notificationsList = response.items
                }else{
                    this.notificationsList = window._.concat(this.notificationsList, response.items);
                }
                if(response.has_next_page){
                    this.loadmore_status = true
                    this.page++;
                }else{
                    this.loadmore_status = false
                }
                this.loading_notifications_list = false

                this.setNotificationCount(0);
            }
            catch (error) {
                this.loading_notifications_list = false
                this.showError(error.error)
            }
        },
    }
}
</script>