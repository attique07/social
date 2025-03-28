<template>
    <FullColumn :keep-right-column="true">
        <template v-slot:center>
            <div v-if="postInfo" class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
                    <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ $t('Boost Post') }}</h3>
                </div>
                <div class="mb-base-2">
                    <label class="block mb-1">{{ $t('Ads Name') }}</label>
                    <BaseInputText v-model="adsName" :error="error.name" />
                </div>
            </div>
            <div v-if="postInfo" class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
                    <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ $t('Demographics & Targeting features') }}</h3>
                </div>
                <div class="mb-base-2">{{ $t('Target your audience based on hashtags they use, followers of similar accounts.') }}</div>
                <div v-if="adsConfig" class="mb-base-2">
                    <label class="block mb-1">{{ $t('Gender') }}</label>
                    <div class="flex flex-wrap gap-x-5 gap-y-base-2">
                        <div class="flex items-center gap-base-2">
                            <BaseRadio :value="0" v-model="genderId" :inputId="'All'" name="genders" />
                            <label :for="'All'">{{ $t('All') }}</label>
                        </div>
                        <div v-for="(gender, key) in adsConfig.genders" :key="key" class="flex items-center gap-base-2">
                            <BaseRadio :value="key" v-model="genderId" :inputId="gender" name="genders" />
                            <label :for="gender">{{ gender }}</label>
                        </div>
                    </div>
                </div>
                <div v-if="adsConfig" class="mb-base-2">
                    <label class="block mb-1">{{ $t('Age') }}</label>
                    <div class="flex items-center gap-base-2 mb-2">
                        <BaseRadio :value="ageTypeAny" v-model="selectedAge" :inputId="'any'" name="age" />
                        <label for="any">{{ $t('Any') }}</label>
                    </div>
                    <div class="flex items-center gap-base-2 mb-2">
                        <BaseRadio :value="ageTypeRange" v-model="selectedAge" :inputId="'any_range'" name="age" />
                        <label for="any_range">{{ $t('Age Range') }}</label>
                    </div>
                    <div v-if="selectedAge === ageTypeRange" class="flex gap-base-2">
                        <BaseInputText v-model="adsAgeFrom" :placeholder="$t('From')" :error="error.age_from"/>
                        <BaseInputText v-model="adsAgeTo" :placeholder="$t('To')" :error="error.age_to" />
                    </div>
                </div>
                <div class="mb-base-2">
                    <label class="block mb-1">{{ $t('Target hashtags') }} <InfoTooltip :content="getHelpTextHashTag()"/></label>
                    <AutoComplete v-model="hashtags" multiple optionLabel="name" :suggestions="hashtagsList" @complete="suggestHashtagsList" :emptySearchMessage="$t('No hashtags found')" class="w-full">
                        <template #option="slotProps">
                            <div>{{ slotProps.option.name }}</div>
                        </template>
                    </AutoComplete>
                </div>
                <div class="mb-base-2">
                    <BaseSelectLocation v-model="location" :show-address="false">
                        <label class="block mb-1">{{ $t('Location') }} <InfoTooltip :content="getHelpTextLocation()"/></label>
                    </BaseSelectLocation>
                </div>
            </div>
            <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
                    <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ $t('Budget & Scheduling') }}</h3>
                </div>
                <div class="mb-base-2">{{ $t('Set a budget that fits your needs and a date range to take more control of your spend.') }}</div>
                <div class="mb-base-2">
                    <label class="block mb-1">{{ $t('Daily budget') }}</label>
                    <BaseInputText v-model="postAmount" :error="error.daily_amount" />
                </div>
                <div class="mb-base-2">
                    <label class="block mb-1">{{ $t('Date Range') }} <InfoTooltip :content="getHelpTextDateRange()"/></label>
                    <div class="flex flex-col sm:flex-row gap-base-2 mb-1">
                        <BaseCalendar v-model="postStartDate" :placeholder="$t('Start Date')" :error="error.start" />
                        <BaseCalendar v-model="postEndDate" :placeholder="$t('End Date')" :error="error.end" />
                    </div>
                    <p class="text-base-xs text-sub-color dark:text-slate-400">{{ $t('Ads that run for at least 4 days tend to get better results.') }}</p>
                </div>
            </div>
            <div class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
                    <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ $t('Payment method') }}</h3>
                </div>
                <div class="mb-base-2">
                    <span v-if="config.wallet.enable">{{ $t('Ewallet') + ' ( ' + exchangeTokenCurrency(user.wallet_balance) }} ~ {{ exchangeCurrency(user.wallet_balance) + ')' }}</span>
                    <span v-else>{{ $t('The eWallet system is not available at this time.') }}</span>
                </div>
                <BaseButton v-if="config.wallet.enable" :href="baseUrl + '/wallet'" target="_blank">{{ $t('Add funds') }}</BaseButton>
            </div>
        </template>
        <template v-slot:right>
            <div v-if="postInfo" class="main-content-section bg-white border border-white text-main-color rounded-none md:rounded-base-lg p-5 dark:bg-slate-800 dark:border-slate-800 dark:text-white">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-base-2">
                    <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{ $t('Ads Preview') }}</h3>
                </div>
                <div>
                    <div class="feed-item-header flex mb-base-2">
                        <div class="feed-item-header-img">
                            <Avatar :user="user" :activePopover="false" :router="false"/>
                        </div>
                        <div class="feed-item-header-info flex-grow ps-base-2">
                            <div class="feed-item-header-info-title flex justify-between items-start gap-base-2">
                                <div class="whitespace-normal">
                                    <UserName :user="user" :truncate="false" :activePopover="false" :router="false" />
                                </div>
                                <div class="pt-[0.1rem]">
                                    <BaseIcon name="more_horiz_outlined" size="20" class="feed-item-dropdown text-sub-color dark:text-slate-400"/>
                                </div>
                            </div>
                            <div class="feed-item-header-info-date block w-max text-sub-color text-xs dark:text-slate-400">{{ postInfo.created_at }}</div>
                        </div>
                    </div>
                    <div class="feed-item-content">
                        <ContentHtml :content="postInfo.content" />
                        <VueperSlides v-if="postMedias.length" :slide-ratio="aspectRatioImage(postMedias[0].subject.params)" :infinite="false" :arrows="true" :bullets="false" disable-arrows-on-edges
                            :rtl="user.rtl ? true : false" :touchable="false" :gap="5" class="activity_content_photos_list no-shadow">
                            <VueperSlide v-for="(adsMedia, index) in postInfo.items" :key="adsMedia.id" class="cursor-pointer bg-no-repeat"
                                :style="{ backgroundColor: `${adsMedia.subject.params.dominant_color ? adsMedia.subject.params.dominant_color : '#000'}`}"
                                :image="adsMedia.subject.url" @click="clickPhotoImage(index)"></VueperSlide>
                            <template #arrow-left>
                                <div class="arrow_slider arrow_slider_left"></div>
                            </template>
                            <template #arrow-right>
                                <div class="arrow_slider arrow_slider_right"></div>
                            </template>
                        </VueperSlides>
                        <div v-if="postSharedLink" class="relative border border-divider dark:border-slate-700">
							<LinkFetched :postItemsList="[postSharedLink]"/>
						</div>
						<div v-if="postUploadedVideo">
							<div class="relative bg-black">
								<img class="w-full" :class="(aspectRatioVideo(postUploadedVideo.subject.thumb.params) == 'horizontal') ? '' : 'max-w-[200px] mx-auto'" :src="postUploadedVideo.subject.thumb.url" />			
							</div>
						</div>
                        <div v-if="postUploadedFiles.length" class="flex flex-wrap gap-base-2 mb-base-2">
							<div v-for="file in postUploadedFiles" :key="file.id" class="bg-white text-inherit border border-divider px-base-2 py-2 rounded-1000 relative max-w-[200px] dark:bg-dark-web-wash dark:border-slate-700">
								<div class="flex items-center gap-2">
									<BaseIcon name="file" />
									<span class="truncate">{{ file.subject.name }}</span>
								</div>
							</div>
						</div>
                    </div>
                    <div class="feed-main-action mt-3 mb-base-2 dark:text-white">
                        <div class="flex justify-between mb-base-2">
                            <div class="flex space-s-3">
                                <div>
                                    <BaseIcon name="like"/>
                                </div>
                                <div>
                                    <BaseIcon name="message"/>
                                </div>
                                <div>
                                    <BaseIcon name="share"/>
                                </div>
                            </div>
                            <div class="feed-comment-info-action-right flex">
                                <div>
                                    <BaseIcon name="bookmarks" class="dark:text-white"/>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="border-t border-divider pt-base-2 mt-base-2 dark:border-white/10">
                        <div class="font-semibold mb-base-2">{{ $t('Demographics & Targeting features') }}</div>
                        <div class="flex gap-base-2 justify-between mb-base-2">
                            <div>{{ $t('Gender') }}</div>
                            <div class="text-end">{{ genderId ? adsConfig?.genders[genderId] : $t('All') }}</div>
                        </div>
                        <div class="flex gap-base-2 justify-between mb-base-2">
                            <div>{{ $t('Age') }}</div>
                            <div class="text-end">
                                <span v-if="selectedAge == ageTypeAny">{{ $t('Any') }}</span>
                                <span v-if="selectedAge == ageTypeRange">{{ (postAgeFrom || 0) + '-' + (postAgeTo || 0) }}</span>
                            </div>
                        </div>
                        <div class="flex gap-base-2 justify-between mb-base-2">
                            <div>{{ $t('Hashtags') }}</div>
                            <div class="text-end break-word">
                                <span v-for="(hashtag, index) in hashtags" :key="hashtag.name">
                                    {{ '#' + hashtag.name + (index == (hashtags.length - 1) ? '' : ', ') }}
                                </span>
                            </div>
                        </div>
                        <div class="flex gap-base-2 justify-between mb-base-2">
                            <div>{{ $t('Locations') }}</div>
                            <div class="text-end break-word">{{ location.address_full }}</div>
                        </div>
                    </div>
                    <div class="border-t border-divider pt-base-2 mt-base-2 dark:border-white/10">
                        <div class="font-semibold mb-base-2">{{ $t('Payment summary') }}</div>
                        <div class="mb-base-2">{{ $filters.numberShortener(runningDate(postStartDate, postEndDate), $t('Your ads will run for [number] day'), $t('Your ads will run for [number] days')) }}</div>
                        <div class="flex gap-base-2 justify-between mb-base-2">
                            <div>{{ $t('Total budget') }}</div>
                            <div class="text-end">{{ exchangeTokenCurrency(postAmount * runningDate(postStartDate, postEndDate)) }}</div>
                        </div>
                        <div class="flex gap-base-2 justify-between mb-base-2">
                            <div>{{ $t('Estimated VAT') }} ({{config.advertising.vat}}%)</div>
                            <div class="text-end">{{ exchangeTokenCurrency(postAmount * runningDate(postStartDate, postEndDate) * Number(config.advertising.vat) / 100) }}</div>
                        </div>
                    </div>
                    <div class="border-t border-divider pt-base-2 mt-base-2 dark:border-white/10">
                        <div class="flex gap-base-2 justify-between mb-base-2 font-semibold">
                            <div>{{ $t('Total amount') }}</div>
                            <div class="text-end">{{ exchangeTokenCurrency(postAmount * runningDate(postStartDate, postEndDate) * (100 + Number(config.advertising.vat)) / 100) }}</div>
                        </div>
                    </div>
                    <BaseButton class="w-full" @click="boostPost()">{{ $t('Boost Post') }}</BaseButton>
                </div>
            </div>
        </template>
    </FullColumn>
