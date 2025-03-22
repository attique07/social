<template>
    <ul>
        <MainMenuItem v-for="(menuItem, index) in menus.main" :key="index" :item="menuItem"/>
    </ul>
</template>

<script>
import { mapState } from 'pinia';
import MainMenuItem from './MainMenuItem.vue';
import { useAppStore } from '../../store/app';
export default {
    components: { MainMenuItem },
    computed: {
        ...mapState(useAppStore, ['menus'])
    }
}
</script>