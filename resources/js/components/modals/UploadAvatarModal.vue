<template>
    <div>
        <cropper ref="cropper"
        :stencil-props="{
            aspectRatio: 1/1
        }"
        :min-width="200"
        :src="image" />
        <div class="text-end mt-base-2">
            <BaseButton :loading="loading" @click="cropAvatar">{{$t('Crop Avatar Image')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import { mapActions } from 'pinia';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import BaseButton from '@/components/inputs/BaseButton.vue';
import constant from '@/utility/constant';
import { useAuthStore } from '../../store/auth';

export default {
    components :{ Cropper, BaseButton },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            image: this.dialogRef.data.imageData,
            loading: false
        }
    },
    methods:{
        ...mapActions(useAuthStore, ['uploadAvatarPicture']),
        async cropAvatar() {
			let { canvas }  =  this.$refs.cropper.getResult()
			if (canvas) {
                const formData = new FormData();
				var canvasTmp
                if (canvas.width > constant.AVATAR_WIDTH) {
                    canvasTmp = document.createElement('canvas')
                    var ctx = canvasTmp.getContext("2d")
                    canvasTmp.width = constant.AVATAR_WIDTH;
                    canvasTmp.height = constant.AVATAR_HEIGHT;
                    ctx.drawImage(canvas,0,0,canvas.width,canvas.height,0,0,canvasTmp.width,canvasTmp.height)
                } else {
                    canvasTmp = canvas
                }
        
				canvasTmp.toBlob(async blob => {
					let file = new File([blob], "Avatar.png", { type: "image/png" })
					formData.append('file', file);
                    if (this.loading) {
                        return
                    }
                    this.loading = true
                    try {
                        await this.uploadAvatarPicture(formData).then((response) => {
                            this.dialogRef.close(response);
                        })
                    } catch (error) {
                        this.showError(error.error)
                    } finally {
                        this.loading = false
                    }
				})
			}
        }
    }
}
</script>