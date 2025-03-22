<template>
	<template v-if="config != null && user != null && locale_loaded">
		<template v-if="authenticated"> <!-- Logged In -->
			<template v-if="$route.name === 'first_login' || $route.name === 'email_confirm'">
				<div id="mainContent" class="flex flex-col justify-between items-center bg-main-bg px-base-2 py-5 min-h-screen w-full dark:bg-dark-body dark:text-white h-screen">
					<div>&nbsp;</div>
					<div class="max-w-md w-full">
						<Logo />
						<router-view :key="$route.path" />
					</div>
					<FooterSite class="text-center" />
				</div>
			</template>
			<div v-else class="flex items-stretch w-full pt-16 pb-16 lg:py-0">
				<HeaderMobile />
				<SidebarMenu />
				<div id="mainContent" class="main-content w-full lg:w-main-content">
					<HeaderSite />
					<template v-if="error">
						<div class="max-w-container w-full mx-auto p-0 md:px-6 md:py-base-2">
							<NotFound />
						</div>
					</template>
					<template v-else>
						<Container v-if="layouts.header.center.length > 0" class="px-0 md:px-6 py-0">
							<div v-for="(dataHeader, index) in layouts.header.center" :key="index">
								<component :is="loadComponentData(dataHeader)" :data="dataHeader"></component>
							</div>
						</Container>
						<Container class="p-0 md:px-6 md:py-base-2">
							<router-view :key="$route.path" />
						</Container>
						<Container v-if="layouts.footer.center.length > 0" class="px-0 pt-0 pb-0 md:px-6 md:pb-6">
							<div v-for="(dataFooter, index) in layouts.footer.center" :key="index">
								<component :is="loadComponentData(dataFooter)" :data="dataFooter"></component>
							</div>
						</Container>
					</template>
				</div>
				<FooterMobile />
			</div>
			<SwitchPagePopover/>
			<ChatBubble v-if="!isMobile && !['chat', 'chat_requests'].includes($route.name)"/>
		</template>
		<template v-else> <!-- Non Logged In -->
			<template v-if="this.checkMobileApp()">
				<router-view :key="$route.path" />
			</template>
			<template v-else>
				<HeaderNonLogin
					v-if="!(['login', 'signup', 'recover'].includes($route.name)) && !this.checkOfflinePage()" />
				<template v-if="error">
					<div class="max-w-container w-full mx-auto p-0">
						<NotFound />
					</div>
				</template>
				<div v-else class="max-w-container w-full mx-auto p-0">
					<div class="flex flex-col justify-between items-center p-4 lg:px-0"
						:class="['login', 'signup', 'recover'].includes($route.name) ? 'h-screen' : 'h-screen-non-login'">
						<div v-if="['login', 'signup', 'recover'].includes($route.name) || this.checkOfflinePage()">&nbsp;
						</div>
						<div class="w-full">
							<router-view :key="$route.path" />
						</div>
						<div v-if="this.checkOfflinePage()">&nbsp;</div>
						<FooterSite v-if="!this.checkOfflinePage()" class="text-center" />
					</div>
				</div>
			</template>
		</template>
	</template>
	<LoadingPage v-else></LoadingPage>
	<ConfirmDialog class="confirm-dialog-shaun" :draggable="false"/>
	<DynamicDialog />
	<Toast :position="user?.rtl ? 'top-left' : 'top-right'">
		<template #message="toast">
			<div class="p-toast-message-icon">
				<BaseIcon v-if="toast.message.severity === 'success'" name="success" class="text-base-green" />
				<BaseIcon v-else-if="toast.message.severity === 'error'" name="error" />
			</div>
			<div class="p-toast-message-text">
				<span class="p-toast-summary">{{ toast.message.summary }}</span>
				<div class="p-toast-detail" v-html="toast.message.detail" />
			</div>
		</template>
	</Toast>
	<div class="backdrop-modal" id="backdropModal"></div>
	<div v-if="config != null && config.cookieEnable && config.cookieLink != '' && !cookieHide"
		class="cookies-warning bg-white p-base-7 fixed bottom-0 inset-x-0 z-[1000] dark:bg-slate-800 dark:text-white">
		<h5 class="text-base-lg font-extrabold mb-base-2">{{ $t('Cookies on') }} {{ config.siteName }}</h5>
		<p>{{ $t('This site uses cookies to store your information on your computer.') }}</p>
		<div class="flex gap-base-2 mt-base-2">
			<BaseButton :href="config.cookieLink" target="_blank">{{ $t('Read more') }}</BaseButton>
			<BaseButton @click=acceptCookie>{{ $t('Accept') }}</BaseButton>
		</div>
	</div>
	<div v-if="!appSuggestHide && config && appLink" class="fixed bg-white inset-x-0 bottom-0  z-[999] p-4 dark:bg-slate-800 dark:text-white">
		<div class="max-w-sm mx-auto">
			<div class="flex gap-3 mb-3 items-start">
				<img :src="config.appSuggestPhoto" class="w-24" alt="">
				<div>
					<div class="text-lg font-bold">{{ $t('Check out our mobile apps!') }}</div>
					<div>{{ $t('Our "White label" Android and iOS apps are available on App Stores. Click to install and try out on your phone.') }}</div>
				</div>
			</div>
			<div class="flex justify-center gap-base-2">
				<BaseButton type="outlined" @click="denyAppSuggest">
					{{ $t('No Thanks') }}
				</BaseButton>
				<BaseButton :href="appLink">
					{{ $t('Get The App') }}
				</BaseButton>
			</div>
		</div>
	</div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { defineAsyncComponent } from "vue";
