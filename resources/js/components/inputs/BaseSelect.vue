<template>
   <Dropdown class="bg-input-background-color text-input-text-color border border-input-border-color w-full dark:bg-slate-800 dark:text-white dark:border-white/10 dark:placeholder:text-slate-300" :emptyMessage="$t('No results found')"/>
</template>

<script>
import Dropdown from 'primevue/dropdown'

export default {
    components: { Dropdown }
}
</script>