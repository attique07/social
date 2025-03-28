<template>
	<textarea :placeholder="placeholder" :rows="rows" class="text-sm w-full bg-input-background-color text-input-text-color border border-input-border-color placeholder:text-input-placeholder-color rounded-xl p-2 outline-none scrollbar-hidden resize-none dark:bg-slate-800 dark:text-white dark:border-white/10 dark:placeholder:text-slate-300" v-model="content" ref="textarea" @input="onEnterComment" @paste="onPasteContent($event)" @keydown.enter.exact.prevent="onPressEnter($event)" />
</template>

<script>
export default {
    data(){
        return{
			content : ''
        }
    },
	mounted() {
        if (this.$el.offsetParent) {
            this.resize();
        }
        if(this.autofocus){
            this.$nextTick(() => this.$refs.textarea?.focus())
        }
    },
    updated() {
        if (this.$el.offsetParent) {
            this.resize();
        }
    },
	props: {
        placeholder: {
            type: String,
            default: ''
        },
		rows: {
            type: Number,
            default: 1
        },
        autofocus: {
            type: Boolean,
			default: false
        }
    },
    methods:{
		resize() {
            const textAreaStyle = window.getComputedStyle(this.$refs.textarea);

            this.$refs.textarea.style.height = 'auto';
            this.$refs.textarea.style.height = `calc(${textAreaStyle.borderTopWidth} + ${textAreaStyle.borderBottomWidth} + ${this.$refs.textarea.scrollHeight}px)`;

            if (parseInt(textAreaStyle.height) >= parseInt(textAreaStyle.maxHeight)) {
                this.$refs.textarea.style.overflowY = 'scroll';
                this.$refs.textarea.style.height = textAreaStyle.maxHeight;
            }
        },
		onEnterComment(){
			this.emitContentChange();
		},
		addContent(content){		
			let curPos = this.$refs.textarea.selectionStart;
            this.content = this.content.slice(0, curPos) + content + this.content.slice(curPos);	
			setTimeout(() => {
				curPos = curPos + content.length;
				this.$refs.textarea.setSelectionRange(curPos, curPos);
				this.$refs.textarea.focus();
			});	
			this.emitContentChange();
            this.resize();
		},
		setContent(content) {
			this.content = content
            this.$refs.textarea.focus()
			this.emitContentChange()
		},
        blurTextarea(){
            this.$refs.textarea.blur()
        },
		emitContentChange(){
			this.$emit('content_change', this.content)
		},
		onPasteContent(event){
			this.$emit('paste_content', event)
		},
        onPressEnter(event){
            this.$emit('press_enter', event)
        },
        resetContent(){
            this.content = ''
			this.emitContentChange()
        },
        focus(){
            this.$refs.textarea.focus()
        }
    },
	emits: ['content_change', 'paste_content', 'press_enter']
}
</script>