<template>
	<Error v-if="error">{{error}}</Error>
	<Loading v-if="loadingHashtags"/>
	<HashtagsList v-else :hashtags-list="followingHashtags"/>
	<div v-if="loadmoreStatus" class="text-center">
        <BaseButton @click="getFollowingHashtags(page)">{{$t('View more')}}</BaseButton>
    </div>
</template>

<script>
import { getMyHashtags } from '@/api/follow';
import Error from '@/components/utilities/Error.vue';
import Loading from '@/components/utilities/Loading.vue';
import HashtagsList from '@/components/lists/HashtagsList.vue';
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { Error, Loading, HashtagsList, BaseButton },
	data(){
		return {
			error: null,
			loadmoreStatus: false,
			loadingHashtags: true,
            followingHashtags: [],
			page: 1
        };
	},
	mounted(){
		this.getFollowingHashtags(this.page)
	},
	methods:{
		async getFollowingHashtags(page){
			try {
				const response = await getMyHashtags(page)
				if(page === 1){
                    this.followingHashtags = response.items
					this.followingHashtags = window._.map(this.followingHashtags, function(element) { 
						return window._.extend({}, element, {is_followed: true});
					});
                }else{
                    this.followingHashtags = window._.concat(this.followingHashtags, response.items);
					this.followingHashtags = window._.map(this.followingHashtags, function(element) { 
						return window._.extend({}, element, {is_followed: true});
					});
                }
                if(response.has_next_page){
                    this.loadmoreStatus = true
                    this.page++;
                }else{
                    this.loadmoreStatus = false
                }
				this.loadingHashtags = false
			} catch (error) {
				this.error = error
				this.loadingHashtags = false
			}
		}
	}
}
</script>