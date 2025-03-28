<template>
    <form @submit.prevent="sendFundToUser">
        <div class="mb-base-2">
            <div class="mb-1"><label>{{ $t('Username') }}</label></div>
            <AutoComplete v-model="selectedUser" multiple :disabled="disableAutoComplete" optionLabel="name" :suggestions="userMentionList" @item-select="handleItemSelect" @item-unselect="handleItemUnselect" @complete="suggestUsersMention" :emptySearchMessage="$t('No users found')" class="w-full">
                <template #option="slotProps">
                    <AutoSuggestUserItem :user="slotProps.option"/>
                </template>
            </AutoComplete>
        </div>
        <div class="mb-base-2">
            <div class="mb-1"><label>{{ $t('Amount') }}</label></div>
            <BaseInputNumber v-model="fundAmount" :placeholder="$t('Value')" class="mb-base-2" />
            <p class="text-base-xs mb-base-2">{{ $t('Availables') + ': ' + exchangeTokenCurrency(user.wallet_balance) }}</p>
        </div>
        <div class="text-end">
            <BaseButton :disabled="!fundAmount">{{$t('Send')}}</BaseButton>
        </div>
    </form>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import BaseInputNumber from '@/components/inputs/BaseInputNumber.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import AutoComplete from 'primevue/autocomplete';
import PasswordModal from '@/components/modals/PasswordModal.vue'
import AutoSuggestUserItem from '@/components/user/AutoSuggestUserItem.vue'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { useUtilitiesStore } from '@/store/utilities'
import { checkPopupBodyClass } from '@/utility/index'
import { getSuggestUsersToSendFund } from '@/api/wallet'
import { storeSend } from '@/api/wallet'

export default {
    components: { BaseInputNumber, BaseButton, AutoComplete, AutoSuggestUserItem },
    data(){
        return{
            disableAutoComplete: false,
            userMentionList: null,
            selectedUser: [],
            fundAmount: null,
            onlySearchUser: false,
            notSearchMyself: true
        }
    },
    inject: ['dialogRef'],
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config'])
    },
    methods:{
        ...mapActions(useAuthStore, ['sendFund']),
        ...mapActions(useUtilitiesStore, ['pingNotification']),
        handleItemSelect(){
            this.disableAutoComplete = true
        },
        handleItemUnselect(){
            this.disableAutoComplete = false
        },
        async suggestUsersMention(event) {
            try {
				const response = await getSuggestUsersToSendFund(event.query);
                this.userMentionList = response;
			} catch (error) {
                this.userMentionList = [];
			}  
        },
        sendFundToUser(){
            if(this.selectedUser.length === 0){
                this.showError(this.$t('Please select user to send fund.'));
                return;
            }
            const passwordDialog = this.$dialog.open(PasswordModal, {
				props: {
					header: this.$t('Enter Password'),
					class: 'password-modal',
					modal: true,
					dismissableMask: true,
					draggable: false
				},
                emits: {
					onConfirm: async (data) => {
						if (data.password) {
							try {
                                await storeSend({
                                    amount: this.fundAmount,
                                    id: this.selectedUser[0].id,
                                    password: data.password,
                                })
                                this.pingNotification()
                                this.dialogRef.close()
                                passwordDialog.close()
                                checkPopupBodyClass();
                                this.showSuccess(this.$t('Send fund successfully.'))
                            } catch (error) {
                                this.showError(error.error)
                                passwordDialog.close()
                                checkPopupBodyClass();
                            }
						}
					}
				}
			})
        }
    }
}
</script>