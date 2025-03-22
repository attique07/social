<template>
    <ProgressBar v-if="value" :value="value" :mode="mode" :class="value === 100 ? 'done' : 'progressing'"/>
</template>

<script>
import ProgressBar from 'primevue/progressbar';

export default {
    components: { ProgressBar },
    props: {
        value: {
            type: Number,
            default: 0
        },
        mode: {
            type: String,
            default: 'determinate'
        }
    }
}
</script>