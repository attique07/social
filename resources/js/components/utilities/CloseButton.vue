<template>
	<Teleport to="body">
		<button ref="btnClose" class="fullscreen-close-btn fixed top-0 end-0 items-center justify-center m-2 bg-transparent w-16 h-16 rounded-md text-white hidden lg:flex" :style="{zIndex: baseZIndex}" @click="handleClickClose">
            <BaseIcon name="big_close" size="44"/>
        </button>
    </Teleport>
</template>
<script>
import { mapActions, mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { BaseIcon },
    mounted(){
        this.setBaseZIndex()
    },
    computed:{
        ...mapState(useAppStore, ['baseZIndex'])
    },
    methods:{
        ...mapActions(useAppStore, ['setBaseZIndex']),
        handleClickClose(){
            this.$emit('click')
        }
    },
    emits: ['click']
}
</script>