import { markSeenNotification } from '@/api/notification';
import { checkOffline, checkAdvancedUpload, checkMobileApp , checkAndroidWeb, checkiOSWeb } from '@/utility/index'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { useLangStore } from '@/store/lang'
import { useChatStore } from '@/store/chat'
import { useUtilitiesStore } from '@/store/utilities'
import SidebarMenu from '@/components/layout/SidebarMenu.vue'
import HeaderSite from '@/components/layout/HeaderSite.vue'
import FooterMobile from '@/components/layout/FooterMobile.vue';
import FooterSite from '@/components/layout/FooterSite.vue';
import HeaderMobile from '@/components/layout/HeaderMobile.vue';
import HeaderNonLogin from '@/components/layout/HeaderNonLogin.vue';
import Container from '@/components/article/Container.vue';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import LoadingPage from '@/pages/LoadingPage.vue';
import Constant from '@/utility/constant'
import localData from '@/utility/localData';
import Echo from 'laravel-echo';
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
import DynamicDialog from 'primevue/dynamicdialog';
import BaseButton from '@/components/inputs/BaseButton.vue'
import NotFound from '@/components/utilities/NotFound.vue';
import Logo from '@/components/utilities/Logo.vue';
import SwitchPagePopover from '@/components/popover/SwitchPagePopover.vue';
import ChatBubble from '@/components/layout/ChatBubble.vue';
var dragTimer = null

