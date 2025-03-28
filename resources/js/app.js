import './bootstrap';

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import { i18n } from './i18n'
import { getAsset } from './utility/index'
import vClickOutside from "click-outside-vue3"
import PrimeVue from 'primevue/config';
import ConfirmationService from 'primevue/confirmationservice';
import DialogService from 'primevue/dialogservice';
import ToastService from 'primevue/toastservice';
import Tooltip from 'primevue/tooltip';
import _ from 'lodash';
import 'primeicons/primeicons.css'
import 'primevue/resources/primevue.min.css'
import { VueReCaptcha } from 'vue-recaptcha-v3'
import { checkPopupBodyClass } from '@/utility'
import RequireLoginModal from '@/components/modals/RequireLoginModal.vue'
import PermissionModal from '@/components/modals/PermissionModal.vue';
import { createPinia } from 'pinia'
import piniaPluginPersistedstate from 'pinia-plugin-persistedstate'
import { useAuthStore } from './store/auth';
import { useAppStore} from './store/app';
import { useLangStore} from './store/lang';
import localData from './utility/localData'
import 'vueperslides/dist/vueperslides.css'

const pinia = createPinia()
pinia.use(piniaPluginPersistedstate)

const app = createApp({
    extends: App,
    async beforeCreate() {   
        const authStore = useAuthStore()
        await useAppStore().getInit()
        await authStore.me()			        
        var locale = localData.get('locale', window.siteConfig.languageDefault);        
        if (authStore.authenticated) {
            locale = authStore.user.language;
            localData.set('locale', locale)
        }
        await useLangStore().init({ i18n, locale })
    }
});

app.use(router)
app.use(i18n)
app.use(pinia)
app.use(vClickOutside)
app.use(PrimeVue)
app.use(ConfirmationService)
app.use(DialogService)
app.use(ToastService)
app.directive('tooltip', Tooltip)
if(window.siteConfig.recapchaPublicKey){
    app.use(VueReCaptcha, { siteKey: window.siteConfig.recapchaPublicKey, loaderOptions:{autoHideBadge: true }})
}
app.mount('#app')
app.config.globalProperties.$filters = {
    numberShortener(number, singularText, pluralText, digits = 1){
        // format number
        let shortNumber
        if(number >= 1e12){
            shortNumber = +(number / 1e12).toFixed(digits) + "T"
        }else if(number >= 1e9){
            shortNumber = +(number / 1e9).toFixed(digits) + "B"
        }else if(number >= 1e6){
            shortNumber = +(number / 1e6).toFixed(digits) + "M"
        }else if(number >= 1e3){
            shortNumber = +(number / 1e3).toFixed(digits) + "K"
        }else{
            shortNumber = number
        }        
        // render content        
        if(number == 1){
            return _.replace(singularText, '[number]', shortNumber)
        }else{
            return _.replace(pluralText, '[number]', shortNumber)
        }
    },
    textTranslate(variable, content){
        return _.replace(content, '%s', variable)
    }
}

app.config.globalProperties.showSuccess = function(content, life_time = 5000) {
    this.$toast.add({severity:'success', summary: null, detail: content, life: life_time})
}

app.config.globalProperties.showError = function(content, life_time = 5000) {
    var message = content
    if (typeof content == 'object') {
        message = content.message
        if (content.code == 'error_validate') {
            var key = _.findKey(content.detail, function() { return  true })
            message = content.detail[key]
        }

        if (['authenticated', 'permission'].includes(content.code)) {
            return
        }
    }

    if (message) {
        this.$toast.add({severity:'error', summary: null, detail: message, life: life_time})
    }
}

app.config.globalProperties.resetErrors = function(errorObject) {
    Object.keys(errorObject).forEach((key) => {
        errorObject[key] = null;
    });
}

app.config.globalProperties.handleApiErrors = function(errorObject, error) {
    this.resetErrors(errorObject);
    if (error.error.code === 'error_validate') {
        Object.keys(errorObject).forEach((key) => {
            errorObject[key] = error.error.detail[key] || null;
        });
    } else if (error.error.code === 'error_message') {
        this.showError(error.error);
    }
    setTimeout(() => {
        const appStore = useAppStore()
        if(appStore.isMobile){
            const errorElement = document.getElementsByClassName("p-invalid")[0];
            let position = errorElement.getBoundingClientRect();
            window.scrollTo(position.left, position.top + window.scrollY - 100);
        }
    }, 100);
}

app.config.globalProperties.asset = function(path) {
    return getAsset(path)
}

app.config.globalProperties.showRequireLogin = function(){
    this.$dialog.open(RequireLoginModal, {
        props:{
            showHeader: false,
            class: 'p-dialog-no-header',
            modal: true,
            dismissableMask: true,
            draggable: false
        },
        onClose: () => {
            checkPopupBodyClass();
        }
    });
}

