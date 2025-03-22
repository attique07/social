<template>
     <div v-if="userReviewInfo.can_review" class="main-content-section bg-white p-5 mb-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800">
        <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('Do you recommend') + ' ' + userReviewInfo.name + '?'}}</h3>
        <div class="flex gap-base-2">
            <div class="flex-1">
                <BaseButton type="secondary" class="w-full" @click="handleClickRecommend(true)">{{ $t('Yes') }}</BaseButton>
            </div>
            <div class="flex-1">
                <BaseButton type="secondary" class="w-full" @click="handleClickRecommend(false)">{{ $t('No') }}</BaseButton>
            </div>
        </div>
    </div>
    <div class="flex gap-base-2 justify-between items-center mb-base-2 px-base-2 md:px-0">
        <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ $t('Rating') }} · {{ userReviewInfo.review_score }} {{ $filters.numberShortener(userReviewInfo.review_count, $t('([number] review)'), $t('([number] reviews)')) }}</h3>
    </div>
    <Loading v-if="loadingPostsList"/>
	<div v-else>
		<div v-if="postsList.length > 0" class="pb-5">
			<TransitionGroup name="fade">
				<PostListsItem v-for="post in postsList" :key="post.id" :post="post"></PostListsItem>
			</TransitionGroup>
			<InfiniteLoading @infinite="loadMoreReviews">	
				<template #spinner>
					<Loading />
				</template>
				<template #complete><span></span></template>
			</InfiniteLoading>
		</div>
		<div v-else class="main-content-section bg-white border border-white text-main-color p-5 text-center rounded-none mb-base-2 md:rounded-base-lg dark:bg-slate-800 dark:border-slate-800 dark:text-white">
			<div class="text-base-sm">{{$t('Nothing to see here yet')}}</div>
		</div>
	</div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import BaseButton from '@/components/inputs/BaseButton.vue'
import RecommendModal from '@/components/modals/RecommendModal.vue'
import PostListsItem from '@/components/posts/PostListsItem.vue'
import Loading from '@/components/utilities/Loading.vue'
import InfiniteLoading from 'v3-infinite-loading'
import { useAuthStore } from '@/store/auth'
import { usePostStore } from '@/store/post'

export default {
    components: { BaseButton, PostListsItem, Loading, InfiniteLoading },
    props: ['userInfo'],
    data(){
        return {
            userReviewInfo: this.userInfo,
            currentPage: 1
        }
    },
    computed: {
        ...mapState(useAuthStore, ['user']),
		...mapState(usePostStore, ['postsList', 'loadingPostsList']),
    },
    mounted(){
        this.getReviewsPageList(this.userReviewInfo.id, this.currentPage)
    },
    unmounted(){
		this.unsetPostsList()
	},
    methods: {
        ...mapActions(usePostStore, ['getReviewsPageList', 'unsetPostsList']),
        handleClickRecommend(isRecommend){
            this.$dialog.open(RecommendModal, {
                data: {
                    page: this.userReviewInfo,
                    isRecommend: isRecommend
                },
                props: {
                    header: isRecommend ? this.$t('Recommend') + ' ' + this.userReviewInfo.name  : this.$t('How can') + ' ' + this.userReviewInfo.name + ' ' + this.$t('improve?'),
                    class: 'p-dialog-lg',
                    modal: true,
                    draggable: false
                },
                onClose: async(options) => {
                    if(options.data){
                        this.userReviewInfo.can_review = false
                    }
                }
            })
        },
        loadMoreReviews($state) {
			this.getReviewsPageList(this.userReviewInfo.id, ++this.currentPage).then((response) => {
				if(response.length === 0){
					$state.complete()
				}else{
					$state.loaded()
				}
			})
		}
    },
    emits: ['change_tab', 'update_user_info']
}
</script>