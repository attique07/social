import { defineStore } from 'pinia'
import { getHomeFeeds, postFeed, deletePost, toggleBookmarkPostItem, getUserFeeds, getPostById, getDiscoveryFeeds, getWatchFeeds, editPost, getMediaFeeds, getProfileMediaFeeds } from '../api/posts'
import { getReviewsPage, storeReviewPage } from '../api/page'
import { toggleLikeItem } from '../api/likes';
import { toggleEnableNotification } from '../api/notification'
import { getSearchResults } from '../api/search';
import { getBookmarkItem } from '../api/bookmark';

export const usePostStore = defineStore('post', {
    // convert to a function
    state: () => ({
        loadingPostsList: true,
        postsList: [],
        postInfo: null,
        postsListType: null,
        isMyProfilePage: false
    }),
    actions: {
        doPushPostsList({postsList, page, post_type}){
            if (page == 1) {
                this.postsList = [];
            }
            let pushedPostsList = window._.map(postsList, function(postsListItem) { 
                return window._.extend({}, postsListItem, {commentsList: []});
            });
            this.postsList = window._.concat(this.postsList, pushedPostsList)
            this.postsListType = post_type
        },
        doAddPost(newPost){
            // Add New Post at Home Page
            if(newPost.id && this.postsListType === 'home'){
                newPost = window._.extend({}, newPost, {commentsList: []})
                this.postsList.unshift(newPost);
            }

            // Add New Post at Profile Page
            if(newPost.id && this.postsListType === 'user' && this.isMyProfilePage){
                newPost = window._.extend({}, newPost, {commentsList: []})
                this.postsList.unshift(newPost);
            }
            // Add New Post at Review Page
            if(newPost.id && this.postsListType === 'review'){
                newPost = window._.extend({}, newPost, {commentsList: []})
                this.postsList.unshift(newPost);
            }
		},
        doDeletePostItem(postData){
            if(this.postsList && window._.find(this.postsList, {id: postData.id})){
                this.postsList = this.postsList.filter(post => post.id !== postData.id)
            }else if(this.postInfo && this.postInfo.id === postData.id){
                this.postInfo = null
            }              
        },
        doToggleEnableNotification(postData){
            let post = this.postsList.find(post => post.id === postData.subject_id);

            if (typeof post === 'undefined') {
                post = this.postInfo;
            }

            if(post.id == postData.subject_id){
                if(postData.action === 'add'){
                    post.enable_notify = true;
                }else if(postData.action === 'remove'){
                    post.enable_notify = false;
                }      
            }
        },
        doToggleLikePostItem(postData){
            let post = this.postsList.find(post => post.id === postData.subject_id);

            if (typeof post === 'undefined') {
                post = this.postInfo;
            }

            if(post.id == postData.subject_id){
                if(postData.action === 'add'){
                    post.is_liked = true;
                    post.like_count++;
                }else if(postData.action === 'remove'){
                    post.is_liked = false;
                    post.like_count--;
                }      
            }
        },
        doToggleBookmarkPostItem(postData){
            let post = this.postsList.find(post => post.id === postData.subject_id);

            if (typeof post === 'undefined') {
                post = this.postInfo;
            }

            if(post.id == postData.subject_id){
                if(postData.action === 'add'){
                    post.is_bookmarked = true;
                }else if(postData.action === 'remove'){
                    post.is_bookmarked = false;
                }      
            }
        },
        doUpdatePost(postData){
            let post = this.postsList.find(post => post.id === postData.id);

            if (typeof post === 'undefined') {
                post = this.postInfo;
            }
            if(post.id == postData.id){
                post.content = postData.content
                post.mentions = postData.mentions
                post.isEdited = true      
            }
        },
        unsetPostsList(){
            this.postsList = [];
            this.postsListType = null,
            this.loadingPostsList = true
        },
        unsetPostInfo(){
            this.postInfo = null
        },
        addCommentToPost(commentData, postId){
            this.postsList.map(post => {
				if (post.id === postId){
                    post.commentsList.unshift(commentData)
                    post.comment_count++        
                }
				return post
			})
        },
        async getHomePostsList(page){
            try {
				const response = await getHomeFeeds(page)
                this.doPushPostsList({postsList: response, page, post_type: 'home'})
                this.loadingPostsList = false
                return response
			} catch (error) {
				console.log(error)
                this.loadingPostsList = false
			}
        },
        async getUserPostsList(userId, page){
            try {
                const response = await getUserFeeds(userId, page)
                this.doPushPostsList({postsList: response, page, post_type: 'user'})
                this.loadingPostsList = false
                return response
			} catch (error) {
				console.log(error)
                this.loadingPostsList = false
			}
        },
        async getSearchPostsList({search_type, query, type, page}){
            try {
                const response = await getSearchResults(search_type, query, type, page)
                this.doPushPostsList({postsList: response, page, post_type: 'search'})
                this.loadingPostsList = false
                return response
			} catch (error) {
				console.log(error)
                this.loadingPostsList = false
			}
        },
        async getDiscoveryPostsList(page){
            try {
				const response = await getDiscoveryFeeds(page)
                this.doPushPostsList({postsList: response, page, post_type: 'discovery'})
                this.loadingPostsList = false
                return response
			} catch (error) {
				console.log(error)
                this.loadingPostsList = false
			}
        },
        async getWatchPostsList(page){
            try {
				const response = await getWatchFeeds(page)
                this.doPushPostsList({postsList: response, page, post_type: 'discovery'})
                this.loadingPostsList = false
                return response
			} catch (error) {
				console.log(error)
                this.loadingPostsList = false
			}
        },
        async getBookmarkedPostsList(page){
            try {
				const response = await getBookmarkItem('posts', page)
                this.doPushPostsList({postsList: response.items, page, post_type: 'bookmark'})
                this.loadingPostsList = false
                return response.items
			} catch (error) {
				console.log(error)
                this.loadingPostsList = false
			}
        },
        async postNewFeed(newPost){
            const response = await postFeed(newPost)
            this.doAddPost(response)
            return response
        },
        async deletePostItem(postData){
            try {
				await deletePost(postData)
				this.doDeletePostItem(postData)
			} catch (error) {
				console.log(error)
			}
        },
        async toggleEnableNotificationPostItem(postData){
            try {
				await toggleEnableNotification(postData)
				this.doToggleEnableNotification(postData)
			} catch (error) {
				console.log(error)
			}
        },
        async toggleLikePostItem(postData){
            await toggleLikeItem(postData)
            this.doToggleLikePostItem(postData)
        },
        async toggleBookmarkPostItem(postData){
            await toggleBookmarkPostItem(postData)
            this.doToggleBookmarkPostItem(postData)
        },
        async getPostById(postId){
            const response = await getPostById(postId)
            this.postInfo = response
            return response
        },
        async editPost(postData){
            const response = await editPost(postData)
            this.doUpdatePost(response)
        },
        setIsMyProfilePage(payload){
            this.isMyProfilePage = payload
        },
        async getMediaPostsList(page){
            try {
				const response = await getMediaFeeds(page)
                this.doPushPostsList({postsList: response, page, post_type: 'media'})
                this.loadingPostsList = false
                return response
			} catch (error) {
                this.loadingPostsList = false
			}
        },
        async getProfileMediaPostsList(userId, page){
            try {
				const response = await getProfileMediaFeeds(userId, page)
                this.doPushPostsList({postsList: response, page, post_type: 'media'})
                this.loadingPostsList = false
                return response
			} catch (error) {
                this.loadingPostsList = false
			}
        },
        async getReviewsPageList(pageId, page){
            try {
				const response = await getReviewsPage(pageId, page)
                this.doPushPostsList({postsList: response.items, page, post_type: 'review'})
                this.loadingPostsList = false
                return response.items
			} catch (error) {
				console.log(error)
                this.loadingPostsList = false
			}
        },
        async postNewReviewFeed(newPost){
            const response = await storeReviewPage(newPost)
            this.doAddPost(response)
            return response
        },
    },
    persist: false
  })