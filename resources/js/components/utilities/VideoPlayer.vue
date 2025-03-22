<template>
    <div class="flex flex-col justify-center" ref="videoContainer">
		<video class="max-h-full" ref="video" preload="metadata" :id="`videoMain-${video.file.id}-${videoKey}`" :poster="video.thumb.url" :autoplay="user.video_auto_play && autoPlay" :muted="volume === 0" playsinline>
			<source :src="video.file.url" type="video/mp4">         
		</video>
        <Transition name="fade">
            <div v-show="showControl" class="absolute left-0 bottom-0 right-0 bg-black-gradient z-10">
                <div class="flex items-center justify-between rtl:flex-row-reverse text-white p-base-2">
                    <div class="flex items-center gap-4 rtl:flex-row-reverse">
                        <button @click="togglePlay">
                            <BaseIcon v-if="play" name="pause_song"/>
                            <BaseIcon v-else name="play_song" />
                        </button>
                        <div class="text-sm">
                            <time ref="timeElapsed">00:00</time>
                            <span> / </span>
                            <time ref="duration">00:00</time>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 rtl:flex-row-reverse">
                        <div class="volume-controls relative leading-none">
                            <button @click="toggleMute">
                                <BaseIcon v-if="muteVideo" name="mute" />
                                <BaseIcon v-else name="unmute" />
                            </button>
                            <div class="volume-controls-slider">
                                <Slider v-model="volume" :step="0.01" :min="0" :max="1" @change="updateVolume" ref="volume" orientation="vertical" />
                            </div>
                        </div>
                        <button @click="toggleFullScreen">
                            <BaseIcon v-if="fullscreen" name="fullscreen_exit" />
                            <BaseIcon v-else name="fullscreen" />
                        </button>
                    </div>
                </div>
                <div class="video-progress bg-gray-300 flex rtl:flex-row-reverse">
                    <div class="progress-bar" ref="progressBar" :style="{width: `${progressWidth}%`}">
                        <div class="progress-bar-dot"></div>
                    </div>
                    <input class="seek" ref="seek" value="0" min="0" type="range" step="1">
                    <div class="seek-tooltip" ref="seekTooltip">00:00</div>
                </div>
            </div>
        </Transition>
        <button class="absolute inset-0 flex justify-center items-center outline-none w-full" @click="togglePlay">
            <div v-if="!play" class="flex justify-center items-center w-12 h-12 bg-black-trans-6 text-white rounded-full">
                <BaseIcon name="play_song" class="leading-none"/>
            </div>
        </button>
	</div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Slider from 'primevue/slider';
import { useAppStore } from '../../store/app'
import { useAuthStore } from '../../store/auth';
import {uuidv4} from '@/utility/index'

