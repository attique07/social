<template>
    <Error v-if="error">{{error}}</Error>
    <Loading v-if="loadingStories"/>
    <template v-else>
        <div v-if="myStoriesList.length > 0" class="flex flex-wrap -mx-1 mb-base-2">
            <div v-for="myStoryItem in myStoriesList" :key="myStoryItem.id" class="p-1 w-1/2 sm:w-1/3 md:w-1/4 lg:w-1/5 xl:w-1/6 cursor-pointer" @click="showStoryDetail(myStoryItem.id)">
                <div class="bg-web-wash rounded-base-lg relative pb-story-ratio font-semibold bg-cover bg-center overflow-hidden">
                    <StoryContentPreview :story="myStoryItem" class="text-lg" />
                </div>
            </div>      
        </div>
        <div v-else class="text-center">{{$t('No Stories')}}</div>
    </template>
    <div v-if="loadmoreStatus" class="text-center">
        <BaseButton @click="getMyStoriesList(page)">{{$t('View more')}}</BaseButton>
    </div>
</template>
<script>
import { mapState } from 'pinia'
import { getMyStories } from '@/api/stories'
import { checkPopupBodyClass, changeUrl } from '@/utility/index'
import Error from '@/components/utilities/Error.vue';
import Loading from '@/components/utilities/Loading.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import StoryItemDetailModal from '@/components/stories/StoryItemDetailModal.vue'
import StoryContentPreview from '@/components/stories/StoryContentPreview.vue';
import { useStoriesStore } from '../../store/stories';

export default{
    components: { Error, Loading, BaseButton, StoryContentPreview },
    data(){
        return{
            error: null,
            loadmoreStatus: false,
            loadingStories: true,
            myStoriesList: [],
            page: 1
        }
    },
    mounted() {
        this.getMyStoriesList(this.page);
    },
    computed: {
        ...mapState(useStoriesStore, ['deleteStoryItem']),
    },
    watch: {
        deleteStoryItem(){
            this.myStoriesList = this.myStoriesList.filter(story => story.id !== this.deleteStoryItem.id)  
        }
    },
    methods: {
        async getMyStoriesList(page){
            try {
				const response = await getMyStories(page)
                if(page === 1){
                    this.myStoriesList = response.items
                }else{
                    this.myStoriesList = window._.concat(this.myStoriesList, response.items);
                }
                if(response.has_next_page){
                    this.loadmoreStatus = true
                    this.page++;
                }else{
                    this.loadmoreStatus = false
                }
                this.loadingStories = false
			} catch (error) {
                this.error = error
                this.loadingStories = false
			}
        },
        showStoryDetail(storyItemId){
            let storyUrl = this.$router.resolve({
                name: 'story_view_item',
                params: { 'storyItemId': storyItemId }
            });
            changeUrl(storyUrl.fullPath)
            this.$dialog.open(StoryItemDetailModal, {
                data: {
                    storyItemId: storyItemId
                },
                props:{
                    class: 'p-dialog-story p-dialog-story-detail p-dialog-no-header-title',
                    modal: true,
                    showHeader: false,
                    draggable: false
                },
                onClose: () => {
                    changeUrl(this.$router.currentRoute.value.fullPath)
                    checkPopupBodyClass();
                }
            });
        }
    } 
}
</script>