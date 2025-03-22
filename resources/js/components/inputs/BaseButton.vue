<template>
	<a v-if="href" :href="href" :class="buttonClass" :disabled="disabled">
		<span :class="{'opacity-0': loading}"><slot></slot></span>		
		<span v-if="loading" class="absolute z-1 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
			<BaseIcon name="spinner" class="spinner" size="18"/>
		</span>
	</a>
	<router-link v-else-if="to" :to="to" :class="buttonClass" :disabled="disabled">
		<span :class="{'opacity-0': loading}"><slot></slot></span>		
		<span v-if="loading" class="absolute z-1 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
			<BaseIcon name="spinner" class="spinner" size="18"/>
		</span>
	</router-link>
	<button v-else :class="buttonClass" :disabled="disabled">
		<span :class="{'opacity-0': loading}"><slot></slot></span>		
		<span v-if="loading" class="absolute z-1 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
			<BaseIcon name="spinner" class="spinner" size="18"/>
		</span>
	</button>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
	components: {
		BaseIcon
	},
	props: {
		type: {
			type: String, // primary, outlined, secondary, secondary-outlined, danger, danger-outlined, transparent, success, warning, info
			default: 'primary'
		},
		size: {
			type: String, // xs, sm, md, lg
			default: 'md'
		},
		loading: {
			type: Boolean,
			default: false
		},
		href: {
			type: String,
			default: ''
		},
		to: {
			type: Object,
			default: null
		},
		disabled: {
			type: Boolean,
			default: false
		}
	},
	computed: {
        buttonClass() {
            return `btn btn-${this.type} btn-size-${this.size} ${this.disabled ? 'opacity-50' : 'hover:bg-hover'}`
        }
    }
}
</script>
