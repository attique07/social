<template>
    <div class="w-full">
        <Calendar v-model="value" @date-select="selectDate" @input="inputDate" :manualInput="manualInput" :dateFormat="dateFormat" :timeOnly="timeOnly" :placeholder="placeholder" class="w-full" :disabled="disabled" :class="{'p-invalid': error}" />
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import Calendar from 'primevue/calendar'

export default {
    components: { Calendar },
    props: {
        modelValue: {
			type: [String, Number, Date],
			default: null
		},
        disabled: {
            type: Boolean,
            default: false
        },
		error: {
			type: String,
			default: null
		},
        timeOnly: {
            type: Boolean,
            default: false
        },
        dateFormat: {
			type: String,
			default: null
		},
        placeholder: {
            type: [String, Number],
			default: null
        },
        manualInput: {
            type: Boolean,
            default: true
        },
    },
    data(){
        return {
            value: this.modelValue
        }
    },
    methods: {
        selectDate(value){
            this.$emit('update:modelValue', value);
        },
        inputDate(event){
            this.$emit('update:modelValue', event.target.value);
        }
    }
}
</script>