<template>
    <div class="inline-block cursor-pointer" @click="handleOpenTooltip">
        <BaseIcon name="info" size="20" />
    </div>
    <OverlayPanel ref="tooltip_content" class="tooltip_content_op max-w-sm mt-base-1 text-base-xs">
        {{ content }}
    </OverlayPanel>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue'
import OverlayPanel from 'primevue/overlaypanel';

export default {
    props: {
        content: {
			type: String,
			default: null
		},
    },
    components: { BaseIcon, OverlayPanel },
    methods:{
        handleOpenTooltip(e){
            this.$refs.tooltip_content.toggle(e);
        }
    }
}
</script>