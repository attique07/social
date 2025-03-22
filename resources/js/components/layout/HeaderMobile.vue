<template>
    <div class="header-mobile flex items-center justify-between gap-base-2 lg:hidden fixed inset-x-0 top-0 bg-white px-4 py-3 z-[999] dark:bg-slate-800">
		<Avatar :user="user" :activePopover="false" :router="false" class="w-7" id="sidebarMobileToggle"/>
		<Logo :className="'max-w-[200px] max-h-6'" class="block" />
		<button @click="openSearchForm">
			<BaseIcon name="search" class="header-mobile-icon text-main-color dark:text-white" />
		</button>   
        <Teleport to="body">
			<Transition name="fade">
				<div class="global-search-header-mobile lg:hidden" v-if="showSearchForm"> 
					<BaseIcon name="close" id="closeMobileSearchBtn" @click="closeSearchForm"/>
					<GlobalSearch :autofocus="true"/>      
				</div>
			</Transition>
        </Teleport>
    </div>
</template>
<script>
import { mapState} from 'pinia'
import { useAuthStore } from '../../store/auth'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import GlobalSearch from '@/components/layout/GlobalSearch.vue';
import Logo from '@/components/utilities/Logo.vue'
import Avatar from '@/components/user/Avatar.vue'

export default {
    name: "HeaderMobile",
    components: { BaseIcon, GlobalSearch, Logo, Avatar },
	data(){
		return{
			showSearchForm: false
		}
	},
	computed:{
        ...mapState(useAuthStore, ['user'])
    },
	watch: {
        '$route'() {
            this.showSearchForm = false
        }
    },
    mounted() {
        var sidebarToggleBtn = document.getElementById('sidebarMobileToggle');
		var sidebarMenu = document.getElementById('sidebarUserMenu');
		var targetBackdrop = document.getElementById('backdropModal');
		var closeSidebarBtn = document.getElementById('closeSidebarBtn');
		var addNewPostBtn = document.getElementById('addNewPostBtn');

		// Open Sidebar Menu Mobile
		if (sidebarToggleBtn) {
			sidebarToggleBtn.addEventListener('click', function () {    
				openSidebar()
			});
		}

		// Close Sidebar Menu Mobile
		if (targetBackdrop) {
			targetBackdrop.addEventListener('click', function () {
				closeSidebar()
			})
		}
		if (closeSidebarBtn) {
			closeSidebarBtn.addEventListener('click', function () {
				closeSidebar()
			})
		}
		if(addNewPostBtn){
			addNewPostBtn.addEventListener('click', function () {
				closeSidebar()
			});		
		}

		function openSidebar(){
			sidebarMenu.classList.add('show');
			targetBackdrop.classList.add('show');
			document.body.classList.add('overflow-hidden')
		}

		function closeSidebar(){
			targetBackdrop.classList.remove('show');
			sidebarMenu.classList.remove('show');
			document.body.classList.remove('overflow-hidden')
		}
    },
	methods:{
		openSearchForm(){
			this.showSearchForm = true
		},
		closeSearchForm(){
			this.showSearchForm = false
		}
	}
}
</script>