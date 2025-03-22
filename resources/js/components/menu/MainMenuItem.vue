<template>
    <li class="sidebar-user-menu-list-item relative">
        <!-- Check Logout Button -->
        <div v-if="item.alias == 'logout'" class="sidebar-user-menu-list-item-wrap flex items-center gap-5 w-full text-main-color transition-colors lg:hover:text-primary-color dark:text-gray-300 dark:lg:hover:text-dark-primary-color" @click="logout()">
            <span class="flex-1 p-3 cursor-pointer">
                <div class="flex items-center gap-5">
                    <div v-if="item.icon" :style="{'-webkit-mask': 'url('+item.icon+')', '-webkit-mask-size': 'contain'}" class="sidebar-main-menu-item-icon w-6 h-6 bg-main-color dark:bg-gray-300"></div>
                    <span class="flex-1 font-semibold">{{item.name}}</span>
                </div>                 
            </span>
        </div>
        <template v-else>
            <!-- Check is header menu -->
            <div v-if="item.is_header" ref="menu_header" class="sidebar-user-menu-list-item-wrap flex items-center gap-5 w-full text-main-color transition-colors lg:hover:text-primary-color dark:text-gray-300 dark:lg:hover:text-dark-primary-color" @click.prevent="toggleMenuMore">
                <span class="flex-1 p-3 cursor-pointer">
                    <div class="flex items-center gap-5">
                        <div v-if="item.icon" :style="{'-webkit-mask': 'url('+item.icon+')', '-webkit-mask-size': 'contain'}" class="sidebar-main-menu-item-icon w-6 h-6 bg-main-color dark:bg-gray-300"></div>
                        <span class="flex-1 font-semibold">{{item.name}}</span>
                    </div> 
                </span>
                <BaseIcon v-if="item.childs.length > 0" name="more_vertical" class="sidebar-main-menu-item-icon-more"/>
            </div>
            <template v-else>
                <!-- Check Menu Item Internal Link -->
                <div v-if="item.type == 'internal'" :class="{'router-link-exact-active': convertLink(item.url, item.is_core) === convertLink(currentRouter, item.is_core)}" class="sidebar-user-menu-list-item-wrap flex items-center gap-5 w-full text-main-color transition-colors lg:hover:text-primary-color dark:text-gray-300 dark:lg:hover:text-dark-primary-color">
                    <a :href="baseUrl + item.url" :target="item.is_new_tab ? '_blank': ''" @click="clickInternalLink($event, item.url, item.is_new_tab)" class="flex-1 p-3 text-main-color dark:text-gray-300 transition-colors">
                        <div class="flex items-center gap-5">
                            <div v-if="item.icon" :style="{'-webkit-mask': 'url('+item.icon+')', '-webkit-mask-size': 'contain'}" class="sidebar-main-menu-item-icon w-6 h-6 bg-main-color dark:bg-gray-300"></div>
                            <span class="flex-1 font-semibold">{{item.name}}</span>
                        </div>                 
                    </a>
                </div>
                <!-- Check Menu Item Outbound Link -->
                <div v-else :class="{'router-link-exact-active': item.url === baseUrl + currentRouter}" class="sidebar-user-menu-list-item-wrap flex items-center gap-5 w-full text-main-color transition-colors lg:hover:text-primary-color dark:text-gray-300 dark:lg:hover:text-dark-primary-color">
                    <a :href="item.url" :target="item.is_new_tab ? '_blank': ''" class="flex-1 p-3 text-main-color dark:text-gray-300 transition-colors">
                        <div class="flex items-center gap-5">
                            <div v-if="item.icon" :style="{'-webkit-mask': 'url('+item.icon+')', '-webkit-mask-size': 'contain'}" class="sidebar-main-menu-item-icon w-6 h-6 bg-main-color dark:bg-gray-300"></div>
                            <span class="flex-1 font-semibold">{{item.name}}</span>
                        </div> 
                    </a>
                </div>
            </template>
        </template>
        <div v-if="showMoreMenu && item.childs.length > 0"  class="absolute left-0 z-10 w-full cursor-pointer" :class="showTop ? 'top-0 pt-11' : 'bottom-0 pb-11'" @click="toggleMenuMore" v-click-outside="closeMenuMore">
            <div ref="menu_child_dropdown" class="bg-sidebar shadow-sidebar-more border-base-lg w-full p-base-2 rounded-base  dark:bg-slate-800 ">
                <ul class="sidebar-user-menu-child-list">
                    <li v-for="(item_child, index) in item.childs" :key="index">
                        <div v-if="item_child.alias == 'logout'" class="sidebar-user-menu-list-item-wrap flex items-center gap-5 w-full text-main-color transition-colors lg:hover:text-primary-color dark:text-gray-300 dark:lg:hover:text-dark-primary-color" @click="logout()">
                            <a href="javascript:void(0);" class="flex-1 p-base-2 text-main-color dark:text-white transition-colors">
                                <div class="flex items-center gap-5">
                                    <div v-if="item_child.icon" :style="{'-webkit-mask': 'url('+item_child.icon+')', '-webkit-mask-size': 'contain'}" class="sidebar-main-menu-item-icon w-6 h-6 bg-main-color dark:bg-gray-300"></div>
                                    {{item_child.name}}
                                </div>                 
                            </a>
                        </div>
                        <template v-else>
                            <!-- Check is header menu -->
                            <div v-if="item_child.is_header" class="sidebar-user-menu-list-item-wrap flex items-center gap-5 w-full text-main-color transition-colors lg:hover:text-primary-color dark:text-gray-300 dark:lg:hover:text-dark-primary-color">
                                <a href="javascript:void(0);" class="flex-1 p-base-2 text-main-color dark:text-white transition-colors">
                                    <div class="flex items-center">
                                        <div v-if="item_child.icon" :style="{'-webkit-mask': 'url('+item_child.icon+')', '-webkit-mask-size': 'contain'}" class="sidebar-main-menu-item-icon w-6 h-6 bg-main-color me-5 dark:bg-gray-300"></div>
                                        {{item_child.name}}
                                    </div> 
                                </a>
                            </div>
                            <template v-else>
                                <!-- Check Menu Item Internal Link -->
                                <div v-if="item_child.type == 'internal'" :class="{'router-link-exact-active': convertLink(item_child.url, item_child.is_core) === convertLink(currentRouter, item_child.is_core)}" class="sidebar-user-menu-list-item-wrap flex items-center gap-5 w-full text-main-color transition-colors lg:hover:text-primary-color dark:text-gray-300 dark:lg:hover:text-dark-primary-color">
                                    <a :href="baseUrl + item_child.url" :target="item_child.is_new_tab ? '_blank': ''" @click.self="clickInternalLink($event, item_child.url, item.is_new_tab)" class="flex-1 p-base-2 text-main-color dark:text-white transition-colors">
                                        <div class="flex items-center">
                                            <div v-if="item_child.icon" :style="{'-webkit-mask': 'url('+item_child.icon+')', '-webkit-mask-size': 'contain'}" class="sidebar-main-menu-item-icon w-6 h-6 bg-main-color me-5 dark:bg-gray-300"></div>
                                            {{item_child.name}}
                                        </div>                 
                                    </a>
                                </div>
                                <!-- Check Menu Item Outbound Link -->
                                <div v-else :class="{'router-link-exact-active': item_child.url === baseUrl + currentRouter}" class="sidebar-user-menu-list-item-wrap flex items-center gap-5 w-full text-main-color transition-colors lg:hover:text-primary-color dark:text-gray-300 dark:lg:hover:text-dark-primary-color">
                                    <a :href="item_child.url" :target="item_child.is_new_tab ? '_blank': ''" class="flex-1 p-base-2 text-main-color dark:text-white transition-colors">
                                        <div class="flex items-center">
                                            <div v-if="item_child.icon" :style="{'-webkit-mask': 'url('+item_child.icon+')', '-webkit-mask-size': 'contain'}" class="sidebar-main-menu-item-icon w-6 h-6 bg-main-color me-5 dark:bg-gray-300"></div>
                                            {{item_child.name}}
                                        </div> 
                                    </a>
                                </div>
                            </template>                  
                        </template>
                    </li>                                             
                </ul>
            </div>
        </div>
    </li>
