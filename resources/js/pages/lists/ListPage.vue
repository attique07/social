<template>
	<Loading v-if="loadingPages"/>
	<template v-else>
		<UserPagesList :pages-list="pagesList"/>
		<InfiniteLoading @infinite="loadMorePages">	
			<template #spinner>
				<Loading />
			</template>
			<template #complete><span></span></template>
		</InfiniteLoading>
	</template>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import Loading from '@/components/utilities/Loading.vue';
import UserPagesList from '@/components/lists/UserPagesList.vue';
import InfiniteLoading from 'v3-infinite-loading'
import { usePagesStore } from '@/store/page'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'

export default {
    components: { Loading, UserPagesList, InfiniteLoading },
    data() {
		return {
			currentPage: 1
		}
	},
    computed: {
		...mapState(useAuthStore, ['user']),
		...mapState(usePagesStore, ['loadingPages', 'pagesList'])
    },
    mounted(){
		if (this.user.is_page) {
            this.setErrorLayout(true)
        } else {
			this.getMyPagesList(this.currentPage)
        }
    },
    unmounted(){
        this.unsetPagesList()
    },
    methods: {
		...mapActions(useAppStore, ['setErrorLayout']),
        ...mapActions(usePagesStore, ['getMyPagesList', 'unsetPagesList']),
        loadMorePages($state) {
			this.getMyPagesList(++this.currentPage).then((response) => {
				if(response.items.length === 0){
					$state.complete()
				}else{
					$state.loaded()
				}
			})	
		}
    }
}
</script>