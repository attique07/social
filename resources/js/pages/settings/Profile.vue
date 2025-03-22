<template>
	<template v-if="user.is_page">
		<div class="flex flex-wrap gap-x-5 mb-3"> 
			<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Page Name')}}</label></div>
			<div class="md:flex-2 w-full">
				<BaseInputText v-model="pageName" :error="error.name"/>
			</div>  
		</div>
		<div class="flex flex-wrap gap-x-5 mb-3"> 
			<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Page Alias')}}</label></div>
			<div class="md:flex-2 w-full">
				<BaseInputText v-model="pageUserName" :error="error.user_name"/>
			</div>  
		</div>
	</template>
	<template v-else>
		<Loading v-if="loadingStatus" />
		<div v-else>
			<div class="flex gap-x-2 md:gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end mb-1">
					<img class="w-10 h-10 min-w-10 rounded-full md:ms-auto" :src="avatar" />
				</div>
				<div class="md:flex-2">
					<h5 class="font-extrabold text-main-color dark:text-white">{{userName}}</h5>
					<button class="text-xs font-bold text-primary-color dark:text-dark-primary-color" @click="$refs.avatar.click()">{{$t('Change profile photo')}}</button>
					<input type="file" ref="avatar" @change="uploadAvatar($event)" @click="onInputClick($event)" accept="image/*" class="hidden">
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Display Name')}}</label></div>
				<div class="md:flex-2 w-full">
					<BaseInputText v-model="name" :error="error.name"/>
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Bio')}}</label></div>
				<div class="md:flex-2 w-full">
					<BaseTextarea v-model="bio" :autoResize="true" :error="error.bio"/>
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('About')}}</label></div>
				<div class="md:flex-2 w-full">
					<BaseTextarea v-model="about" :autoResize="true" :error="error.about"/>
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Location')}}</label></div>
				<div class="md:flex-2 w-full">
					<BaseSelectLocation v-model="location" :show-label="false"/>
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Gender')}}</label></div>
				<div class="md:flex-2 w-full">
					<BaseSelect v-model="gender_id" :options="[{'key': $t('Prefer not to say'), 'value': '0'}, ...genders]" optionLabel="key" optionValue="value" :class="{'p-invalid':error.gender_id}" />
					<small v-if="error.gender_id" class="p-error">{{error.gender_id}}</small>
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Birthday')}}</label></div>
				<div class="md:flex-2 w-full">
					<BaseCalendar v-model="birthday" :error="error.birthday"/>
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Timezone')}}</label></div>
				<div class="md:flex-2 w-full">
					<BaseSelect v-model="timezone" :options="timezones" optionLabel="key" optionValue="value" :class="{'p-invalid':error.timezone}" />
					<small v-if="error.timezone" class="p-error">{{error.timezone}}</small>
				</div>  
			</div>
			<div class="flex flex-wrap gap-x-5 mb-3"> 
				<div class="md:flex-1 md:text-end w-full mb-1 pt-2"><label>{{$t('Link')}}</label></div>
				<div class="md:flex-2 w-full">
					<div v-for="(link, index) in links" :key="index" class="mb-3 relative">
						<BaseInputText v-model="links[index]" :class="{'pe-8': links.length > 1}"/>
						<button v-if="links.length > 1" @click="removeMoreLink(index)" class="absolute top-2 end-1"><BaseIcon name="close" class="text-red-600"/></button>
					</div>
					<small v-if="error.links" class="block p-error mb-2">{{error.links}}</small>
					<button class="block text-xs font-bold text-primary-color dark:text-dark-primary-color" @click="addMoreLink">{{$t('Add more link')}}</button>
				</div>  
			</div>	
		</div>
	</template>
	<div class="text-center mt-8">
		<BaseButton :loading="loadingSave" @click="saveProfileSettings()">{{$t('Save')}}</BaseButton>
	</div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { defineAsyncComponent } from 'vue'
import { getProfileSettings } from '@/api/setting'
import { checkPopupBodyClass } from '@/utility/index'
import Loading from '@/components/utilities/Loading.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseCalendar from '@/components/inputs/BaseCalendar.vue'
import BaseSelectLocation from '@/components/inputs/BaseSelectLocation.vue'
import { useAuthStore } from '@/store/auth'

