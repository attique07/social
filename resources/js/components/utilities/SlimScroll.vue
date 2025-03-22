<template>
    <div>
        <div class="relative">
            <div v-show="user.rtl ? !isNextButtonDisabled : !isPrevButtonDisabled" @click="onPrevButtonClick">
                <slot name="prev">
                    <button class="absolute top-1/2 -translate-y-1/2 left-base-2 flex items-center justify-center w-10 h-10 bg-white shadow-md rounded-full text-main-color dark:bg-slate-800 dark:text-white dark:shadow-slate-500 z-10"><BaseIcon name="caret_left"/></button>
                </slot>
            </div>
            <div class="slimscroll-content flex" :class="[`gap-${gap}`, `p-${padding}`]" ref="slimScrollContent" @scroll="onScroll">
                <slot></slot>
            </div>
            <div v-show="user.rtl ? !isPrevButtonDisabled : !isNextButtonDisabled" @click="onNextButtonClick">
                <slot name="next">
                    <button class="absolute top-1/2 -translate-y-1/2 right-base-2 flex items-center justify-center w-10 h-10 bg-white shadow-md rounded-full text-main-color dark:bg-slate-800 dark:text-white dark:shadow-slate-500 z-10"><BaseIcon name="caret_right"/></button>
                </slot>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import { useAuthStore } from '../../store/auth'

export default {
    components: { BaseIcon },
    props: {
        gap: {
			type: Number,
			default: 1
		},
        padding: {
			type: Number,
			default: 0
		}
    },
	data(){
		return {
			isPrevButtonDisabled: true,
            isNextButtonDisabled: false
		}
	},
    mounted() {
        this.updateButtonState()
    },
    computed:{
        ...mapState(useAuthStore, ['user'])
    },
    methods: {
        onScroll(event) {
            this.updateButtonState();
            event.preventDefault();
        },
		updateButtonState() {
            const content = this.$refs.slimScrollContent;
            let { scrollLeft, scrollWidth } = content;
            const width = Math.ceil(content.getBoundingClientRect().width);

            this.isPrevButtonDisabled = scrollLeft === 0;
            this.isNextButtonDisabled = Math.abs(parseInt(scrollLeft)) >= scrollWidth - width;
        },
		onPrevButtonClick() {
            const content = this.$refs.slimScrollContent;
            const width = Math.ceil(content.getBoundingClientRect().width);
            const pos = content.scrollLeft - width;

            content.scrollLeft = pos;
        },
        onNextButtonClick() {
            const content = this.$refs.slimScrollContent;
            const width = Math.ceil(content.getBoundingClientRect().width);
            const pos = content.scrollLeft + width;
            const lastPos = content.scrollWidth - width;

            content.scrollLeft = pos >= lastPos ? lastPos : pos;
        }
    }
}
</script>