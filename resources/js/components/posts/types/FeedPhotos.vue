<template>
	<div :id="'postFeedSlider_'+post.id">
		<VueperSlides v-if="post.items" ref="photosFeed" :slide-ratio="aspectRatioImage(post.items[0].subject.params)" :infinite="false" :arrows="true" disable-arrows-on-edges
			:rtl="user.rtl ? true : false" :touchable="false" :gap="5" @slide="slideSlider($event.currentSlide.index)" class="activity_content_photos_list no-shadow">
			<VueperSlide v-for="(postItem, index) in post.items" :key="postItem.id" class="cursor-pointer bg-no-repeat"
				:style="{ backgroundColor: `${postItem.subject.params.dominant_color ? postItem.subject.params.dominant_color : '#000'}`}"
				:image="postItem.subject.url" @click="clickPhotoImage(index)"></VueperSlide>
			<template #arrow-left>
				<div class="arrow_slider arrow_slider_left"></div>
			</template>
			<template #arrow-right>
				<div class="arrow_slider arrow_slider_right"></div>
			</template>
		</VueperSlides>
	</div>
	<PhotoTheater ref="photoTheater" :photos="post.items"/>
</template>

<script>
import { mapState } from 'pinia'
import { VueperSlides, VueperSlide } from 'vueperslides'
import PhotoTheater from '@/components/modals/PhotoTheater.vue';
import { useAuthStore } from '@/store/auth'

export default {
	components: {
		VueperSlides, VueperSlide, PhotoTheater
	},
	props: ['post'],
	data() {
		return {
			displayPhotosTheater: false
		}
	},
	mounted(){
		var postParentWrap = document.getElementById("postFeedSlider_" +this.post.id)
		var bullets = postParentWrap.getElementsByClassName("vueperslides__bullet")
		if(bullets.length > 6){
			for (let i = 0; i < bullets.length; i++) {
				if(this.user.rtl){
					bullets[i].style.transform = 'translateX(-'+((bullets.length * 14 - 84) / 2)+'px)'
				}else{
					bullets[i].style.transform = 'translateX('+((bullets.length * 14 - 84) / 2)+'px)'
				}
			}
		}
	},
	computed: {
		...mapState(useAuthStore, ['user'])
	},
	methods: {
		clickPhotoImage(photoIndex) {
			this.$refs.photoTheater.openPhotosTheater(photoIndex)
		},
		slideSlider(index){
			var postParentWrap = document.getElementById("postFeedSlider_" +this.post.id)
			var bullets = postParentWrap.getElementsByClassName("vueperslides__bullet")	
			if(bullets.length > 6){
				var translateX_prev = new DOMMatrixReadOnly(window.getComputedStyle(bullets[0]).transform).e + 14
				var translateX_next = new DOMMatrixReadOnly(window.getComputedStyle(bullets[0]).transform).e - 14
				if(this.getBoundingClientRect_RelativeToParent(bullets[index]).left > 70){
					for (let i = 0; i < bullets.length; i++) {
						bullets[i].style.transform = 'translateX('+translateX_next+'px)'
					}
				}
				else if(this.getBoundingClientRect_RelativeToParent(bullets[index]).left < 14){
					for (let i = 0; i < bullets.length; i++) {
						bullets[i].style.transform = 'translateX('+translateX_prev+'px)'
					}
				}
			}		
		},
		getBoundingClientRect_RelativeToParent(element){
			const domRect = element.getBoundingClientRect(),
			parentDomRect = element.parentElement.getBoundingClientRect()
			return new DOMRect(domRect.left - parentDomRect.left, domRect.top - parentDomRect.top, domRect.width, domRect.height)
		}
	}
}
</script>