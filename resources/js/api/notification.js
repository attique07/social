export function getUserNotifications(page){
    return window.axios.get(`notification/get/${page}`).then((response) => {
        return response.data.data;
    });
}
export function toggleEnableNotification(itemData){
    return window.axios.post('notification/store_enable', itemData).then((response) => {
        return response.data.data
    })
}
export function markSeenNotification(notificationData){
    return window.axios.post('notification/store_seen', notificationData).then((response) => {
        return response.data.data
    })
}