<template>
    <div>
        <cropper ref="cropper"
        :stencil-props="{
            aspectRatio: 5.2/2
        }"
        :min-height="300"
        :src="image" />
        <div class="text-end mt-base-2">
            <BaseButton :loading="loading" @click="cropCover">{{$t('Crop Cover Image')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import { uploadCoverProfilePicture } from '@/api/user'
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';
import BaseButton from '@/components/inputs/BaseButton.vue';
import constant from '@/utility/constant';

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
        async cropCover() {
			let { canvas }  =  this.$refs.cropper.getResult()
			if (canvas) {
				const formData = new FormData();
                var canvasTmp
                if (canvas.width > constant.COVER_WIDTH) {
                    canvasTmp = document.createElement('canvas')
                    var ctx = canvasTmp.getContext("2d")
                    canvasTmp.width = constant.COVER_WIDTH;
                    canvasTmp.height = constant.COVER_HEIGHT;
                    ctx.drawImage(canvas,0,0,canvas.width,canvas.height,0,0,canvasTmp.width,canvasTmp.height)
                } else {
                    canvasTmp = canvas
                }

				canvasTmp.toBlob(async blob => {
					let file = new File([blob], "Cover.png", { type: "image/png" })
					formData.append('file', file);
                    if (this.loading) {
                        return
                    }
                    this.loading = true
					try {
						const response = await uploadCoverProfilePicture(formData)
                        this.dialogRef.close(response);
					} catch (error) {
						this.showError(error.error)
					} finally {
                        this.loading = false
                    }
				});
			}
        }
    }
}
</script>