export default {
	components: { Loading, BaseButton, BaseIcon, BaseInputText, BaseSelect, BaseTextarea, BaseCalendar, BaseSelectLocation },
    data(){
		return {
			loadingStatus: true,
			about: null,
			avatar: null,
			bio: null,
			gender_id: null,
			genders: [],
			links: null,
			name: null,
			userName: null,
			timezone: null,
			timezones: [],
			birthday: null,
			error: {
				about: null,
				bio: null,
				gender_id: null,
				links: null,
				location: null,
				name: null,
				user_name: null,
				timezone: null,
				birthday: null
			},
			loadingSave: false,
			pageName: null,
			pageUserName: null,
			location: {
				country_id: null,
				state_id: null,
				city_id: null,
				zip_code: null,
				address: null
			}
		}
	},
	computed: {
		...mapState(useAuthStore, ['user'])
	},
	mounted(){
		this.getProfileSettings()
	},
	methods:{
		...mapActions(useAuthStore, ['storeProfileSettings', 'storeProfilePageSettings']),
		async getProfileSettings(){
			if(this.user.is_page){
				this.pageName = this.user.name
				this.pageUserName = this.user.user_name
			} else {
				try {
					const response = await getProfileSettings()
					this.avatar = response.avatar
					this.userName = response.name
					this.name = response.name
					this.bio = response.bio
					this.about = response.about
					this.links = response.links.split(" ")
					this.gender_id = response.gender_id.toString()
					this.genders = window._.map(response.genders, function(key, value) {
						return { key, value }
					});
					this.timezone = response.timezone
					this.timezones = window._.map(response.timezones, function(key, value) {
						return { key, value }
					});
					this.birthday = response.birthday
					this.location.country_id = response.country_id
					this.location.state_id = response.state_id
					this.location.city_id = response.city_id
					this.location.zip_code = response.zip_code
					this.location.address = response.address
					this.loadingStatus = false
				} catch (error) {
					console.log(error)
					this.loadingStatus = false
				}
			}
		},
		async saveProfileSettings(){
			if (this.loadingSave) {
				return
			}
			this.loadingSave = true
			try {		
				if(this.user.is_page){
					await this.storeProfilePageSettings({
						name: this.pageName,
						user_name: this.pageUserName
					})
				} else {
					await this.storeProfileSettings({
						name: this.name,
						bio: this.bio,
						about: this.about,
						links: this.links.filter(link => link != '').join(' '),
						gender_id: this.gender_id == 0 ? null : this.gender_id,
						timezone: this.timezone,
						birthday: this.formatDateTime(this.birthday),
						country_id: this.location.country_id,
						state_id: this.location.state_id,
						city_id: this.location.city_id,
						zip_code: this.location.zip_code,
						location: this.location.address
					})
					this.userName = this.name
	
					// Remove empty link input
					if(this.links.filter(element => {if (Object.keys(element).length !== 0) {return true;}return false;}).length > 0){
						this.links = this.links.filter(element => {if (Object.keys(element).length !== 0) {return true;}return false;})
					}
				}	
				
				this.showSuccess(this.$t('Your changes have been saved.'))
				this.resetErrors(this.error)
			} catch (error) {
				this.handleApiErrors(this.error, error)
			} finally {
				this.loadingSave = false
			}
		},
		uploadAvatar(event){
			var input = event.target;
			// Ensure that you have a file before attempting to read it
			if (input.files && input.files[0]) {
				// create a new FileReader to read this image and convert to base64 format
				var reader = new FileReader();
				// Define a callback function to run, when FileReader finishes its job
				reader.onload = e => {
					// Note: arrow function used here, so that "this.imageData" refers to the imageData of Vue component
					// Read image as base64 and set to imageData
					// Open modal to crop cover image
					const UploadAvatarModal = defineAsyncComponent(() =>
						import('@/components/modals/UploadAvatarModal.vue')
					)
					this.$dialog.open(UploadAvatarModal, {
						data: {
							imageData: e.target.result
						},
						props:{
							header: this.$t('Crop Avatar'),
							class: 'crop-avatar-modal p-dialog-md',
							modal: true
						},
						onClose: (options) => {
							if(options.data){
								this.avatar = options.data.avatar
							}
							checkPopupBodyClass();
						}
					})
				};
				// Start the reader job - read file as a data url (base64 format)
				reader.readAsDataURL(input.files[0]);

			}
		},
		onInputClick(event){
			event.target.value = null
		},
		addMoreLink(){
			this.error.links = null
			this.links.push('')
		},
		removeMoreLink(id){
			this.links = this.links.filter((link, index) => index != id)

			if (this.links.length == 0) {
				this.links.push('')
			}
		}
    }
}
</script>