</template>

<script>
import { mapState } from 'pinia';
import { getMentionHashtagsList } from '@/api/hashtag'
import { getAdvertisingConfig, storeBootAdvertising, storeValidateBootAdvertising } from '@/api/ads'
import { getPostById } from '@/api/posts'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app';
import { VueperSlides, VueperSlide } from 'vueperslides'
import FullColumn from '@/components/columns/FullColumn.vue'
import AutoComplete from 'primevue/autocomplete';
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseSelectLocation from '@/components/inputs/BaseSelectLocation.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import InfoTooltip from '@/components/utilities/InfoTooltip.vue';
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseRadio from '@/components/inputs/BaseRadio.vue'
import BaseCalendar from '@/components/inputs/BaseCalendar.vue'
import LinkFetched from "@/components/posts/LinkFetched.vue"
import PasswordModal from '@/components/modals/PasswordModal.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import Constant from '@/utility/constant'

export default {
    props: ['id'],
    components: { FullColumn, AutoComplete, BaseInputText, BaseSelectLocation, BaseIcon, Avatar, UserName, InfoTooltip, BaseButton, BaseRadio, BaseCalendar, VueperSlides, VueperSlide, LinkFetched, ContentHtml },
    data(){
        return{
            ageTypeAny: Constant.AGE_TYPE.ANY,
            ageTypeRange: Constant.AGE_TYPE.RANGE,
            adsConfig: null,
            postInfo: null,
            postType: 'text',
            postMedias: [],
            postSharedLink: null,
			postUploadedVideo: null,
			postUploadedFiles: [],
            hashtags: [],
            hashtagsList: [],
            location: {
                country_id: null,
				state_id: null,
				city_id: null,
				zip_code: null,
				address: null,
                address_full: null
			},
            error: {
                name: null,
                daily_amount: null,
                start: null,
                end: null,
                age_from: null,
                age_to: null
            },
            adsName: null,
            selectedAge: Constant.AGE_TYPE.ANY,
            postAgeFrom: null,
            postAgeTo: null,
            genderId: 0,
            postStartDate: 0,
            postEndDate: 0,
            postAmount: 0
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config']),
        baseUrl(){
            return window.siteConfig.siteUrl
        }
    },
    watch:{
        selectedAge(){
            switch(this.selectedAge){
                case this.ageTypeAny:
                    this.adsAgeFrom = this.adsAgeTo = null
                    break;
            }
        }
    },
    mounted(){
        if (! this.config.advertising.enable) {
            return this.$router.push({ 'name': 'permission' })
        }
        this.getAdvertisingConfig()
        this.getPostDetail(this.id);
    },
    methods: {
        async getAdvertisingConfig(){
            try {
                const response = await getAdvertisingConfig()
                this.adsConfig = response
            } catch (error) {
                this.showError(error.error)
            }
        },
        async getPostDetail(adsId){
            try {
                const response = await getPostById(adsId);
                this.postInfo = response
                this.postType = this.postInfo.type
                switch(this.postType){
                    case 'photo':
                        this.postMedias = this.postInfo.items
                        break;
                    case 'link':
                        this.postSharedLink = this.postInfo.items[0]
                        break;
                    case 'video':
                        this.postUploadedVideo = this.postInfo.items[0]
                        break;
                    case 'file':
                        this.postUploadedFiles = this.postInfo.items
                        break;
                }
            } catch (error) {
                this.showError(error.error)
            }
        },
        async suggestHashtagsList(event) {
            try {
				const response = await getMentionHashtagsList(event.query, 1);
                this.hashtagsList = response;
			} catch (error) {
                this.hashtagsList = [];
			}  
        },
        async boostPost(){
            let idItems = null;
            if(this.postMedias.length > 0){
                idItems = this.postMedias.map(x => x.id); 
            }else if(this.postSharedLink){
                idItems = [this.postSharedLink.id]
            }else if(this.postUploadedVideo){
                idItems = [this.postUploadedVideo.id]
            }else if(this.postUploadedFiles.length){
                idItems = this.postUploadedFiles.map(x => x.id); 
            }
            let adsData = {
                id: this.id,
                content: this.postInfo.content,
                type: this.postType,
                items: idItems,
                name: this.adsName,
                country_id: this.location.country_id,
                state_id: this.location.state_id,
                city_id: this.location.city_id,
                zip_code: this.location.zip_code,
                hashtags: this.hashtags.map(hashtag => hashtag.name),
                gender_id: this.genderId,
                daily_amount: this.postAmount,
                age_type: this.selectedAge,
                age_from: this.postAgeFrom,
                age_to: this.postAgeTo,
                start: this.formatDateTime(this.postStartDate),
                end: this.formatDateTime(this.postEndDate)
            }
            try {
                await storeValidateBootAdvertising(adsData)
                this.resetErrors(this.error)
                const passwordDialog = this.$dialog.open(PasswordModal, {
                    props: {
                        header: this.$t('Enter Password'),
                        class: 'password-modal',
                        modal: true,
                        dismissableMask: true,
                        draggable: false
                    },
                    emits: {
                        onConfirm: async (data) => {
                            if (data.password) {
                                try {
                                    adsData.password = data.password
                                    await storeBootAdvertising(adsData)
                                    this.showSuccess(this.$t('Thank you, your payment has been processed and your ad is scheduled to run now. You can manage all your ads in the ads section.'))
                                    this.$router.push({ name: 'advertisings' })
                                    passwordDialog.close()
                                } catch (error) {
                                    this.handleApiErrors(this.error, error)
                                    passwordDialog.close()
                                }
                            }
                        }
                    }
                })
            } catch (error) {
                this.handleApiErrors(this.error, error)
            }
        },
        getHelpTextHashTag() {
            return this.$filters.textTranslate(this.config.siteName,this.$t('Keyword targeting allows you to reach people on %s based on keywords in their search queries, recent posts, and posts they recently engaged with. This targeting option puts you in the best position to reach the most relevant people, drive engagements, and increase conversions.'));
        },
        getHelpTextLocation() {
            return this.$filters.textTranslate(this.config.siteName,this.$t("Advertisers can target their campaigns to specific geographies: countries, regions, metros, cities, postal codes. %s geo targeting is based on location info inside member's profile."));
        },
        getHelpTextDateRange() {
            return this.$t("Choose how long you want your campaign to run by setting your date range from the calendar. The start date defaults to today's date, but you can change it to any date in the future.");
        }
    }
}
</script>