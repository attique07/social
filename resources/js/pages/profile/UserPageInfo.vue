<template>
    <div class="main-content-section bg-white p-5 mb-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800">
        <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('Info') }}</h3>
        <div v-if="description || isOwnerPage" class="mb-base-2">
            <div class="font-bold mb-base-2">{{ $t('Description') }}</div>
            <div class="flex items-start gap-base-2">
                <template v-if="description">
                    <div class="flex-1"><ContentHtml :content="description" /></div>
                    <button v-if="isOwnerPage" class="border border-secondary-box-color rounded-md p-2 dark:border-white/30" @click="handleEditDescription()"><BaseIcon name="pencil" size="16" /></button>
                </template>
                <button v-else class="block text-xs text-primary-color dark:text-dark-primary-color" @click="handleEditDescription()">{{$t('Add Description')}}</button>
            </div>
        </div>
        <div v-if="categories.length || isOwnerPage" class="mb-base-2">
            <div class="font-bold mb-base-2">{{ $t('Categories') }}</div>
            <div v-if="categories.length" class="flex flex-wrap gap-2 mt-base-2">
                <div class="boxes-list flex flex-wrap gap-base-2 flex-1">
                    <router-link :to="{name: 'user_pages', query: {category_id: category.id}}" v-for="category in categories" :key="category.id" class="boxes-list-item text-xs text-sub-color font-bold inline-flex gap-2 items-center rounded-base px-base-2 py-2 border border-secondary-box-color dark:border-white/30 dark:text-slate-400">
                        {{ category.name }}
                    </router-link>  
                </div>
                <button v-if="isOwnerPage" class="border border-secondary-box-color rounded-md p-2 dark:border-white/30 self-start" @click="handleEditCategories()"><BaseIcon name="pencil" size="16" /></button>
            </div>
            <button v-else-if="categories.length === 0 && isOwnerPage" class="block text-xs text-primary-color dark:text-dark-primary-color" @click="handleEditCategories()">{{$t('Add Categories')}}</button>
        </div>
        <div v-if="hashtags.length || isOwnerPage" class="mb-base-2">
            <div class="font-bold mb-base-2">{{ $t('Hashtags') }}</div>
            <div v-if="hashtags.length" class="flex flex-wrap gap-2 mt-base-2">
                <div class="boxes-list flex flex-wrap gap-base-2 flex-1">
                    <router-link :to="{name: 'search', params: {search_type: 'hashtag', type: 'page'}, query: {q: hashtag.name}}" v-for="hashtag in hashtags" :key="hashtag.name" class="boxes-list-item text-xs text-sub-color font-bold inline-flex gap-2 items-center rounded-base px-base-2 py-2 border border-secondary-box-color dark:border-white/30 dark:text-slate-400">
                        <span class="font-bold text-xs">{{ hashtag.name }}</span>
                    </router-link>  
                </div>
                <button v-if="isOwnerPage" class="border border-secondary-box-color rounded-md p-2 dark:border-white/30 self-start" @click="handleEditHashtags()"><BaseIcon name="pencil" size="16" /></button>
            </div>
            <button v-else-if="hashtags.length === 0 && isOwnerPage" class="block text-xs text-primary-color dark:text-dark-primary-color" @click="handleEditHashtags()">{{$t('Add Hashtags')}}</button>
        </div>
        <div v-if="!isEmpty(location) || phoneNumber || email || isOwnerPage" class="mb-base-2">
            <div class="font-bold mb-base-2">{{ $t('Address') }}</div>
            <div v-if="location || isOwnerPage" class="flex items-start gap-base-2 mb-base-2">
                <BaseIcon name="location"/>
                <template v-if="!isEmpty(location)">
                    <div class="flex-1 break-word">{{ location.address_full }}</div>
                    <div v-if="isOwnerPage" class="flex items-center gap-base-2">
                        <button @click="handleEditPrivacy('address', privacy.address)"><BaseIcon :name="privacyIcon(privacy.address)" /></button>
                        <button class="border border-secondary-box-color rounded-md p-2 dark:border-white/30" @click="handleEditLocation()"><BaseIcon name="pencil" size="16" /></button>
                    </div>
                </template>
                <button v-else-if="isEmpty(location) && isOwnerPage" class="block text-xs text-primary-color dark:text-dark-primary-color" @click="handleEditLocation()">{{$t('Add Location')}}</button>
            </div>
            <div v-if="phoneNumber || isOwnerPage" class="flex items-start gap-base-2 mb-base-2">
                <BaseIcon name="phone"/>
                <template v-if="phoneNumber">
                    <div class="flex-1 break-word">{{ phoneNumber }}</div>
                    <div v-if="isOwnerPage" class="flex items-center gap-base-2">
                        <button @click="handleEditPrivacy('phone_number', privacy.phone_number)"><BaseIcon :name="privacyIcon(privacy.phone_number)" /></button>
                        <button class="border border-secondary-box-color rounded-md p-2 dark:border-white/30" @click="handleEditPhoneNumber()"><BaseIcon name="pencil" size="16" /></button>
                    </div>
                </template>
                <button v-else-if="!phoneNumber && isOwnerPage" class="block text-xs text-primary-color dark:text-dark-primary-color" @click="handleEditPhoneNumber()">{{$t('Add Phone')}}</button>
            </div>
            <div v-if="email || isOwnerPage" class="flex items-start gap-base-2 mb-base-2">
                <BaseIcon name="mail"/>
                <template v-if="email">
                    <div class="flex-1 break-word">{{ email }}</div>
                    <div v-if="isOwnerPage" class="flex items-center gap-base-2">
                        <button @click="handleEditPrivacy('email', privacy.email)"><BaseIcon :name="privacyIcon(privacy.email)" /></button>
                        <button class="border border-secondary-box-color rounded-md p-2 dark:border-white/30" @click="handleEditEmail()"><BaseIcon name="pencil" size="16" /></button>
                    </div>
                </template>
                <button v-else-if="!email && isOwnerPage" class="block text-xs text-primary-color dark:text-dark-primary-color" @click="handleEditEmail()">{{$t('Add Email')}}</button>
            </div>
        </div>
        <div v-if="websites[0] || isOwnerPage" class="mb-base-2">
            <div class="flex items-center gap-base-2 mb-base-2">
                <div class="font-bold flex-1">{{ $t('Websites and social links') }}</div>
                <button v-if="websites[0] && isOwnerPage" class="border border-secondary-box-color rounded-md p-2 dark:border-white/30" @click="handleEditWebsites()"><BaseIcon name="pencil" size="16" /></button>
            </div>
            <div v-if="websites[0]">
                <div v-for="(website, index) in websites" :key="index" class="flex gap-base-2 mb-base-2">
                    <BaseIcon name="copy_link"/>
                    <div class="flex-1 break-word">
                        <a :href="websites[index]" target="_blank">{{ websites[index] }}</a>
                    </div>
                </div>
            </div>
            <div v-else class="flex items-center gap-base-2 mb-base-2">
                <BaseIcon name="copy_link"/>
                <button v-if="isOwnerPage" class="block text-xs text-primary-color dark:text-dark-primary-color" @click="handleEditWebsites()">{{$t('Add Link')}}</button>
            </div>
        </div>
        <div v-if="open_hours.type.value != 'none' || !!price.value || isOwnerPage" class="mb-base-2">
            <div class="font-bold mb-base-2">{{ $t('Basic Info') }}</div>
            <div v-if="reviewCount || isOwnerPage" class="flex items-center gap-base-2 mb-base-2">
                <BaseIcon name="star"/>
                <div class="flex-1">
                    {{ $t('Rating') }}: {{ reviewScore }} {{ $filters.numberShortener(reviewCount, $t('([number] review)'), $t('([number] reviews)')) }}
                </div>
                <button v-if="isOwnerPage" class="border border-secondary-box-color rounded-md p-2 dark:border-white/30" @click="handleClickToggleReview(reviewEnable)"><BaseIcon name="pencil" size="16" /></button>
            </div>
            <div v-if="open_hours.type.value != 'none' || isOwnerPage" class="flex items-start gap-base-2 mb-base-2">
                <BaseIcon name="clock"/>
                <template v-if="open_hours">
                    <div v-if="open_hours.type.value === 'hours'" class="flex-1">
                        <div v-for="(open_hour, index) in open_hours.values" :key="open_hour" class="flex flex-col sm:flex-row">
                            <span class="w-32">{{ dayFormat(index) + ': '}}</span>
                            {{ open_hour.start + ' - ' + open_hour.end }}
                        </div>
                    </div>
                    <div v-else class="flex-1">{{ open_hours.type.title }}</div>
                    <button v-if="isOwnerPage" class="border border-secondary-box-color rounded-md p-2 dark:border-white/30" @click="handleEditHours()"><BaseIcon name="pencil" size="16" /></button>
                </template>
                <button v-else class="block text-xs text-primary-color dark:text-dark-primary-color" @click="handleEditHours()">{{$t('Add Open Hours')}}</button>
            </div>
            <div v-if="!!price.value || isOwnerPage" class="flex items-center gap-base-2 mb-base-2">
                <BaseIcon name="coin"/>
                <template v-if="price.value !== 0">
                    <div class="flex-1">{{ $t('Price Range') + ': ' + price.title }} </div>
                    <button v-if="isOwnerPage" class="border border-secondary-box-color rounded-md p-2 dark:border-white/30" @click="handleEditPriceRange()"><BaseIcon name="pencil" size="16" /></button>
                </template>
                <button v-else-if="price.value === 0 && isOwnerPage" class="block text-xs text-primary-color dark:text-dark-primary-color" @click="handleEditPriceRange()">{{$t('Add Price Range')}}</button>
            </div>
        </div>
    </div>
    <div v-if="(isOwnerPage || reviewEnable) && reviewsList.length" class="main-content-section bg-white p-5 mb-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800">
        <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('Reviews') }}</h3>
        <div class="mb-base-2">{{ $t('Rating') }} · {{ reviewScore }} {{ $filters.numberShortener(reviewCount, $t('([number] review)'), $t('([number] reviews)')) }}</div>
        <div class="flex flex-wrap -mx-base-1">
            <router-link v-for="review in reviewsList.slice(0, 2)" :key="review.id" :to="{name: 'post', params: {id: review.id}}" class="p-base-1 text-main-color dark:text-white w-full sm:w-1/2 ">
                <div class="feed-item bg-white border border-divider rounded-base-lg p-4 h-full dark:bg-slate-800 dark:border-white/10">
                    <div class="feed-item-header flex mb-base-2">
                        <div class="feed-item-header-img">
                            <Avatar :user="review.user"/>
                        </div>
                        <div class="feed-item-header-info flex-grow ps-base-2">
                            <div class="feed-item-header-info-title">
                                <div class="whitespace-normal">
                                    <UserName :user="review.user" :truncate="false" />
                                    <template v-if="review.type === 'user_page_review'">                                   
                                        <span>{{ ' ' + (review.items[0].subject?.is_recommend ? $t('recommended') : $t("doesn't recommend")) + ' ' }}</span>
                                        <UserName :user="review.items[0].subject?.page" :truncate="false" />
                                    </template>
                                </div>
                            </div>
                            <div class="feed-item-header-info-date block w-max text-sub-color text-xs dark:text-slate-400">{{review.created_at}}</div>
                        </div>
                    </div>
                    <div class="feed-item-content">
                        <ContentHtml class="activity_feed_content_message mb-base-2" :content="review.content" :mentions="review.mentions" />
                        <div class="activity_feed_content_item">
                            <PostContentType :post="review"/>
                        </div>
                    </div>
                    <div class="flex flex-wrap justify-between gap-base-2">
                        <div class="feed-item-like-text inline-block text-main-color font-semibold dark:text-white">{{ $filters.numberShortener(review.like_count, $t('[number] like'), $t('[number] likes')) }}</div>
                        <div class="feed-item-like-text inline-block text-main-color font-semibold dark:text-white"> {{ $filters.numberShortener(review.comment_count, $t('[number] comment'), $t('[number] comments')) }}</div>
                    </div>
                </div>
            </router-link>
        </div>
        <BaseButton @click="handleViewAllReviews()" type="outlined" class="w-full mt-base-2">{{ $t('View all') }}</BaseButton>
    </div>
    <div v-if="postsList.length" class="main-content-section bg-white p-5 mb-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800">
        <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('Media') }}</h3>
        <div class="grid grid-cols-3 gap-base-2">
            <MasonryPostListsItem v-for="postItem in postsList" :key="postItem.id" :post="postItem"/>
        </div>
        <BaseButton @click="handleViewAllMedias()" type="outlined" class="w-full mt-base-2">{{ $t('View all') }}</BaseButton>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import SelectPriceRangeModal from '@/components/modals/SelectPriceRangeModal.vue';
