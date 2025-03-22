<template>
    <Error v-if="error">{{error}}</Error>
    <div class="px-base-2 pt-6 pb-4 text-center">
        <p class="text-base-lg font-bold mb-base-2">{{$t('What’s the Status of Your Sent Invites')}}</p>
        <p class="mb-base-2">{{$t('Here’s a list of the people that have been invited to join')}}</p>
        <div v-if="inviteInfo" class="flex justify-between items-center border border-secondary-box-color mb-base-2 dark:border-white/10">
            <div class="px-2 py-5 flex-1">
                <div class="text-3xl md:text-base-4xl py-3">{{inviteInfo.total_sent}}</div>
                <div class="text-base-xs uppercase">{{$t('Sent')}}</div>
            </div>
            <div class="px-2 py-5 flex-1 border-l border-r border-secondary-box-color dark:border-white/10">
                <div class="text-3xl md:text-base-4xl py-3">{{inviteInfo.total_register}}</div>
                <div class="text-base-xs uppercase">{{$t('Joined')}}</div>
            </div>
            <div class="px-2 py-5 flex-1">            
                <div class="text-3xl md:text-base-4xl py-3">{{inviteInfo.percent_register}}%</div>
                <div class="text-base-xs uppercase">{{$t('Conversion')}}</div>
            </div>
        </div>
        <Loading v-else/>
        <form class="flex gap-base-2 mb-base-2" @submit.prevent="searchInvitesList">
            <BaseInputText v-model="searchKeyword" :placeholder="$t('Keyword')" class="max-w-[240px]"/>
            <BaseSelect class="max-w-[240px]" v-model="selectedStatus" :options="statusList" optionLabel="name" optionValue="value" :placeholder="$t('Status')"/>
            <BaseButton>{{$t('Search')}}</BaseButton>
        </form>
        <div class="relative overflow-x-auto border border-b-0 border-table-border-color md:rounded-md dark:border-white/10">
            <table class="w-full text-sm whitespace-nowrap text-center">
                <thead class="text-xs uppercase bg-table-header-color dark:bg-dark-web-wash">
                    <tr>
                        <th scope="col" class="p-3 text-start">{{$t('Email')}}</th>
                        <th scope="col" class="p-3">{{$t('Last Updated')}}</th>
                        <th scope="col" class="p-3">{{$t('Status')}}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loadingInvites">
                        <td colspan="3"><Loading class="mt-base-2"/></td>
                    </tr>
                    <template v-else>
                        <template v-if="invitesList.length">                 
                            <tr v-for="(inviteItem, index) in invitesList" :key="index" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b border-table-border-color dark:border-gray-700">
                                <td class="p-3 text-start">{{ inviteItem.email }}</td>
                                <td class="p-3">{{ inviteItem.sent_at }}</td>
                                <td class="p-3">{{ labelText(inviteItem.status) }}</td>
                            </tr>
                        </template>
                        <tr v-else class="border-b border-table-border-color dark:border-gray-700">
                            <td colspan="3" class="p-3">{{$t('No Invites Sent')}}</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div v-if="loadmoreInvites" class="text-center my-base-2">
            <BaseButton @click="getInvitesList(selectedStatus, page, searchKeyword)">{{$t('View more')}}</BaseButton>
        </div>
    </div> 
</template>

<script>
import { getInviteInfo, getInvitesList } from '@/api/invite';
import Error from '@/components/utilities/Error.vue';
import Loading from '@/components/utilities/Loading.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import BaseInputText from '@/components/inputs/BaseInputText.vue';
import BaseSelect from '@/components/inputs/BaseSelect.vue';

export default {
    components: { Error, Loading, BaseButton, BaseInputText, BaseSelect },
    data() {
        return {
            error: null,
            inviteInfo: null,
            invitesList: null,
            loadingInvites: true,
            loadmoreInvites: false,
            page: 1,
            searchKeyword: '',
            selectedStatus: 'all',
            statusList: [
                { name: this.$t('All'), value: 'all'},
                { name: this.$t('Joined'), value: 'joined'},
                { name: this.$t('Sent'), value: 'sent'}
            ]
        };
    },
    mounted(){
        this.getInviteInfo()
        this.getInvitesList(this.selectedStatus, this.page, this.searchKeyword)
    },
    methods: {
        labelText(name){
            switch(name){
                case 'sent':
                    return this.$t('Sent')
                case 'joined':
                    return this.$t('Joined')
            }
        },
        async getInviteInfo() {
            try {
                const response = await getInviteInfo();
                this.inviteInfo = response
            }
            catch (error) {
                this.error = error;
            }
        },
        async getInvitesList(status, page, keyword) {
            try {
                const response = await getInvitesList(status, page, keyword)
                if(page === 1){
                    this.invitesList = response.items
                }else{
                    this.invitesList = window._.concat(this.invitesList, response.items);
                }
                if(response.has_next_page){
                    this.loadmoreInvites = true
                    this.page++;
                }else{
                    this.loadmoreInvites = false
                }
                this.loadingInvites = false
            }
            catch (error) {
                this.error = error
                this.loadingInvites = false
            }
        },
        searchInvitesList()
        {
            this.page = 1;
            this.getInvitesList(this.selectedStatus, this.page, this.searchKeyword)
        }
    }
}
</script>

<style>

</style>