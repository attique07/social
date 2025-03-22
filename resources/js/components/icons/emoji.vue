<template>
  <svg :width="size" :height="size" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M12 21C16.9706 21 21 16.9706 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12C3 16.9706 7.02944 21 12 21Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M8.625 11.25C9.24632 11.25 9.75 10.7463 9.75 10.125C9.75 9.50368 9.24632 9 8.625 9C8.00368 9 7.5 9.50368 7.5 10.125C7.5 10.7463 8.00368 11.25 8.625 11.25Z" fill="currentColor"/>
    <path d="M15.375 11.25C15.9963 11.25 16.5 10.7463 16.5 10.125C16.5 9.50368 15.9963 9 15.375 9C14.7537 9 14.25 9.50368 14.25 10.125C14.25 10.7463 14.7537 11.25 15.375 11.25Z" fill="currentColor"/>
    <path d="M15.8996 14.25C15.5031 14.9331 14.9342 15.5 14.2498 15.8941C13.5653 16.2882 12.7894 16.4956 11.9996 16.4956C11.2098 16.4956 10.4339 16.2882 9.74945 15.8941C9.06501 15.5 8.49609 14.9331 8.09961 14.25" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
</template>
<script>
export default {
  props: {
    size: { type: String }
  }
}
</script>