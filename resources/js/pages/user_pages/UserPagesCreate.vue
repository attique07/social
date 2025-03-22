<template>
    <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ $t('Create New Page') }}</h3>
        </div>
        <form @submit.prevent="createNewPage">
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Page Name') }}</label>
                <BaseInputText v-model="name" :placeholder="$t('Please enter page name')" :error="error.name"/>
            </div>
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Page Alias') }}</label>
                <BaseInputText v-model="user_name" :placeholder="$t('Please enter page alias')" :error="error.user_name"/>
            </div>
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Description') }}</label>
                <BaseTextarea v-model="description" :placeholder="$t('Enter page description')" :error="error.description" />
            </div>
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Categories') }}</label>
                <BaseInputText :placeholder="$t('Select categories')" @click="handleClickCategoriesInput()" :error="error.categories"/>
                <div v-if="categories.length > 0" class="flex flex-wrap gap-2 mt-base-2">
                    <div v-for="category in categories" :key="category.id" class="text-sub-color inline-flex items-center rounded-base px-base-2 py-2 border border-secondary-box-color dark:text-slate-400 dark:border-white/30">
                        <span class="font-bold text-xs me-2">{{category.name}}</span>
                        <button @click="deleteSelectedCategories(category)">
                            <BaseIcon name="close" size="16" />
                        </button>
                    </div>  
                </div>
            </div>
            <div class="mb-2">
                <label class="block mb-1">{{ $t('Hashtags') }}</label>
                <AutoComplete v-model="hashtags" multiple optionLabel="name" :suggestions="hashtagsList" @complete="suggestHashtagsList" :emptySearchMessage="$t('No hashtags found')" class="w-full">
                    <template #option="slotProps">
                        <div>{{ slotProps.option.name }}</div>
                    </template>
                </AutoComplete>
            </div>
            <BaseButton class="w-full" :loading="loadingCreate">{{ $t('Continue') }}</BaseButton>
        </form>
    </div>
</template>

<script>
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import SelectCategoriesModal from '@/components/modals/SelectCategoriesModal.vue'
import { storeUserPage } from '@/api/page'
import { getMentionHashtagsList } from '@/api/hashtag'
import AutoComplete from 'primevue/autocomplete';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import { useAuthStore } from '@/store/auth'
import { mapState, mapActions} from 'pinia'
import { useAppStore } from '@/store/app'

export default {
    components: { BaseButton, BaseInputText, BaseTextarea, AutoComplete, BaseIcon },
    data(){
        return{
            loadingCreate: false,
            name: null,
            user_name: null,
            description: null,
            categories: [],
            hashtags: [],
            hashtagsList: [],
            error: {
				name: null,
				user_name: null,
                categories: null,
                description: null
            }
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user'])
    },
    mounted() {
        if (this.user.is_page) {
            this.setErrorLayout(true)
        }
    },
    methods: {
        ...mapActions(useAppStore, ['setErrorLayout']),
        async createNewPage(){
            this.loadingCreate = true
            try {
                const response = await storeUserPage({
                    name: this.name,
                    user_name: this.user_name,
                    description: this.description,
                    categories: this.categories.map(category => category.id),
                    hashtags: this.hashtags.map(hashtag => hashtag.name)
                })
                this.loadingCreate = false
                this.$router.push({ name: 'profile',  params: {user_name: response.user_name} })
                this.showSuccess(this.$t('Your page has been created.'))
                this.resetErrors(this.error)
            } catch (error) {
                this.loadingCreate = false
                this.handleApiErrors(this.error, error)
            }
        },
        handleClickCategoriesInput(){
            this.$dialog.open(SelectCategoriesModal, {
                data: {
                    selectedCategoryIds: this.categories.map(category => category.id)
                },
                props: {
                    header: this.$t('Select Categories'),
                    class: 'select-categories-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: (options) => {
                    if(options.data){
                        this.categories = options.data.selectedCategoriesList
                    }
                }
            })
        },
        async suggestHashtagsList(event) {
            try {
				const response = await getMentionHashtagsList(event.query, 1);
                this.hashtagsList = response;
			} catch (error) {
                this.hashtagsList = [];
			}  
        },
        deleteSelectedCategories(categoryData){
            this.categories = this.categories.filter(category => category.id !== categoryData.id)
            var childrens = categoryData.childs;
            if(childrens && childrens.length){
                this.categories = window._.differenceBy(this.categories, childrens, 'id')
            }
		}
    }
}
</script>