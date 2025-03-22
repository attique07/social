<template>
    <form @submit.prevent="saveDescription(description)">
        <BaseTextarea v-model="description" autofocus autoResize class="mb-base-2" />
        <BaseButton class="w-full">{{ $t('Save') }}</BaseButton>
    </form>
</template>

<script>
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import { storeDescriptionPage } from '@/api/page'

export default {
    inject: ['dialogRef'],
    data(){
        return{
            description: this.dialogRef.data.pageDescription
        }
    },
    components: { BaseTextarea, BaseButton },
    methods: {
        async saveDescription(description){
            try {
                await storeDescriptionPage(description)
                this.dialogRef.close({description: this.description});
                this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.showError(error.error)
            }
        },
    }
}
</script>