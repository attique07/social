<template>
    <div class="flex items-center gap-base-2 mb-base-2">
        <label for="public" class="flex-1">
            <div class="flex items-center gap-base-2">
                <BaseIcon name="earth"/>
                <div>
                    <div class="font-bold">{{ $t('Public') }}</div>
                    <div class="text-base-xs">{{ $t('Everyone will see') }}</div>
                </div>
            </div>
        </label>
        <BaseRadio :value="1" v-model="selectedPrivacyValue" :inputId="'public'" name="privacy" @change="handleSelectPrivacy(this.selectedPrivacyType, this.selectedPrivacyValue)" />
    </div>
    <div class="flex items-center gap-base-2 mb-base-2">
        <label for="follower" class="flex-1">
            <div class="flex items-center gap-base-2">
                <BaseIcon name="friend"/>
                <div>
                    <div class="font-bold">{{ $t('Follower') }}</div>
                    <div class="text-base-xs">{{ $t('Your followers will see ') }}</div>
                </div>
            </div>
        </label>
        <BaseRadio :value="2" v-model="selectedPrivacyValue" :inputId="'follower'" name="privacy" @change="handleSelectPrivacy(this.selectedPrivacyType, this.selectedPrivacyValue)" />
    </div>
    <div class="flex items-center gap-base-2">
        <label for="only_me" class="flex-1">
            <div class="flex items-center gap-base-2">
                <BaseIcon name="lock"/>
                <div>
                    <div class="font-bold">{{ $t('Only Me') }}</div>
                </div>
            </div>
        </label>
        <BaseRadio :value="3" v-model="selectedPrivacyValue" :inputId="'only_me'" name="privacy" @change="handleSelectPrivacy(this.selectedPrivacyType, this.selectedPrivacyValue)" />
    </div>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue';
import BaseRadio from '@/components/inputs/BaseRadio.vue'
import { storePrivacyPage } from '@/api/page'

export default {
    components: { BaseIcon, BaseRadio },
    inject: ['dialogRef'],
    data(){
        return{
            selectedPrivacyType: this.dialogRef.data.selectedPrivacyType,
            selectedPrivacyValue: this.dialogRef.data.selectedPrivacyValue,
        }
    },
    methods: {
        async handleSelectPrivacy(type, value){
            try {
                await storePrivacyPage(type, value)
                this.dialogRef.close({type: type, value: value});
                this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>