<template>
	<Loading v-if="loadingPostsList" />
	<template v-else>
		<div class="post-status-box bg-white border border-white rounded-none md:rounded-base-lg p-4 mb-base-2 dark:bg-slate-800 dark:border-slate-800">
			<div class="flex items-center gap-base-2">
				<Avatar :user="user" :activePopover="false" :router="false"/>
				<div @click="openStatusBox()" class="flex-1 post-status-box-placeholder text-sub-color dark:text-slate-400 pe-base-2 py-base-2" role="button">{{ $t('What do you want to talk about?') }}</div>
			</div>
			<div v-if="config.ffmegEnable || config.postUploadFileEnable" class="flex gap-base-2 justify-between items-center border-t border-divider mt-base-2 pt-base-2 dark:border-white/10">
				<button @click="openStatusBox('photo')" class="flex-1 flex gap-base-2 items-center justify-center bg-light-web-wash rounded-1000 p-2 dark:bg-dark-web-wash dark:text-white hover:bg-hover">
					<BaseIcon name="photo" />
					<span>{{ $t('Photo') }}</span>
				</button>
				<button v-if="config.ffmegEnable" @click="openStatusBox('video')" class="flex-1 flex gap-base-2 items-center justify-center bg-light-web-wash rounded-1000 p-2 dark:bg-dark-web-wash dark:text-white hover:bg-hover">
					<BaseIcon name="youtube" />
					<span>{{ $t('Video') }}</span>
				</button>
				<button v-if="config.postUploadFileEnable" @click="openStatusBox('file')" class="flex-1 flex gap-base-2 items-center justify-center bg-light-web-wash rounded-1000 p-2 dark:bg-dark-web-wash dark:text-white hover:bg-hover">
					<BaseIcon name="paperclip" />
					<span>{{ $t('File') }}</span>
				</button>
			</div>
		</div>
		<div v-if="postsList.length > 0" class="pb-5">
			<TransitionGroup name="fade">
				<PostListsItem v-for="post in postsList" :key="post.id" :post="post"></PostListsItem>
			</TransitionGroup>
			<InfiniteLoading @infinite="loadMorePosts">
				<template #spinner>
					<Loading />
				</template>
				<template #complete><span></span></template>
			</InfiniteLoading>
		</div>
		<div v-else class="main-content-section bg-white border border-white text-main-color p-10 text-center rounded-none mb-base-2 md:rounded-base-lg dark:bg-slate-800 dark:border-slate-800 dark:text-white">
			<div class="text-base-lg font-bold mb-base-2">{{ $t('Nothing to see here yet') }}</div>
			<div class="text-base-sm">{{ $t('Start following people and tags to see updated posts') }}</div>
		</div>
	</template>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { checkPopupBodyClass } from '@/utility'
import Avatar from '@/components/user/Avatar.vue'
import PostListsItem from '@/components/posts/PostListsItem.vue'
import Loading from '@/components/utilities/Loading.vue'
import InfiniteLoading from 'v3-infinite-loading'
import PostStatusBox from '@/components/posts/PostStatusBox.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import { usePostStore } from '@/store/post'
import { useActionStore } from '@/store/action'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app';

export default {
	components: {
		Avatar,
		PostListsItem,
		Loading,
		InfiniteLoading,
		BaseIcon
	},
	data() {
		return {
			currentPage: 1
		}
	},
	mounted() {
		this.getHomePostsList(this.currentPage);
	},
	unmounted() {
		this.unsetPostsList()
	},
	computed: {
		...mapState(useAuthStore, ['user']),
		...mapState(usePostStore, ['postsList', 'loadingPostsList']),
		...mapState(useActionStore, ['samePage']),
		...mapState(useAppStore, ['config'])
	},
	watch: {
		samePage() {
			this.currentPage = 1
			this.unsetPostsList()
			this.getHomePostsList(this.currentPage)
		}
	},
	methods: {
		...mapActions(usePostStore, ['getHomePostsList', 'unsetPostsList']),
		loadMorePosts($state) {
			this.getHomePostsList(++this.currentPage).then((response) => {
				if (response.length === 0) {
					$state.complete()
				} else {
					$state.loaded()
				}
			})
		},
		openStatusBox(type){
			this.$dialog.open(PostStatusBox, {
				data: {
					chosenType: type
				},
                props:{
					class: 'post-status-modal p-dialog-lg',
                    modal: true,
					showHeader: false
                },
				onClose: () => {
                    checkPopupBodyClass();
                }
            });
		}
	},
}
</script>
