export function getUserSubscription(status, page){
    return window.axios.get(`subscription/get/${status}/${page}`).then((response) => {
        return response.data.data;
    });
}
export function getSubscriptionDetail(subscriptionId){
    return window.axios.get(`subscription/get_detail/${subscriptionId}`).then((response) => {
        return response.data.data
    })
}
export function cancelSubscription(subscriptionId){
    return window.axios.post('subscription/store_cancel', {id: subscriptionId}).then((response) => {
        return response.data.data
    })
}
export function resumeSubscription(subscriptionId){
    return window.axios.post('subscription/store_resume', {id: subscriptionId}).then((response) => {
        return response.data.data
    })
}
export function getTransactions(id, page){
    return window.axios.get(`subscription/get_transaction/${id}/${page}`).then((response) => {
        return response.data.data;
    });
}