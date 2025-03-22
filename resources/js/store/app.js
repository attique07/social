import { defineStore } from 'pinia'
import localData from '../utility/localData'
import { getInit } from '../api/utility';

export const useAppStore = defineStore('app', {
    // convert to a function
    state: () => ({
        config: null,
        menus: null,
        layouts: null,
        gateways: null,
        darkmode: localData.get('darkmode','auto'),
        isMobile: false,
        errorLayout : false,
        muteVideo: true,
        volumeValue: 0,
        volumeCurrentValue: 0,
        dynamicDialogShown: false,
        currentUrl: null,
        baseZIndex: '1101',
        systemMode: null
    }),
    actions: {
        async getInit() {
            await getInit().then((response) => {
                this.config = response.data.data.config
                this.menus = response.data.data.menus
                this.layouts = response.data.data.layouts
                this.gateways = response.data.data.gateways
            });
        },
        setErrorLayout(value) {
            this.errorLayout = value
        },
        setIsMobile(payload){
            this.isMobile = payload
        },
        setDarkmode(darkmode){
            this.darkmode = darkmode
        },
        setMuteVideo(payload){
            this.muteVideo = payload
        },
        setVolumeValue(value){
            this.volumeValue = value
        },
        setVolumeCurrentValue(value){
            this.volumeCurrentValue = value
        },
        setDynamicDialogShown(payload){
            this.dynamicDialogShown = payload
        },
        setCurrentUrl(url){
            this.currentUrl = url
        },
        setBaseZIndex(){
            setTimeout(() => {
                this.baseZIndex = getComputedStyle(document.querySelector('.p-dialog-mask')).getPropertyValue("z-index")
            }, 100);
        },
        updateSystemMode() {
			this.systemMode = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
		}
    },
    persist: false
  })