export default {
    props: {
        video: {
            type: Object,
            default: null
        },
        autoPlay:{
            type: Boolean,
            default: false
        }
    },
    components: { BaseIcon, Slider },
    data() {    
        return {
            play: false,
            fullscreen: false,
            showControl: false,
            progressWidth: 0,
            volume: 0,
            videoKey: uuidv4()
		}
	},
    mounted() {
        this.$refs.video.addEventListener('play', this.updatePlayButton);
        this.$refs.video.addEventListener('pause', this.updatePlayButton);
        this.$refs.video.addEventListener('loadedmetadata', this.initializeVideo);
        this.$refs.video.addEventListener('timeupdate', this.updateTimeElapsed);
        this.$refs.video.addEventListener('timeupdate', this.updateProgress);
        this.$refs.video.addEventListener('volumechange', this.updateVolumeIcon);
        this.$refs.video.addEventListener('click', this.togglePlay);

        this.$refs.seek.addEventListener('mousemove', this.updateSeekTooltip);
        this.$refs.seek.addEventListener('input', this.skipAhead);

        this.$refs.videoContainer.addEventListener('fullscreenchange', this.updateFullscreenButton);
        this.$refs.videoContainer.addEventListener('webkitfullscreenchange', this.updateFullscreenButton);
        this.$refs.videoContainer.addEventListener('mouseenter', this.showControls);
        this.$refs.videoContainer.addEventListener('mouseleave', this.hideControls);

        this.volume = this.volumeCurrentValue ? this.volumeCurrentValue : this.volumeValue
        this.$refs.video.volume = this.volume;
    },
    watch: {
        volumeValue(){
            this.volume = this.volumeValue
        },
        volumeCurrentValue(){
            this.volume = this.volumeCurrentValue
        },
        volume(){
            this.$refs.video.volume = this.volume;
        },
        dynamicDialogShown(newValue){
            if(newValue){
                this.$refs.video.pause();
            }
        }
    },
	computed: {
        ...mapState(useAppStore, ['muteVideo', 'volumeValue', 'volumeCurrentValue', 'dynamicDialogShown']),
        ...mapState(useAuthStore, ['user']),
	},
    methods: {
        ...mapActions(useAppStore, ['setMuteVideo', 'setVolumeValue', 'setVolumeCurrentValue']),
        togglePlay() {
            if (this.$refs.video.paused || this.$refs.video.ended) {
                document.querySelectorAll('.video_feed_activity_content video').forEach(vid => vid.pause());
                this.$refs.video.play();
            } else {
                this.$refs.video.pause();
            }
        },
        playVideo(){
            document.querySelectorAll('.video_feed_activity_content video').forEach(vid => {
                if(vid.id === this.$refs.video.id){
                    vid.play();
                }else{
                    vid.pause()
                }
            });
        },
        pauseVideo(){
            this.$refs.video.pause();
        },
        updatePlayButton() {
            if (this.$refs.video?.paused) {
                this.play = false
            } else {
                this.play = true
            }
        },
        formatTime(timeInSeconds) {
            const result = new Date(timeInSeconds * 1000).toISOString().substr(11, 8);

            return {
                minutes: result.substr(3, 2),
                seconds: result.substr(6, 2),
            };
        },
        initializeVideo() {
            if (! this.$refs.video) {
                return
            }
            const videoDuration = Math.round(this.$refs.video.duration);
            this.$refs.seek.setAttribute('max', videoDuration);
            this.$refs.progressBar.setAttribute('max', videoDuration);
            const time = this.formatTime(videoDuration);
            this.$refs.duration.innerText = `${time.minutes}:${time.seconds}`;
            this.$refs.duration.setAttribute('datetime', `${time.minutes}m ${time.seconds}s`);
        },
        updateTimeElapsed() {
            if(this.$refs.video){
                const time = this.formatTime(Math.round(this.$refs.video.currentTime));
                this.$refs.timeElapsed.innerText = `${time.minutes}:${time.seconds}`;
                this.$refs.timeElapsed.setAttribute('datetime', `${time.minutes}m ${time.seconds}s`);
            }
        },
        updateProgress() {
            if(this.$refs.video){
                this.$refs.seek.value = Math.floor(this.$refs.video.currentTime);
                this.progressWidth = Math.floor(this.$refs.video.currentTime * 100 / this.$refs.video.duration);
            }
        }, 
        updateSeekTooltip(event) {
            const skipTo = Math.round(
                (event.offsetX / event.target.clientWidth) *
                parseInt(event.target.getAttribute('max'), 10)
            );
            this.$refs.seek.setAttribute('data-seek', skipTo);
            const t = this.formatTime(skipTo);
            this.$refs.seekTooltip.textContent = `${t.minutes}:${t.seconds}`;
            const rect = this.$refs.video.getBoundingClientRect();
            this.$refs.seekTooltip.style.left = `${event.pageX - rect.left}px`;
        },
        skipAhead(event) {
            const skipTo = event.target.dataset.seek
                ? event.target.dataset.seek
                : event.target.value;
            this.$refs.video.currentTime = skipTo;
            this.$refs.progressBar.value = skipTo;
            this.$refs.seek.value = skipTo;
        },
        updateVolume() {
            if (this.$refs.video.muted) {
                this.$refs.video.muted = false;
            }
			this.setVolumeCurrentValue(this.volume);
            if(this.$refs.video.volume === 0){
                this.setMuteVideo(true)
            } else  {
                this.setMuteVideo(false)
            }
        },
        updateVolumeIcon() {
            if(!this.muteVideo){
                this.setMuteVideo(false)
            }
            else if (this.muteVideo || this.$refs.video.volume === 0) {
                this.setMuteVideo(true)
            }
        },
        toggleMute() {
            this.setMuteVideo(!this.muteVideo)
            if (this.muteVideo) {
                this.setVolumeValue(0)
            }else if(this.volumeCurrentValue){
                this.setVolumeValue(this.volumeCurrentValue)
            }else{
				this.setVolumeValue(1)
			}
        },
        toggleFullScreen() {
            if (document.fullscreenElement || document.webkitIsFullScreen) {
                if (document.exitFullscreen) {
                    document.exitFullscreen()
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen()
                }
            } else {
                let elem = this.$refs.videoContainer
                if (elem.requestFullscreen) {
                    elem.requestFullscreen();
                } else if (elem.webkitRequestFullscreen) { / Safari /
                    elem.webkitRequestFullscreen();
                } else if (elem.msRequestFullscreen) { / IE11 /
                    elem.msRequestFullscreen();
                }
            }
            if(this.user.video_auto_play){
                setTimeout(() => {
                    this.playVideo()
                }, 100);
            }
        },
        updateFullscreenButton() {
            if (document.fullscreenElement || document.webkitIsFullScreen) {
                this.fullscreen = true
            } else {
                this.fullscreen = false
            }
        },
        hideControls() {
            this.showControl = false
        },
        showControls() {
            this.showControl = true
        }
    }
}
</script>