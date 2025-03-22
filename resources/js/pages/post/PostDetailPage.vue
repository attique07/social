<template>
    <Loading v-if="loading_status" />
    <div v-else>
        <div class="feed-item bg-white border border-white p-base-2 rounded-none md:rounded-base-lg dark:bg-slate-800 dark:border-slate-800 mb-14 lg:mb-base-2">
            <Error v-if="error" class="mb-0">{{error}}</Error>
            <div v-else>
                <div class="feed-entry-wrap">
                    <PostContent :post="postInfo" @comment_click="focusForm()"/>                      
                    <div v-if="postInfo" class="feed-comment-info-comment-holder">
                        <CommentsList ref="CommentsList" :comment_id="params.comment_id" :reply_id="params.reply_id" :item="postInfo" :router_name="'post'" :type="'posts'"/>
                    </div>  
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import CommentsList from '@/components/comments/CommentsList.vue';
import Loading from '@/components/utilities/Loading.vue';
import Error from '@/components/utilities/Error.vue';
import PostContent from '@/components/posts/PostContent.vue';
import { usePostStore } from '../../store/post';
import { useAppStore } from '../../store/app';

export default {
    components: {
        CommentsList,
        Loading,
        Error,
        PostContent
    },
    props: ['data', 'params', 'position'],
    data() {    
        return {
            loading_status: true,
            error: null
		}
	},
    mounted() {
        if(this.params.id){
            this.loadPostInfo(this.params.id)
        }
    },
    unmounted(){
		this.unsetPostInfo()
	},
    computed: {
        ...mapState(usePostStore, ['postInfo']),
    },
    methods: {
        ...mapActions(usePostStore, ["getPostById", 'unsetPostInfo']),
        ...mapActions(useAppStore, ['setErrorLayout']),
        async loadPostInfo(postId){
            try {
                await this.getPostById(postId);
                this.loading_status = false
            } catch (error) {
                this.setErrorLayout(true)
                this.error = error.error.detail.id
                this.loading_status = false
            }
        },
        focusForm(){
            this.$refs.CommentsList.focusForm()
        }
    }
}
</script>