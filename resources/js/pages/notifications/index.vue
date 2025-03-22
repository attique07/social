<template>
	<div class="main-content-section bg-white p-5 rounded-none mb-base-2 md:rounded-base-lg dark:bg-slate-800">	
		<div class="flex flex-wrap items-center justify-between gap-1 mb-5">
			<h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{$t('Notifications')}}</h3>				
		</div>
		<NotificationsList />
	</div>
</template>
<script>
import NotificationsList from '@/components/notifications/NotificationsList.vue';

export default {
    components: { NotificationsList }
}
</script>