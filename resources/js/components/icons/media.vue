<template>
    <svg :width="size" :height="size" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M11.375 4.99866H5.375V10.9987H11.375V4.99866Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M20.375 4.99866H14.375V10.9987H20.375V4.99866Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M11.375 13.9987H5.375V19.9987H11.375V13.9987Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M20.375 13.9987H14.375V19.9987H20.375V13.9987Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    </svg>
</template>

<script>
export default {
  props: {
    size: { type: String }
  }
}
</script>