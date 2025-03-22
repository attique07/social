<template>
     <div class="relative">
        <label :for="`imagesUpload${key}`" class="cursor-pointer">
            <BaseIcon name="camera" />
        </label>
        <input type="file" @change="uploadChatImages($event)" class="absolute top-0 left-0 w-0 h-0 text-base-none opacity-0 cursor-pointer" :id="`imagesUpload${key}`" :name="`imagesUpload${key}`" :ref="`imagesUpload${key}`" multiple accept="image/*" />
    </div>
</template>

<script>
import { uuidv4 } from '@/utility/index'
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
    components: { BaseIcon },
    data(){
        return {
            key: uuidv4()
        }
    },
    methods: {
        uploadChatImages(event) {
            this.$emit('upload', event);
        },
        open(){
            this.$refs[`imagesUpload${this.key}`].click()
        },
        reset(){
            this.$refs[`imagesUpload${this.key}`].value = null
        }
    },
    emits: ['upload']
}
</script>