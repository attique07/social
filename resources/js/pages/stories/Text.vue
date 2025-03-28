<template>
	<div class="h-full">
		<div class="flex h-full">
			<div class="flex-1 bg-cover lg:rounded-base-lg bg-center relative" :style="{'backgroundImage': 'url('+selectedBackground.photo_url+')'}">			
				<span class="absolute top-3 left-3 right-3 z-10">			
					<div class="flex gap-2 justify-between items-center">
						<div class="flex gap-2">
							<button class="flex justify-center items-center bg-white shadow-md h-8 w-8 rounded-full dark:bg-slate-800 rtl:rotate-180" @click="closeCreateStoryModal()">
								<BaseIcon name="arrow_left" />                  
							</button>						
							<button class="flex justify-center items-center bg-white shadow-md h-8 w-8 rounded-full dark:bg-slate-800" @click="toggleSongsList()">
								<BaseIcon name="music_notes"/>                  
							</button>
							<button class="flex justify-center items-center bg-white shadow-md h-8 w-8 rounded-full dark:bg-slate-800" @click="toggleAddTextarea()">
								<BaseIcon name="character"/>                  
							</button>									
						</div>
						<BaseButton :loading="loading" @click="postStory()">{{ $t('Post Story') }}</BaseButton>
					</div>
					<span v-if="selected_song" class="bg-black text-white px-2 py-1 text-xs rounded-lg inline-flex items-center gap-1 relative mt-4 z-10">
						<BaseIcon name="music_note" size="16" />
						{{ selected_song.name }}
						<button @click="removeSong" class="absolute -top-2 -end-2 bg-red-600 text-white rounded-full">
							<BaseIcon name="close" size="16" />
						</button>
					</span>
				</span>
				<div v-if="showAddTextarea" class="absolute top-14 left-3 right-3 z-10" v-click-outside="toggleAddTextarea">
					<div class="relative leading-none">
						<BaseTextarea :maxlength="1024" v-model="content" :placeholder="$t('Enter Text')" :autofocus="true" classInput="pe-10 max-h-40"/> 
						<span class="absolute bottom-3 end-3"><BaseColorPicker v-model="color" /></span>		
					</div>
				</div>
				<Songs v-if="showSongsList" class="absolute top-14 left-3 right-3 z-10" v-click-outside="toggleSongsList" @select_song="selectSong"/>
				<button v-if="storyBackgrounds.length > 0" class="absolute bottom-4 start-3 z-10" @click="toggleBackgroundsList()">
					<BaseIcon name="background" />
				</button>
				<Backgrounds v-if="showBackgroundsList" class="absolute bottom-3 left-3 right-3 z-20" v-click-outside="toggleBackgroundsList"/>
				<div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center text-main-color text-xl max-w-[80%] w-full max-h-[60%] overflow-x-hidden overflow-y-auto story-content-box">
					<ContentHtml v-if="content" :style="{ color: color}" :content="content" :limit="100" />
					<span v-else class="text-sub-color dark:text-slate-300">{{$t('Enter Text')}}</span>
				</div>
			</div>			
		</div>
    </div>
	<CloseButton @click="closeCreateStoryModal()" />
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { storeStory } from '@/api/stories'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseButton from "@/components/inputs/BaseButton.vue"
import BaseColorPicker from '@/components/inputs/BaseColorPicker.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import Backgrounds from '@/components/stories/Backgrounds.vue'
import Songs from '@/components/stories/Songs.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import { useStoriesStore } from '../../store/stories'
import { useAppStore } from '../../store/app'
import CloseButton from '@/components/utilities/CloseButton.vue'

export default {
	components: { BaseIcon, BaseButton, BaseColorPicker, BaseTextarea, Backgrounds, Songs, ContentHtml, CloseButton },
	inject: ['dialogRef'],
	data(){
		return{		
			type: 'text',
			content: null,
			color: '#ffffff',
			photo: null,
			selected_song: null,
			showSongsList: false,
			showBackgroundsList: false,
			showAddTextarea: true,
			loading: false
		}
	},
	mounted(){
		this.getStoryBackgrounds()
	},
	unmounted() {
		this.selectBackground(null)
	},
	computed:{
		...mapState(useStoriesStore, ['storyBackgrounds', 'selectedBackground']),
		...mapState(useAppStore, ['isMobile'])
	},
	watch: {
        '$route'(){
            this.dialogRef.close()
        }
    },
	methods: {
		...mapActions(useStoriesStore, ['getStoryBackgrounds', 'selectBackground']),
		toggleSongsList(){
			this.showSongsList = !this.showSongsList;
		},
		toggleAddTextarea(){
			this.showAddTextarea = !this.showAddTextarea;
		},
		selectSong(song){
			this.selected_song = song
			this.toggleSongsList()
		},
		removeSong(){
			this.selected_song = null
		},
		toggleBackgroundsList(){
			this.showBackgroundsList = !this.showBackgroundsList;
		},
		async postStory(){
			if (this.loading) {
				return
			}
			this.loading = true
			try {
				await storeStory({
					type: this.type,
					content: this.content,
					content_color: this.color,
					song_id: this.selected_song ? this.selected_song.id : 0,
					background_id: this.selectedBackground.id,
					photo: this.photo
				})
				this.showSuccess(this.$t('Your story has been posted.'))
				this.closeCreateStoryModal()
				this.$router.push({'name' : 'home'})
			} catch (error) {
				this.showError(error.error)
			} finally {
				this.loading = false
			}
		},
		closeCreateStoryModal(){
            this.dialogRef.close()
        }
	}
}
</script>