<template>
    <input type="number" inputmode="decimal" step="0.01" class="p-inputtext p-component w-full" :value="modelValue" @input="onInput">
</template>

<script>
export default {
    props: {
        modelValue: {
			type: [String, Number],
			default: null
		}
    },
    methods:{
        onInput(event) {
            this.$emit('update:modelValue', event.target.value);
        }
    },
    emits: ['update:modelValue']
}
</script>