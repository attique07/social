<template>
	<a href="javascript:void(0)" @click="goToHome">
        <img :class="className" :src="logoSrc" />
    </a>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { useAppStore } from '../../store/app'
import { useActionStore } from '../../store/action'

export default {
	props: {
		className: {
			type: String,
			default: 'max-h-10 mx-auto mb-10'
		}
	},
    data(){
        return {
            siteLogo : window.siteConfig.siteLogo,
            siteLogoDarkMode : window.siteConfig.siteLogoDarkMode,
        }
    },
    computed: {
		...mapState(useAppStore, ['darkmode', 'systemMode']),
        logoSrc(){
            if (this.darkmode === 'on') {
				return this.siteLogoDarkMode
			}else if (this.darkmode === 'off') {
				return this.siteLogo
			}else{
				if (this.systemMode === 'dark') {
					return this.siteLogoDarkMode
				} else if (this.systemMode === 'light') {
					return this.siteLogo
				}
			}
            return null
        }
	},
    methods: {
        ...mapActions(useActionStore, ['doSamePage']),
        goToHome(){
            if (this.$route.name == 'home') {
                this.doSamePage({status: true})
            } else {
                this.$router.push({ name: 'home' })
            }
        }
    }
}
</script>
