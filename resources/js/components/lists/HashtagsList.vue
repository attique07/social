<template>
    <div v-if="hashtagsList.length > 0">
        <div v-for="hashtagListItem in hashtagsList" :key="hashtagListItem.name" class="flex items-center mb-3 last:mb-0">
            <router-link :to="{name: 'search', params: {search_type: 'hashtag', type: 'post'}, query: {q: hashtagListItem.name}}" :target="target" class="text-base-none text-main-color dark:text-white">
                <BaseIcon name="big_hashtag" size="48" />
            </router-link>
            <router-link :to="{name: 'search', params: {search_type: 'hashtag', type: 'post'}, query: {q: hashtagListItem.name}}" :target="target" class="flex-1 mx-base-2 text-inherit">
                <div class="list_items_title_text_color text-base-sm font-semibold break-word">{{hashtagListItem.name}}</div>
                <div class="list_items_sub_text_color text-xs text-sub-color dark:text-slate-400">{{ $filters.numberShortener(hashtagListItem.post_count, $t('[number] post'), $t('[number] posts')) }}</div>
            </router-link>
            <button v-if="hashtagListItem.is_followed" class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color" @click="unFollowHashtag(hashtagListItem.name)">{{$t('Unfollow')}}</button>
            <button v-else class="list_items_button text-xs text-primary-color cursor-pointer font-bold dark:text-dark-primary-color" @click="followHashtag(hashtagListItem.name)">{{$t('Follow')}}</button>
        </div>
    </div>
    <div v-else class="text-center">{{$t('No Tags')}}</div>
</template>
<script>
import { toggleFollowHashtag } from '../../api/follow';
import BaseIcon from '../icons/BaseIcon.vue';

export default {
	components:{ BaseIcon },
    props: ['hashtagsList', 'target'],
	methods: {
        async followHashtag(hashtagName){
            try {
                await toggleFollowHashtag({name: hashtagName, action: 'follow'});
                this.hashtagsList.map(hashtagItem => {
                    if (hashtagItem.name === hashtagName) {
                        hashtagItem.is_followed = true
                    }
                    return hashtagItem
                });
                this.$emit('follow_hashtag', hashtagName)
            }
            catch (error) {
                this.showError(error.error)
            }
        },
        async unFollowHashtag(hashtagName){
            try {
                await toggleFollowHashtag({name: hashtagName, action: 'unfollow'});
                this.hashtagsList.map(hashtagItem => {
                    if (hashtagItem.name === hashtagName) {
                        hashtagItem.is_followed = false
                    }
                    return hashtagItem
                });
                this.$emit('unfollow_hashtag', hashtagName)
            }
            catch (error) {
                this.showError(error.error)
            }
        }
	},
    emits: ['follow_hashtag', 'unfollow_hashtag']
}
</script>