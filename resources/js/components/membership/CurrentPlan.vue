<template>
    <div v-if="subscription" class="flex flex-col gap-1 bg-light-blue p-3 rounded-base dark:bg-dark-web-wash">
		<div class="flex gap-4">
			<p class="flex-1 font-semibold">{{ $t('Current Package') }} : {{ subscription.name }}</p>
			<span class="text-white text-base-xs px-2 py-1 rounded-base self-start" :class="subscription.status == 'cancel' ? 'bg-base-red' : 'bg-base-green'">{{ subscription.status_text }}</span>
		</div>
		<p>{{ $t('Created date') + ': ' + subscription.created_at }}</p>
		<template v-if="subscription.status === 'active'">
			<p>{{ subscription.trial }}</p>
			<p>{{ $t('Subscription renewal date') + ': ' + subscription.next_payment }}</p>
			<p>{{ $t('You will be charged') + ': ' + subscription.price }}</p>
			<p>{{ $t('Payment method') + ': ' + subscription.gateway }}</p>
		</template>
		<template v-if="subscription.status === 'cancel'">
			<p>{{ $t('Subscription will stop at') + ': ' + subscription.next_payment }}</p>
		</template>
		<div v-if="['active', 'cancel'].includes(subscription.status)">
			<BaseButton v-if="subscription.can_cancel" @click="doCancelSubscription(subscription.id)" size="sm">{{ $t('Cancel subscription') }}</BaseButton>
			<BaseButton v-if="subscription.can_resume" @click="doResumeSubscription(subscription.id)" size="sm">{{ $t('Resume subscription') }}</BaseButton>
		</div>
	</div>
</template>

<script>
import { cancelSubscription, resumeSubscription } from '@/api/subscription'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    props: ['subscription'],
    components: { BaseButton },
    methods:{
        async doCancelSubscription(id){
			this.$confirm.require({
                message: this.$t('Your subscription will be canceled at the end of your billing period on') + ' ' + this.subscription.next_payment + '. ' + this.$t('You can change your mind anytime before this date.'),
                header: this.$t('Cancel Subscription?'),
                acceptLabel: this.$t('Cancel Subscription'),
                rejectLabel: this.$t('Keep Subscription'),
                accept: async () => {
                    try {
						await cancelSubscription(id)
						this.$emit('update', id)
					} catch (error) {
						this.showError(error.error)
					}
                }
            })
		},
		async doResumeSubscription(id){
			this.$confirm.require({
                message: this.$t('Do you want to resume this subscription?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
						await resumeSubscription(id)
						this.$emit('update', id)
					} catch (error) {
						this.showError(error.error)
					}
                }
            })
		},
		emits: ['update']
    }
}
</script>