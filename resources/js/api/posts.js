export function getHomeFeeds(page){
    return window.axios.get(`post/home/${page}`).then((response) => {
        return response.data.data
    })
}
export function postFeed(postData){
    return window.axios.post('post/store', postData).then((response) => {
        return response.data.data;
    });
}
export function deletePost(postId){
    return window.axios.post("post/delete", postId).then((response) => {
        return response.data.data
    })
}
export function toggleBookmarkPostItem(postData){
    return window.axios.post("bookmark/store", postData).then((response) => {
        return response.data.data
    })
}
export function getUserFeeds(userId, page){
    return window.axios.get(`/post/profile/${userId}/${page}`).then((response) => {
        return response.data.data
    })
}
export function getPostById(postId){
    return window.axios.get(`/post/get/${postId}`).then((response) => {
        return response.data.data
    })
}
export function getDiscoveryFeeds(page){
    return window.axios.get(`/post/discovery/${page}`).then((response) => {
        return response.data.data
    })
}

export function getWatchFeeds(page){
    return window.axios.get(`/post/watch/${page}`).then((response) => {
        return response.data.data
    })
}

export function uploadPostImages(data){
    return window.axios.post('post/upload_photo', data).then((response) => {
        return response.data.data;
    });
}
export function deletePostItem(item){
    return window.axios.post('post/delete_item', item).then((response) => {
        return response.data.data;
    });
}
export function fetchLink(data){
    return window.axios.post('post/fetch_link', data).then((response) => {
        return response.data.data;
    });
}
export function editPost(data){
    return window.axios.post('post/store_edit', data).then((response) => {
        return response.data.data;
    }); 
}
export function uploadPostVideo(data, onUploadProgress){
    return window.axios.post('post/upload_video', data, {
        onUploadProgress: onUploadProgress
    }).then((response) => {
        return response.data.data;
    });
}
export function getMediaFeeds(page){
    return window.axios.get(`/post/media/${page}`).then((response) => {
        return response.data.data
    })
}
export function getProfileMediaFeeds(userId, page){
    return window.axios.get(`/post/profile_media/${userId}/${page}`).then((response) => {
        return response.data.data
    })
}
export function uploadPostFiles(fileData){
    return window.axios.post('post/upload_file', fileData).then((response) => {
        return response.data.data;
    });
}