<template>
    <div>
        <AutoComplete v-model="selectedHashtags" multiple optionLabel="name" :suggestions="hashtagsList" @complete="suggestHashtagsList" @item-select="handleItemSelect" :emptySearchMessage="$t('No hashtags found')" class="w-full mb-base-2">
            <template #option="slotProps">
                <div>{{ slotProps.option.name }}</div>
            </template>
        </AutoComplete>
        <BaseButton @click="saveHashtags(selectedHashtags)" class="w-full">{{ $t('Save') }}</BaseButton>
    </div>
</template>

<script>
import AutoComplete from 'primevue/autocomplete';
import BaseButton from '@/components/inputs/BaseButton.vue'
import { storeHashtagsPage } from '@/api/page'
import { getMentionHashtagsList } from '@/api/hashtag'

export default {
    inject: ['dialogRef'],
    data(){
        return{
            hashtagsList: [],
            selectedHashtags: this.dialogRef.data.selectedHashtags
        }
    },
    components: { AutoComplete, BaseButton },
    methods: {
        async suggestHashtagsList(event) {
            try {
				const response = await getMentionHashtagsList(event.query, 1);
                this.hashtagsList = response;
			} catch (error) {
                this.hashtagsList = [];
			}  
        },
        async saveHashtags(selectedHashtags){
            try {
                await storeHashtagsPage(selectedHashtags.map(hashtag => hashtag.name))
                this.dialogRef.close({hashtags: selectedHashtags})
                this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.showError(error.error)
            }
        },
        handleItemSelect(){
            this.selectedHashtags = window._.uniqBy(this.selectedHashtags, 'name')
        }
    }
}
</script>