app.config.globalProperties.checkUploadedData = function(data, type){
    const appStore = useAppStore()
    if (typeof type === 'undefined') {
        type = 'photo'
    }

    var extensions = appStore.config.photoExtensionSupport
    switch (type) {
        case 'video':
            extensions = appStore.config.videoExtensionSupport
            break;
        case 'csv':
            extensions = appStore.config.csvExtensionSupport
            break;
        case 'user_verify':
            extensions = appStore.config.userVerifyExtensionSupport
            break;
        case 'chat':
            extensions = appStore.config.chatExtensionSupport
            break;
        case 'post':
            extensions = appStore.config.postUploadExtensionSupport
            break;
    }
    const fileExtension = data.name.split('.').pop().toLowerCase()

    if (!extensions.split(',').includes(fileExtension)){
        this.$toast.add({severity:'error', summary: null, detail: this.$t('The {name} has an invalid extension. Valid extension(s): {extension}.', {name:data.name,extension: extensions}), life: 5000})
        return false;
    }
    if(data.size > appStore.config.maxUploadSize * 1024){
        this.$toast.add({severity:'error', summary: null, detail: this.$t('The {name} is too large, maximum file size is {size}Kb.', {name:data.name,size: appStore.config.maxUploadSize}), life: 5000})
        return false;
    }

    return true
}
app.config.globalProperties.formatDateTime = function(str) {
    if (! str) {
        return str
    }
    
    var date = new Date(str),
        mnth = ("0" + (date.getMonth() + 1)).slice(-2),
        day = ("0" + date.getDate()).slice(-2);
    return [date.getFullYear(), mnth, day].join("-");
}
app.config.globalProperties.formatTimeOnly = function(str) {
    if (! str) {
        return str
    }
    
    var date = new Date(str)
    return [("0" + date.getHours()).slice(-2), ("0" + date.getMinutes()).slice(-2)].join(":");
}
app.config.globalProperties.formattedAmount = function(amount) {
    return amount ? parseFloat(String(amount).replace(/,/g, '')).toFixed(2) : 0
}
app.config.globalProperties.exchangeCurrency = function(amount) {
    const appStore = useAppStore()
    return Math.round((this.formattedAmount(amount) / appStore.config.wallet.exchangeRate) * 100) / 100 + ' ' + appStore.config.currency.code
}
app.config.globalProperties.exchangeTokenCurrency = function(amount) {
    const appStore = useAppStore()
    return this.formattedAmount(amount) + ' ' + appStore.config.wallet.tokenName
}
app.config.globalProperties.aspectRatioImage = function(image){
    const imageFrameHeight = image.height,
    imageFrameWidth = image.width,
    imageFrameAspectRatio = imageFrameHeight / imageFrameWidth
    if(imageFrameHeight === 0 || imageFrameWidth === 0){
        return 1
    } else {
        if(imageFrameAspectRatio > 1.5){
            return 1.5
        } else if(imageFrameAspectRatio < 0.35){
            return 0.35
        } else{
            return imageFrameAspectRatio
        }
    }
}
app.config.globalProperties.aspectRatioVideo = function(video){
    if (video.width > video.height) {
        return 'horizontal';
    } else {
        return 'vertical';
    }
}
app.config.globalProperties.checkPermission = function(permission){
    const authStore = useAuthStore()
    if (!window._.has(authStore.user.permissions, permission) || !authStore.user.permissions[permission]) {
        this.showPermissionPopup(permission)
        return false
    }
    return true
}
app.config.globalProperties.showPermissionPopup = function(permission, message = ''){
    this.$dialog.open(PermissionModal, {
        props:{
            header: this.$t('Permission'),
            modal: true,
            draggable: false
        },
        data: {
            permission: permission,
            message: message
        },
        onClose: () => {
            checkPopupBodyClass();
        }
    })
}
app.config.globalProperties.parseDate = function(dateStr){
    if(dateStr){
        var parts = dateStr.split('-');
        var year = 2000 + parseInt(parts[0], 10);
        var month = parseInt(parts[1], 10) - 1;
        var day = parseInt(parts[2], 10);
        return new Date(year, month, day);
    } else {
        return 0
    }
}
app.config.globalProperties.runningDate = function(startDate, endDate){
    return startDate && endDate && Math.floor((this.parseDate(this.formatDateTime(endDate)) - this.parseDate(this.formatDateTime(startDate)) ) / (1000 * 60 * 60 * 24)) + 1
}
app.config.globalProperties.getOthersMemberInRoom = function(members){
    const authStore = useAuthStore()
    return members.filter(member => member.id !== authStore.user.id)
}
app.config.globalProperties.showMiniChatBox = function(){
    const appStore = useAppStore()
    return !appStore.isMobile && !['chat', 'chat_requests'].includes(this.$route.name)
}