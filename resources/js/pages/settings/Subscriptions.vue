<template>
	<BaseSelect class="max-w-[240px] mb-base-2" @change="filterSubscriptionsList()" v-model="selectedStatus" :options="statusList" optionLabel="name" optionValue="value" :placeholder="$t('Status')"/>
	<div class="relative overflow-x-auto border border-b-0 border-table-border-color md:rounded-md dark:border-white/10">
		<table class="w-full text-sm whitespace-nowrap text-start">
			<thead class="text-xs uppercase bg-table-header-color dark:bg-dark-web-wash">
				<tr>
					<th scope="col" class="p-3">{{$t('Subscription')}}</th>
					<th scope="col" class="p-3">{{$t('Status')}}</th>
					<th scope="col" class="p-3">{{$t('Next payment')}}</th>
					<th scope="col" class="p-3">{{$t('Action')}}</th>
				</tr>
			</thead>
			<tbody>
				<tr v-if="loadingSubscription">
					<td colspan="5"><Loading class="mt-base-2"/></td>
				</tr> 
				<template v-else>
					<template v-if="subscriptionsList.length">                 
						<tr v-for="subscription in subscriptionsList" :key="subscription.id" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b border-table-border-color dark:border-gray-700">
							<td class="p-3">{{ subscription.name }}</td>
							<td class="p-3">{{ subscription.status_text }}</td>
							<td class="p-3">{{ subscription.next_payment }}</td>
							<td class="p-3">
								<router-link :to="{name: 'setting_subscription_detail', params: {id: subscription.id}}">{{ $t('View') }}</router-link>
							</td>
						</tr>
					</template>
					<tr v-else class="border-b border-table-border-color dark:border-gray-700">
						<td colspan="5" class="text-center p-3">{{$t('No Subscriptions')}}</td>
					</tr>
				</template>
			</tbody>
		</table>
	</div>
	<div v-if="loadmoreSubscription" class="text-center my-base-2">
		<BaseButton @click="getSubscriptionsList(selectedStatus, currentPage)">{{$t('View more')}}</BaseButton>
	</div>
</template>

<script>
import { getUserSubscription } from '@/api/subscription'
import Loading from '@/components/utilities/Loading.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import BaseSelect from '@/components/inputs/BaseSelect.vue';

export default {
	components: { Loading, BaseButton, BaseSelect },
	data(){
		return{
			loadingSubscription: true,
			loadmoreSubscription: false,
			subscriptionsList: [],
			currentPage: 1,
			selectedStatus: 'all',
            statusList: [
				{ name: this.$t('All'), value: 'all'},
				{ name: this.$t('Active'), value: 'active'},
				{ name: this.$t('Cancel'), value: 'cancel'},
				{ name: this.$t('Stop'), value: 'stop'}
			]
		}
	},
	mounted(){
		this.getSubscriptionsList(this.selectedStatus, this.currentPage)
	},
	methods:{
		async getSubscriptionsList(status, page){
			try {
				const response = await getUserSubscription(status, page)
				if(page === 1){
                    this.subscriptionsList = response.items
                }else{
                    this.subscriptionsList = window._.concat(this.subscriptionsList, response.items);
                }
                if(response.has_next_page){
                    this.loadmoreSubscription = true
                    this.currentPage++;
                }else{
                    this.loadmoreSubscription = false
                }
                this.loadingSubscription = false
			} catch (error) {
				this.showError(error.error)
			}
		},
		filterSubscriptionsList(){
            this.currentPage = 1;
            this.getSubscriptionsList(this.selectedStatus, this.currentPage)
        }
	}
}
</script>