<template>
    <div class="text-center">
        <div class="mb-base-2">{{$t('Please login to continue')}}</div>
        <BaseButton @click="clickLogin">{{$t('Login')}}</BaseButton>
    </div>
</template>
<script>
import BaseButton from '@/components/inputs/BaseButton.vue';
export default {
    components: { BaseButton },
    methods: {
        clickLogin() {
            this.$router.push({ name: 'login' })
        }
    }
}
</script>