import EditDescriptionPage from '@/components/modals/EditDescriptionPage.vue';
import EditLocationPage from '@/components/modals/EditLocationPage.vue';
import EditPhoneNumberPage from '@/components/modals/EditPhoneNumberPage.vue';
import EditEmailPage from '@/components/modals/EditEmailPage.vue';
import EditWebsitePage from '@/components/modals/EditWebsitePage.vue';
import SelectOpenHoursModal from '@/components/modals/SelectOpenHoursModal.vue';
import SelectPrivacyModal from '@/components/modals/SelectPrivacyModal.vue';
import SelectCategoriesModal from '@/components/modals/SelectCategoriesModal.vue';
import EditHashtagsModal from '@/components/modals/EditHashtagsModal.vue';
import BaseButton from '@/components/inputs/BaseButton.vue'
import MasonryPostListsItem from '@/components/posts/MasonryPostListsItem.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import PostContentType from '@/components/posts/PostContentType.vue'
import EnableReviewModal from '@/components/modals/EnableReviewModal.vue'
import { getReviewsPage } from '@/api/page'
import { useAuthStore } from '@/store/auth'
import { usePostStore } from '@/store/post'

export default {
    components: { BaseIcon, BaseButton, MasonryPostListsItem, Avatar, UserName, ContentHtml, PostContentType },
    data(){
        return {
            description: this.userInfo.description,
            categories: this.userInfo.categories,
            hashtags: this.userInfo.page_hashtags,
            location: {
                country_id: this.userInfo.country_id,
                state_id: this.userInfo.state_id,
                city_id: this.userInfo.city_id,
                zip_code: this.userInfo.zip_code,
                address: this.userInfo.address,
                address_full: this.userInfo.address_full
            },
            phoneNumber: this.userInfo.phone_number,
            email: this.userInfo.email,
            price: this.userInfo.price,
            websites: this.userInfo.websites.split(" "),
            reviewCount: this.userInfo.review_count,
            reviewEnable: this.userInfo.review_enable,
            reviewScore: this.userInfo.review_score,
            open_hours: this.userInfo.open_hours,
            privacy: this.userInfo.page_privacy_settings,
            currentPage: 1,
            reviewsList: [],
            reviewCurrentPage: 1
        }
    },
    props: ['userInfo'],
    computed: {
        ...mapState(useAuthStore, ['user']),
        ...mapState(usePostStore, ['postsList', 'loadingPostsList']),
        isOwnerPage(){
            return this.userInfo.id === this.user.id
        }
    },
    mounted(){
        this.getProfileMediaPostsList(this.userInfo.id, this.currentPage)
        this.getReviewsPageList(this.userInfo.id, this.reviewCurrentPage)
    },
    unmounted(){
		this.unsetPostsList()
	},
    methods: {
        ...mapActions(usePostStore, ['getProfileMediaPostsList', 'unsetPostsList']),
        privacyIcon(value){
            switch (value) {
                case 1:
                    return 'earth'
                case 2:
                    return 'friend'
                case 3:
                    return 'lock'
                default:
                    break;
            }
        },
        dayFormat(value){
            switch (value) {
                case "mon":
                    return this.$t('Monday')
                case "tue":
                    return this.$t('Tuesday')
                case "wed":
                    return this.$t('Wednesday')
                case "thu":
                    return this.$t('Thursday')
                case "fri":
                    return this.$t('Friday')
                case "sat":
                    return this.$t('Saturday')
                case "sun":
                    return this.$t('Sunday')
            }
        },
        handleEditDescription(){
            this.$dialog.open(EditDescriptionPage, {
                data: {
                    pageDescription: this.description
                },
                props:{
					header: this.$t('Edit Description'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
				onClose: (options) => {
                    if (options.data) {
                        this.description = options.data.description
                    }
                }
            });
        },
        handleEditCategories(){
            this.$dialog.open(SelectCategoriesModal, {
                data: {
                    selectedCategoryIds: this.categories.map(category => category.id),
                    selectedType: 'save'
                },
                props: {
                    header: this.$t('Select Categories'),
                    class: 'select-categories-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: async(options) => {
                    if(options.data){
                        this.categories = options.data.selectedCategoriesList
                    }
                }
            })
        },
        handleEditHashtags(){
            this.$dialog.open(EditHashtagsModal, {
                data: {
                    selectedHashtags: this.hashtags
                },
                props: {
                    header: this.$t('Select Hashtags'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: async(options) => {
                    if(options.data){
                        this.hashtags = options.data.hashtags
                    }
                }
            })
        },
        handleEditLocation(){
            this.$dialog.open(EditLocationPage, {
                data: {
                    pageLocation: this.location
                },
                props:{
					header: this.$t('Edit Location'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
				onClose: (options) => {
                    if (options.data) {
                        this.location = options.data.location
                    }
                }
            });
        },
        handleEditPhoneNumber(){
            this.$dialog.open(EditPhoneNumberPage, {
                data: {
                    pagePhoneNumber: this.phoneNumber
                },
                props:{
					header: this.$t('Edit Phone'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
				onClose: (options) => {
                    if (options.data) {
                        this.phoneNumber = options.data.phoneNumber
                    }
                }
            });
        },
        handleEditEmail(){
            this.$dialog.open(EditEmailPage, {
                data: {
                    pageEmail: this.email
                },
                props:{
					header: this.$t('Edit Email'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
				onClose: (options) => {
                    if (options.data) {
                        this.email = options.data.email
                    }
                }
            });
        },
        handleEditWebsites(){
            this.$dialog.open(EditWebsitePage, {
                data: {
                    pageWebsites: this.websites
                },
                props:{
					header: this.$t('Add Website and Social Links'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
				onClose: (options) => {
                    if (options.data) {
                        this.websites = options.data.websites
                    }
                }
            });
        },
        handleEditPriceRange(){
            this.$dialog.open(SelectPriceRangeModal, {
                data: {
                    selectedRangePrice: this.price
                },
                props:{
					header: this.$t('Price Range'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
				onClose: (options) => {
                    if (options.data) {
                        this.price = options.data.price
                    }
                }
            });
        },
        handleEditHours(){
            this.$dialog.open(SelectOpenHoursModal, {
                data: {
                    selectedOpenHours: this.open_hours
                },
                props:{
					header: this.$t('Open Hours'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
				onClose: (options) => {
                    if (options.data) {
                        this.open_hours = options.data.open_hours
                    }
                }
            });
        },
        handleEditPrivacy(type, value){
            this.$dialog.open(SelectPrivacyModal, {
                data: {
                    selectedPrivacyType: type,
                    selectedPrivacyValue: value
                },
                props:{
					header: this.$t('Select audience'),
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
				onClose: (options) => {
                    if (options.data) {
                        this.privacy[options.data.type] = options.data.value
                    }
                }
            });
        },
        async getReviewsPageList(pageId, page){
            try {
				const response = await getReviewsPage(pageId, page)
                if (page == 1) {
                    this.reviewsList = [];
                }
                this.reviewsList = window._.concat(this.reviewsList, response.items)
			} catch (error) {
				console.log(error)
			}
        },
        async handleClickToggleReview(reviewEnable){
            this.$dialog.open(EnableReviewModal, {
                data: {
                    currentStatus: reviewEnable
                },
                props: {
                    header: this.$t('Allow reviews on your Page'),
                    class: 'p-dialog-md',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: async(options) => {
                    if(options.data){
                        this.reviewEnable = options.data.enable
                        this.$emit('update_user_info', {'review_enable': options.data.enable})
                    }
                }
            })
        },
        handleViewAllReviews(){
            this.$emit('change_tab', 'review')
        },
        handleViewAllMedias(){
            this.$emit('change_tab', 'media')
        },
        isEmpty(obj) {
            for (let key in obj) {
                if (Object.prototype.hasOwnProperty.call(obj, key) && obj[key] !== null && obj[key] !== 0 && obj[key] !== '') {
                    return false;
                }
            }
            return true;
        }
    },
    emits: ['change_tab', 'update_user_info']
}
</script>