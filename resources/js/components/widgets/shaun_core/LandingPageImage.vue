<template>
    <div class="widget-box mb-base-2">
        <div v-if="data.enable_title" class="widget-box-header flex flex-wrap items-center justify-between gap-2 mb-5">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ data.title }}</h3>
        </div>
        <img :src="data.data.photo_url" class="w-full" />
    </div>
</template>

<script>
export default {
    props: ['data', 'params', 'position']
}
</script>