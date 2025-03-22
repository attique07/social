<template>
    <div>
        <div class="mb-base-2" v-if="! config.membership.enable">{{$t('You do not have permission to do it.')}}</div>
        <div class="mb-base-2" v-if="config.membership.enable">{{message}}</div>
        <div class="flex gap-base-2 justify-end">
            <BaseButton v-if="config.membership.enable" :to="{name: 'membership', force: true}">{{$t('Upgrade Now')}}</BaseButton>
            <BaseButton v-if="! config.membership.enable" :to="{name: 'contact', force: true}">{{$t('Contact Us')}}</BaseButton>
        </div>
    </div>
</template>
<script>
import { mapState } from 'pinia'
import BaseButton from '@/components/inputs/BaseButton.vue';
import { useAppStore } from '@/store/app'
export default {
    components: { BaseButton },
    inject: ['dialogRef'],
    data() {
		return {
			permission: this.dialogRef.data.permission,
            message: this.dialogRef.data.message,
		}
	},
    mounted() {
        if (! this.message) {
            this.message = this.config.permissionMessages[this.permission]
        }
    },
    computed: {
		...mapState(useAppStore, ['config']),
    },
    methods: {
        clickClose() {
            this.dialogRef.close()
        }
    }
}
</script>