export default {
	components: {
		SidebarMenu,
		HeaderSite,
		FooterMobile,
		FooterSite,
		HeaderMobile,
		HeaderNonLogin,
		Container,
		BaseIcon,
		LoadingPage,
		Toast,
		ConfirmDialog,
		DynamicDialog,
		BaseButton,
		NotFound,
		Logo,
		SwitchPagePopover,
		ChatBubble
	},
	data() {
		return {
			cookieHide: localData.get('cookie_hide', false),
			error: false,
			appSuggestHide: localData.get('app_suggest_hide', false)
		};
	},
	mounted() {
		// Scroll Desktop
		var viewport = function () {
			var e = window, a = 'inner';
			if (!('innerWidth' in window)) {
				a = 'client';
				e = document.documentElement || document.body;
			}
			return e[a + 'Width'];
		}
		document.addEventListener('scroll', function () {
			if (viewport() > 769) {
				var _top = window.pageYOffset
				if (_top > 24) {
					document.body.classList.add('documentScrolling');
				} else {
					document.body.classList.remove('documentScrolling');
				}
			}
		})

		// set IsMobile
		if (viewport() > 769) {
			this.setIsMobile(false)
		} else {
			this.setIsMobile(true)
		}
		// set IsMobile when resize window
		window.addEventListener("resize", () => {
			if (viewport() > 769) {
				this.setIsMobile(false)
			} else {
				this.setIsMobile(true)
			}
		})

		if (checkAdvancedUpload) {
			var self = this
			var arrayEvent = ['dragover', 'dragenter', 'dragleave', 'drop']
			arrayEvent.forEach(function (event) {
				window.addEventListener(event, function (e) {
					// preventing the unwanted behaviours
					e.preventDefault();
					e.stopPropagation();
				});
			});

			['dragover', 'drop'].forEach(function (event) {
				window.addEventListener(event, function (e) {
					self.setEventDragDrop(e);
					window.clearTimeout(dragTimer);
				});
			});

			window.addEventListener('dragleave', function (e) {		
				dragTimer = window.setTimeout(function() {
					self.setEventDragDrop(e)
				}, 25);
			});
		}
		window.document.addEventListener("visibilitychange", () => {
			if (! window.document.hidden) {
				this.me()
			}
		});
		
		this.updateSystemMode();
		window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', this.updateSystemMode);
	},
	beforeUnmount() {
		window.matchMedia('(prefers-color-scheme: dark)').removeEventListener('change', this.updateSystemMode);
	},
	unmounted() {
		if (this.authenticated) {
			clearInterval(this.interval)
		}
	},
	watch: {
		'$route'(){
			this.checkPermission()
        },
		async user(user) {
			if (user.id > 0 && this.authenticated) {
				//check user verify email
				if (this.config.emailVerify && user.email_verified == 0) {
					if (this.$router.currentRoute.value.name != 'email_confirm') {
						this.$router.push({ 'name': 'email_confirm' })
					}					
				} else if (user.already_setup_login == 0) { //Check user first login		
					this.$router.push({ 'name': 'first_login' })
				}

				this.setDarkmode(user.darkmode)

				//broadcast
				if (this.config.broadcastEnable) {
					window.Echo = new Echo({
						broadcaster: 'pusher',
						key: this.config.broadcastKey,
						cluster: this.config.broadcastCluster,
						wsHost: this.config.broadcastHost,
						wsPort: this.config.broadcastPort,
						wssPort: this.config.broadcastPort,
						forceTLS: this.config.broadcastForceTLS,
						enabledTransports: ['ws', 'wss'],
						authEndpoint: window.siteConfig.siteUrl + '/broadcasting/auth'
					});
					window.Echo.private(Constant.CHANNEL_USER + user.id).
						listen('.Chat.MessageSentEvent', (data) => {
							this.setChatMessageSentEvent(data)
							this.setChatCount(data.chat_count)
						}).listen('.Chat.RoomSeenSelfEvent', (data) => {
							this.setChatCount(data.chat_count)
							this.setChatRoomSeenSelfEvent(data)
						}).listen('.Chat.RoomSeenEvent', (data) => {
							this.setRoomSeenEvent(data)
						}).listen('.Chat.RoomUnreadEvent', (data) => {
							this.setChatCount(data.chat_count)
							this.setRoomUnreadEvent(data)
						}).listen('.Chat.RoomAcceptEvent', (data) => {
							this.setRoomAcceptEvent(data)
						}).listen('.Chat.MessageUnsentEvent', (data) => {
							this.setChatMessageUnsentEvent(data)
						}
						);
				}

				// Ping
				this.pingNotification()
				this.interval = setInterval(() => {
					this.pingNotification()
				}, this.config.notificationInterval)

				// Mark read notify
				var urlParams = new URLSearchParams(window.location.search);

				if (urlParams.get('notify_id')) {
					markSeenNotification({
						id: urlParams.get('notify_id')
					})
				}
			} else if (user.id == 0 && this.authenticated) {
				await useAuthStore().logout();
				window.location.href = window.siteConfig.siteUrl;
			}

			// Check RTL
			if (user.rtl) {
				document.body.dir = 'rtl'
			} else {
				document.body.dir = 'ltr'
			}

			this.checkPermission()
		},
		darkmode() {
			if (this.darkmode === 'on') {
				document.documentElement.classList.add('dark')
			}else if (this.darkmode === 'off') {
				document.documentElement.classList.remove('dark')
			}else{
				if (this.systemMode === 'dark') {
					document.documentElement.classList.add('dark')
				} else if (this.systemMode === 'light') {
					document.documentElement.classList.remove('dark')
				}
			}
		},
		errorLayout(error) {
			this.error = error
		},
		systemMode() {
			if (this.darkmode === 'auto'){
				if (this.systemMode === 'dark') {
					document.documentElement.classList.add('dark')
				} else if (this.systemMode === 'light') {
					document.documentElement.classList.remove('dark')
				}
			}
		},
		locale_loaded() {
			if (this.locale_loaded) {
				if (localData.get('inactive')) {
					localData.remove('inactive');
					this.showError(this.$t('Your account has been inactive.'))
				}
			}
		}
	},
	computed: {
		...mapState(useAuthStore, ['user', 'authenticated']),
		...mapState(useAppStore, ['config', 'darkmode', 'systemMode', 'layouts', 'errorLayout', 'isMobile']),
		...mapState(useLangStore, ['locale_loaded']),
		appLink() {
			if (checkAndroidWeb()) {
				return this.config.androidLink;
			}

			if (checkiOSWeb()) {
				return this.config.iosLink;
			}

			return null;
		}
	},
	methods: {
		...mapActions(useUtilitiesStore, ['pingNotification', 'setChatCount', 'setEventDragDrop']),
		...mapActions(useChatStore, ['setChatMessageSentEvent', 'setChatRoomSeenSelfEvent', 'setRoomSeenEvent', 'setRoomUnreadEvent', 'setRoomAcceptEvent', 'setChatMessageUnsentEvent']),
		...mapActions(useAppStore, ['setIsMobile', 'setDarkmode', 'updateSystemMode']),
		...mapActions(useAuthStore, ['me']),
		loadComponentData(data) {
			if (data.type == 'component') {
				return defineAsyncComponent(() => import(`./components/widgets/${data.package}/${data.component}.vue`))
			} else {
				return defineAsyncComponent(() => import(`./pages/${this.$route.name}/${data.component}.vue`))
			}
		},
		acceptCookie() {
			this.cookieHide = true;
			localData.set('cookie_hide', true)
		},
		checkOfflinePage() {
			return checkOffline()
		},
		checkMobileApp() {
			return checkMobileApp()
		},
		checkPermission(){
			if (this.user && this.$router.currentRoute.value.meta.permission) {
				let permission = this.$router.currentRoute.value.meta.permission;
				if (! window._.has(this.user.permissions, permission) || ! this.user.permissions[permission]) {
					this.$router.push({ 'name': 'permission' })
				}
			}
		},
		denyAppSuggest() {
			this.appSuggestHide = true;
			localData.set('app_suggest_hide', true)
		},
	}
};
</script>
