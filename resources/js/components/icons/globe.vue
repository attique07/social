<template>
  <svg :width="size" :height="size" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M3.51562 9H20.4844" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M3.51562 15H20.4844" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M12 20.7563C14.0711 20.7563 15.75 16.836 15.75 12C15.75 7.16408 14.0711 3.24377 12 3.24377C9.92893 3.24377 8.25 7.16408 8.25 12C8.25 16.836 9.92893 20.7563 12 20.7563Z" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
</template>

<script>
export default {
  props: {
    size: { type: String }
  }
}
</script>