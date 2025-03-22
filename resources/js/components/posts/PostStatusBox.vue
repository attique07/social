<template>
	<UploadWrap @drop_data="dropData">
		<div ref="postStatusBox" class="p-[2.5rem_1rem] lg:p-[1.5rem_1.25rem_4.5rem]">
			<button @click="closeStatusBox()" class="float-end -mt-8 -me-1 md:mt-2 md:me-0">
				<BaseIcon name="close" class="post-status-close-icon text-main-color dark:text-white" />	
			</button>
			<div class="status-box-dialog">
				<div class="status-box-main rounded-md ms-auto me-auto text-main-color dark:text-white">
					<div class="status-box-header flex justify-between items-center me-10 mb-2">
						<div class="status-box-user flex items-center truncate">
							<Avatar :user="user" class="me-3"/>
							<UserName :user="user" :activePopover="false" />
						</div>
					</div>
					<div class="status-box-message pb-2">
						<div class="status-box-message-input">						
							<Mentionable v-model="post.content" :placeholder="$t('What do you want to talk about?')" :rows="3" ref="mention" @update:modelValue="inputChange" @paste_content="pasteImage" :hasEventEnter="false" className="max-h-[75vh] lg:max-h-[60vh]"/>
						</div>
						<EmojiPicker @emoji_click="addEmoji"/>
					</div>
					<ProgressBar :value="videoUploadProgress" class="mb-base-2"/>
					<div class="status-box-items">
						<Loading v-if="loading_fetch_link"/>
						<div v-if="!loading_fetch_link && shared_link" class="relative border border-divider dark:border-slate-700">
							<LinkFetched :postItemsList="[shared_link]"/>
							<button @click="removeLinkFetched()" class="absolute top-2 end-2 fetched-link-close flex items-center justify-center w-7 h-7 rounded-full bg-primary-color dark:bg-dark-primary-color">
								<BaseIcon name="close" size="20" class="text-white" />
							</button>
						</div>
						<div class="status-box-image-upload-block">
							<div v-if="images_upload.length" class="status-box-image-upload-preview mb-base-2">
								<VueperSlides class="no-shadow" :slide-ratio="0.5625" :infinite="false" :bullets="false" disable-arrows-on-edges :touchable="false" transition-speed='200' ref="previewUploadImages" :rtl="user.rtl ? true : false">
									<VueperSlide
										v-for="image in images_upload"
										:key="image.subject.id"
										class="status-box-image-upload-preview-item"
										:image="image.subject.url"
										:style="{ backgroundColor: `${image.subject.params.dominant_color ? image.subject.params.dominant_color : '#000'}`}"
									>
									</VueperSlide>
									<template #arrow-left>
										<div class="arrow_slider arrow_slider_left"></div>
									</template>
									<template #arrow-right>
										<div class="arrow_slider arrow_slider_right"></div>
									</template>
								</VueperSlides>
							</div>
							<div v-if="images_upload.length || images_upload_loading.length" class="flex flex-wrap gap-2 mb-base-2">
								<Draggable v-if="images_upload.length" :list="images_upload" @dragover="preventPhotosListDrag($event)" @dragend="endDraggingPhotos($event)" class="flex flex-wrap gap-2">
									<TransitionGroup type="transition">
									<div
										v-for="(image, index) in images_upload"
										:key="image.subject.id"
										class="inline-block w-20 h-20 bg-cover bg-center relative cursor-pointer border border-divider rounded-md dark:border-white/10"
										:style="{ backgroundImage: `url(${image.subject.url})`}"
										@click="$refs.previewUploadImages.goToSlide(index)"
										@touchend="$refs.previewUploadImages.goToSlide(index)"
									>
										<button class="inline-flex absolute top-1 end-1 bg-black/30 text-white w-4 h-4" @touchend.prevent="removeImage(image.id, index)" @click.stop.prevent="removeImage(image.id, index)">
											<BaseIcon name="close" size="16" />
										</button>
									</div>
									</TransitionGroup>
								</Draggable>
								<div v-for="index in images_upload_loading" :key="index" class="inline-block w-20 h-20 bg-cover bg-center relative rounded-md border border-divider dark:border-white/10 float-start status-box-image-upload-list-loading">
									<span class="loading-icon">
										<div class="loader"></div>
									</span>
								</div>
								<button v-if="images_upload.length || images_upload_loading.length" class="add-images-icon inline-flex items-center justify-center w-20 h-20 text-main-color border border-divider dark:text-white/50 dark:border-white/10 rounded-md hover:bg-hover" @click="this.$refs.imagesUploadStatus.open()">
									<BaseIcon name="photo" />
								</button>
							</div>									
						</div>
						<div v-if="video_upload">
							<div class="relative bg-black">
								<img class="w-full" :class="(aspectRatioVideo(video_upload.subject.thumb.params) == 'horizontal') ? '' : 'max-w-[200px] mx-auto'" :src="video_upload.subject.thumb.url" />
								<button @click="removeUploadedVideo(video_upload.id)" class="absolute top-2 end-2 flex items-center justify-center w-7 h-7 rounded-full bg-primary-color dark:bg-dark-primary-color">
									<BaseIcon name="close" size="20" class="text-white" />
								</button>					
							</div>
						</div>
						<div v-if="files_upload_loading.length || files_upload.length" class="flex flex-wrap gap-base-2 mb-base-2">
							<div v-for="index in files_upload_loading" :key="index" class="inline-block w-48 relative rounded-md border border-divider dark:border-white/10 status-box-image-upload-list-loading">
								<span class="loading-icon">
									<div class="loader"></div>
								</span>
							</div>
							<div v-for="file in files_upload" :key="file.id" class="bg-web-wash border border-divider p-base-2 rounded-md relative max-w-[200px] dark:bg-dark-web-wash dark:border-slate-700">
								<div class="flex items-center gap-2">
									<BaseIcon name="file" />
									<span class="truncate">{{ file.subject.name }}</span>
								</div>
								<button class="shadow-md inline-flex items-center justify-center absolute -top-2 -end-2 bg-white border border-divider text-main-color rounded-full w-5 h-5" @click="removePostFile(file.id)">
									<BaseIcon name="close" size="16" />
								</button>
							</div>
						</div>
					</div>
					<div class="status-box-action flex justify-between items-center bg-white border-t border-divider dark:border-white/10 dark:bg-slate-800 z-10">
						<div class="status-box-action-list flex items-center gap-3">
							<UploadImages v-if="show_upload_image" ref="imagesUploadStatus" @upload="uploadImages" class="status-box-action-list-item" />
							<UploadVideo v-if="show_upload_video" ref="videoUploadStatus" @upload="uploadVideo" class="status-box-action-list-item" />
							<UploadFiles v-if="show_upload_file" ref="fileUploadStatus" @upload="uploadFiles" class="status-box-action-list-item" />
							<TenorGifs v-if="show_upload_image" @upload="uploadGif" class="status-box-action-list-item" />
						</div>
						<BaseButton :loading="loading" :disabled="!(post.content.trim() != '' || images_upload.length || shared_link || video_upload || files_upload.length)" @click="postStatus()">{{$t('Post')}}</BaseButton>
					</div>				
				</div>
			</div>
		</div>
	</UploadWrap>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { VueDraggableNext } from "vue-draggable-next";