</template>

<script>
import { mapState } from 'pinia'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import { useAppStore } from '../../store/app'
import { useAuthStore } from '../../store/auth'

export default {
    props: ['item'],
    components: { BaseIcon },
    data(){
        return{
            showMoreMenu: false,
            currentRouter: this.$router.currentRoute.value.fullPath,
            showTop: false
        }
    },
    computed:{
        ...mapState(useAppStore, ['config', 'currentUrl']),
        baseUrl(){
            return window.siteConfig.siteUrl
        }
    },
    watch: {
        '$route'(){
            this.currentRouter = this.$router.currentRoute.value.fullPath
        },
        currentUrl(){
            this.currentRouter = this.currentUrl
        }
    },
    methods: {
        async logout() {
            try {
                await useAuthStore().logout();
                window.location.href = window.siteConfig.siteUrl;
            } catch (error) {
                console.log(error);
            }
        },
        toggleMenuMore(){		
			this.showMoreMenu = !this.showMoreMenu;
            this.updateMenuChildPosition()
		},
		closeMenuMore() {
			if (this.showMoreMenu) this.showMoreMenu = false;
		},
        clickInternalLink(e, link, isNewTab){
            if (! isNewTab) {
                e.preventDefault()
                this.$router.push(link);
            }
        },
        convertLink(link, isCore){
            if(isCore){ // relative links
                const pathname = (new URL(link, this.baseUrl)).pathname;
                return pathname.split("/")[1]
            } else{ // absolute links
                return link.split(this.currentRouter)[1]
            }
        },
        updateMenuChildPosition () {
            this.$nextTick(() => {
                const menuHeaderRect = this.$refs.menu_header?.getBoundingClientRect()
                const menuChildDropdown = this.$refs.menu_child_dropdown?.getBoundingClientRect()
                
                if((window.innerHeight - menuHeaderRect?.top) > menuChildDropdown?.height){
                    this.showTop = true
                }else{
                    this.showTop = false
                }
            })
		}
    }
}
</script>