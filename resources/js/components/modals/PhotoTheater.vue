<template>
	<Teleport to="body">
		<div v-if="displayPhotosTheater" class="photos-feed-fullscreen-mask" @click="handleClickBackdrop">
			<button type="button" class="photos-feed-fullscreen-close" @click="closePhotosTheater">
				<BaseIcon name="big_close" size="44"/>
			</button>
			<VueperSlides v-if="photos.length > 0" fractions ref="photosFeedFullscreen" :infinite="false" :arrows="true" :bullets="false" disable-arrows-on-edges
			@slide="$refs.photosFeedFullscreen.goToSlide($event.currentSlide.index, { emit: false })" :dragging-distance="50" prevent-y-scroll
			:rtl="user.rtl ? true : false" :gap="5" class="photos-feed-fullscreen-slider no-shadow">
				<VueperSlide v-for="postItem in photos" :key="postItem.id">
				<template #content>
					<div class="vueperslide__content-wrapper absolute top-1/2 -translate-y-1/2 left-2 right-2 md:left-16 md:right-16 z-10">
						<img :src="postItem.subject.url" alt="">
					</div>
				</template>
				</VueperSlide>
				<template #arrow-left>
					<button class="fixed flex items-center justify-center h-full top-0 bottom-0 left-0 hover:bg-gray-trans-4 hover:ps-1 transition-all cursor-pointer">
						<span class="photos-feed-fullscreen-nav w-14 h-14 text-white mx-2">
							<BaseIcon name="chevron_left" size="44"/>
						</span>
					</button>
				</template>
				<template #arrow-right>
					<button class="fixed flex items-center justify-center h-full top-0 bottom-0 right-0 hover:bg-gray-trans-4 hover:pe-1 transition-all cursor-pointer">
						<span class="photos-feed-fullscreen-nav w-14 h-14 text-white mx-2">
							<BaseIcon name="chevron_right" size="44"/>
						</span>
					</button>
				</template>
			</VueperSlides>	
		</div>
	</Teleport>
</template>

<script>
import { mapState } from 'pinia'
import { VueperSlides, VueperSlide } from 'vueperslides'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import { useAuthStore } from '@/store/auth'

export default {
	components: {
		VueperSlides, VueperSlide, BaseIcon
	},
	props: ['photos'],
	data() {
		return {
			displayPhotosTheater: false
		}
	},
	computed: {
		...mapState(useAuthStore, ['user'])
	},
	methods: {
		onKeyDown(event) {
			switch (event.code) {
				case 'Escape':
					this.closePhotosTheater()
					break;
				case 'ArrowLeft':
					if(this.user.rtl){
						this.$nextTick((this.$refs.photosFeedFullscreen.next()))			
					}else{
						this.$nextTick((this.$refs.photosFeedFullscreen.previous()))			
					}
					break;
				case 'ArrowRight':
					if(this.user.rtl){
						this.$nextTick((this.$refs.photosFeedFullscreen.previous()))		
					}else{
						this.$nextTick((this.$refs.photosFeedFullscreen.next()))					
					}
					break;
				default:
					break;
			}
        },
		openPhotosTheater(photoIndex) {
			this.displayPhotosTheater = true;
			document.body.classList.add('overflow-hidden')
			this.$nextTick(() => this.$refs.photosFeedFullscreen.goToSlide(photoIndex))
			window.addEventListener('keydown', this.onKeyDown);
		},
		closePhotosTheater(){
			this.displayPhotosTheater = false;
			document.body.classList.remove('overflow-hidden')
			window.removeEventListener('keydown', this.onKeyDown);
		},
		handleClickBackdrop(e){
			if(e.target.tagName == 'DIV'){
				this.displayPhotosTheater = false;
				document.body.classList.remove('overflow-hidden')
				window.removeEventListener('keydown', this.onKeyDown);
			}
		}
	}
}
</script>