<template>
    <div>
        <div class="mb-base-2">
            <div class="mb-1"><label>{{ $t('Username') }}</label></div>
            <AutoComplete v-model="selectedUser" multiple :disabled="disableAutoComplete" optionLabel="name" :suggestions="userMentionList" @item-select="handleItemSelect" @item-unselect="handleItemUnselect" @complete="suggestUsersMention" :emptySearchMessage="$t('No users found')" class="w-full">
                <template #option="slotProps">
                    <AutoSuggestUserItem :user="slotProps.option"/>
                </template>
            </AutoComplete>
        </div>
        <BaseButton @click="handleClickAddModerator()" class="w-full">{{$t('Add')}}</BaseButton>
    </div>
</template>
<script>
import BaseButton from '@/components/inputs/BaseButton.vue'
import AutoComplete from 'primevue/autocomplete'
import AutoSuggestUserItem from '@/components/user/AutoSuggestUserItem.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import { getSuggestUsersForAdmin, storeAdminPage } from '@/api/page'
import { checkPopupBodyClass } from '@/utility/index'

export default {
    components: { BaseButton, AutoComplete, AutoSuggestUserItem },
    data(){
        return{
            disableAutoComplete: false,
            userMentionList: null,
            selectedUser: [],
            fundAmount: null
        }
    },
    inject: ['dialogRef'],
    methods:{
        handleItemSelect(){
            this.disableAutoComplete = true
        },
        handleItemUnselect(){
            this.disableAutoComplete = false
        },
        async suggestUsersMention(event) {
            try {
				const response = await getSuggestUsersForAdmin(event.query);
                this.userMentionList = response;
			} catch (error) {
				this.userMentionList = [];
			}  
        },
        async handleClickAddModerator(){   
            if(this.selectedUser.length === 0){
                this.showError(this.$t('Please select user to add moderator.'));
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
                                const response = await storeAdminPage({
                                    id: this.selectedUser[0].id,
                                    password: data.password
                                })
                                this.showSuccess(this.$t('Add Moderator Successfully.'))
                                passwordDialog.close()
                                checkPopupBodyClass();
                                this.dialogRef.close({adminInfo: response})
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