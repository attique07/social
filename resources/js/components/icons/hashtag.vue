<template>
  <svg :width="size" :height="size" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M4.0874 9H20.9999" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M16.5 3.75L13.5 20.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M10.5 3.75L7.5 20.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M3 15H19.9125" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
</template>

<script>
export default {
  props: {
    size: { type: String }
  }
}
</script>