import { VueperSlides, VueperSlide } from 'vueperslides'
import { uploadPostImages, deletePostItem, fetchLink, uploadPostVideo, uploadPostFiles } from '@/api/posts'
import BaseIcon from "@/components/icons/BaseIcon.vue"
import BaseButton from "@/components/inputs/BaseButton.vue"
import EmojiPicker from "@/components/utilities/EmojiPicker.vue"
import Mentionable from "@/components/utilities/Mentionable.vue"
import Loading from '@/components/utilities/Loading.vue'
import LinkFetched from "@/components/posts/LinkFetched.vue"
import UploadWrap from '@/components/layout/UploadWrap.vue';
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import TenorGifs from '@/components/utilities/TenorGifs.vue'
import { useAuthStore } from '@/store/auth';
import { useAppStore } from '@/store/app';
import { usePostStore } from '@/store/post';
import { useUtilitiesStore } from '@/store/utilities'
import Constant from '@/utility/constant'
import UploadImages from '@/components/utilities/UploadImages.vue'
import UploadVideo from '@/components/utilities/UploadVideo.vue'
import UploadFiles from '@/components/utilities/UploadFiles.vue'
import ProgressBar from '@/components/utilities/ProgressBar.vue'

export default {
	components: {
		BaseIcon,
		BaseButton,
		EmojiPicker,
		VueperSlides,
		VueperSlide,
		Draggable: VueDraggableNext,
		LinkFetched,
		Mentionable,
		Loading,
		UploadWrap,
		Avatar,
		UserName,
		TenorGifs,
		UploadImages,
		UploadVideo,
		UploadFiles,
		ProgressBar
	},
	inject: {
        dialogRef: {
            default: null
        }
    },
	data(){
		return{
			listPosts: [],
			images_upload: [],
			images_upload_loading: [],
			shared_link: null,
			post: {
				type: 'text',
				content: '',
				items: null
			},
			loading_fetch_link: false,
			saved_link: [],
			video_upload: null,
			loading: false,
			show_upload_image: true,
			show_upload_video: false,
			show_upload_file: false,
			files_upload_loading: [],
			files_upload: [],
			chosenType: this.dialogRef.data?.chosenType,
			videoUploadProgress: 0
		}
	},
	mounted(){
		this.$refs.mention.addContent('') // Focus Status Box when open
		this.show_upload_video = this.config.ffmegEnable,
		this.show_upload_file = this.config.postUploadFileEnable
		this.$nextTick(() => {
			switch (this.chosenType) {
				case 'photo':
					this.$refs.imagesUploadStatus.open()
					break;
				case 'video':
					this.$refs.videoUploadStatus.open()
					break;
				case 'file':
					this.$refs.fileUploadStatus.open()
					break;
				default:
					break;
			}
		})
	},
	computed:{
		...mapState(useAuthStore, ['user']),
		...mapState(useAppStore, ['config']),
		...mapState(useUtilitiesStore, ['eventDragDrop'])
	},
	methods: {
		...mapActions(usePostStore, ['postNewFeed']),
		...mapActions(useUtilitiesStore, ['setEventDragDrop']),
		async postStatus(){
			try {
				if (this.loading) {
					return
				}
				// check post status empty
				let check = false;
				if (this.post.content.trim() != '' || this.images_upload.length || this.shared_link || this.video_upload || this.files_upload.length) {
					check = true;
				}
	
				if (!check) {
					this.showError(this.$t('The content is required.'))
					return;
				}

				this.loading = true
	
				let idItems = null;
				if(this.images_upload.length){
					idItems = this.images_upload.map(x => x.id); 
				}else if(this.shared_link){
					idItems = [this.shared_link.id]
				}else if(this.video_upload){
					idItems = [this.video_upload.id]
				}else if(this.files_upload.length){
					idItems = this.files_upload.map(x => x.id); 
				}

				const response = await this.postNewFeed({
					type: this.post.type,
					content: this.post.content,
					items: idItems
				})
				this.post.type = 'text';
				this.post.content = '';
				this.images_upload = [];
				this.shared_link = null;
				this.video_upload = null;
				if(response.queue){
					this.showSuccess(this.$t('The video in your post is being processed. We will send you a notification when it is done.'))
				}else{
					this.showSuccess(this.$t('Your post has been shared.'))
				}
				this.closeStatusBox()
			} catch (error) {
				if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
					this.showPermissionPopup('post',error.error.message)
				} else {
					this.showError(error.error)
				}
			} finally {
				this.loading = false
			}
		},
		uploadImages(event){
			this.startUploadImages(event.target.files)
		},
		pasteImage(event){
			this.startUploadImages(event.clipboardData.items, true)
		},
		dropImage(event){
			if (this.post.type == 'video') {				
				this.startUploadVideo(event.dataTransfer.files)				
			} else {
				this.startUploadImages(event.dataTransfer.files)
			}		
		},
		async startUploadImages(uploadedFiles, clipboard){
			if (typeof clipboard === 'undefined') {
                clipboard = false
            }
			for( var i = 0; i < uploadedFiles.length; i++ ){
				if (clipboard) {
					// Skip content if not image
					if (uploadedFiles[i].type.indexOf("image") == -1) continue;
				}
				var checkUpload = true
				if (! clipboard) {
					checkUpload = this.checkUploadedData(uploadedFiles[i])
				}
				if(checkUpload){
					let formData = new FormData()
                    var blob = uploadedFiles[i]
                    if (clipboard) {
                        blob = uploadedFiles[i].getAsFile();
                    }
                    formData.append('file', blob)
					this.images_upload_loading.unshift(i)
					try {
						const response = await uploadPostImages(formData);
						this.images_upload.push(response);
						this.checkType()
						this.images_upload_loading.shift()
					} catch (error) {
						this.showError(error.error)
						this.images_upload_loading.shift()
						this.$refs.imagesUploadStatus.reset()
					}	
				}
			}
			this.$refs.imagesUploadStatus.reset()
		},
		async removeImage(imageId, index){
			try {
				await deletePostItem({
					id: imageId
				});
				this.images_upload = this.images_upload.filter(image => image.id !== imageId);
				this.checkType()
				if(index === 0){
					setTimeout(() => {
						if (this.$refs.previewUploadImages) {
							this.$refs.previewUploadImages.goToSlide(0)
						}						
					}, 25);
				}
				this.$refs.imagesUploadStatus.reset()
			} catch (error) {
				this.showError(error.error)
			}
		},
		addEmoji(emoji){		
			this.$refs.mention.addContent(emoji)
		},
		async fetchLink(content){
			let fetch_url = this.getUrlFromText(content)
			if(this.post.type != 'text'){
				return
			}
			if (fetch_url && (fetch_url.substring(0, 7) == 'http://' || fetch_url.substring(0, 8) == 'https://' || (fetch_url.substring(0, 4) == 'www.'))){
				this.loading_fetch_link = true
				try {
					const response = await fetchLink({
						url: fetch_url
					});
					this.shared_link = response
					this.loading_fetch_link = false
					this.checkType()
				} catch (error) {
					//this.showError(error.error)
					this.loading_fetch_link = false
				}  
			}
		},
		removeLinkFetched(){
			this.saved_link.push(this.shared_link.subject.url)
			try {
				deletePostItem({
					id: this.shared_link.id
				});
				this.shared_link = null
				this.checkType()
			} catch (error) {
				this.showError(error.error)
			}			
		},
		getUrlFromText(text) {
			let links = text.match(Constant.LINK_REGEX);
			if (links)
			{
				let unique_link = links.filter(link => !this.saved_link.includes(link))
				if(unique_link.length > 0){
					return unique_link[0].charAt(0).toLowerCase() + unique_link[0].slice(1);
				}
			}
		},
		closeStatusBox(){
			this.dialogRef.close();
		},
		inputChange(content){
			var shareLinkTypingTimer = null;
			clearTimeout(shareLinkTypingTimer);
			shareLinkTypingTimer = setTimeout(() => this.fetchLink(content), 1000);
        },
		checkType() {
			if(this.images_upload.length){					
				this.post.type = 'photo'
			}else if(this.video_upload){					
				this.post.type = 'video'
			}else if (this.shared_link) {
				this.post.type = 'link'
			}else if (this.files_upload.length) {
				this.post.type = 'file'
			}else{
				this.post.type = 'text'
			}

			this.show_upload_video = false
			if (window._.includes(['text'],this.post.type) && this.config.ffmegEnable) {
				this.show_upload_video = true
			}

			this.show_upload_file = false
			if (window._.includes(['text', 'file'],this.post.type) && this.config.postUploadFileEnable) {
				this.show_upload_file = true
			}

			this.show_upload_image = false
			if (!window._.includes(['file', 'video', 'link'],this.post.type)) {
				this.show_upload_image = true
			}
		},
		uploadVideo(event){
			this.startUploadVideo(event.target.files)	
		},
		dropVideo(event){
			if (this.post.type == 'photo') {
				this.startUploadImages(event.dataTransfer.files)
			} else {
				this.startUploadVideo(event.dataTransfer.files)
			}
		},
		async startUploadVideo(uploadedFiles){			
			for( var i = 0; i < uploadedFiles.length; i++ ){
				if(this.checkUploadedData(uploadedFiles[i], 'video')){
					if (this.video_upload) {
						this.removeUploadedVideo(this.video_upload.id)
					}
					let formData = new FormData()
					formData.append('file', uploadedFiles[i])
					const onProgress = (progressEvent) => {
						const percentCompleted = Math.round((progressEvent.loaded * 100) / progressEvent.total);
						this.videoUploadProgress = percentCompleted > 0 ? percentCompleted - 1 : percentCompleted;
					};
					try {
						const response = await uploadPostVideo(formData, onProgress);
						this.video_upload = response;
						this.checkType()
						this.videoUploadProgress++
					} catch (error) {
						if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
							this.showPermissionPopup('post',error.error.message)
						} else {
							this.showError(error.error)
						}
						this.$refs.videoUploadStatus.reset()
						this.videoUploadProgress = 0;
					}	
				}
			}
			this.$refs.videoUploadStatus.reset()
		},
		removeUploadedVideo(videoId){
			try {
				deletePostItem({
					id: videoId
				});
				this.video_upload = null
				this.checkType()
				this.videoUploadProgress = 0
			} catch (error) {
				this.showError(error.error)
			}
		},
		dropData(data){
			const fileExtension = data.dataTransfer.files[0].name.split('.').pop().toLowerCase()

			if(data.dataTransfer.files[0].type.split('/')[0] === 'image'){
				this.dropImage(data)
			} else if(this.config.videoExtensionSupport.split(',').includes(fileExtension) && this.config.ffmegEnable){
				this.dropVideo(data)
			} else{
				this.dropImage(data)
			}
		},
		preventPhotosListDrag(e){
			e.preventDefault()
			e.stopPropagation()
		},
		endDraggingPhotos(e){
			this.setEventDragDrop(e)
		},
		uploadFiles(event){
			this.startUploadFile(event.target.files)
		},
		async startUploadFile(uploadedFiles){			
			for( var i = 0; i < uploadedFiles.length; i++ ){
				if(this.checkUploadedData(uploadedFiles[i], 'post')){
					let formData = new FormData()
					formData.append('file', uploadedFiles[i])
					this.files_upload_loading.unshift(i)
					try {
						const response = await uploadPostFiles(formData);
						this.files_upload.push(response);
						this.checkType()
						this.files_upload_loading.shift()
					} catch (error) {
						this.showError(error.error)
						this.files_upload_loading.shift()
						this.$refs.fileUploadStatus.reset()
					}	
				}
			}
			this.$refs.fileUploadStatus.reset()
		},
		async removePostFile(fileId){
			try {
				await deletePostItem({
					id: fileId
				});
				this.files_upload = this.files_upload.filter(file => file.id !== fileId);
				this.checkType()
				this.$refs.fileUploadStatus.reset()
			} catch (error) {
				this.showError(error.error)
			}
		},
		uploadGif(file) {
            this.startUploadImages([file]);
        }
	}
}
</script>