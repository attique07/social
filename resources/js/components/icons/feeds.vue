<template>
  <svg :width="size" :height="size" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M5.25 4.5C9.02857 4.50248 12.6517 6.00462 15.3235 8.67647C17.9954 11.3483 19.4975 14.9714 19.5 18.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M5.25 11.25C6.23491 11.25 7.21018 11.444 8.12013 11.8209C9.03007 12.1978 9.85686 12.7503 10.5533 13.4467C11.2497 14.1431 11.8022 14.9699 12.1791 15.8799C12.556 16.7898 12.75 17.7651 12.75 18.75" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M5.25 19.875C5.87132 19.875 6.375 19.3713 6.375 18.75C6.375 18.1287 5.87132 17.625 5.25 17.625C4.62868 17.625 4.125 18.1287 4.125 18.75C4.125 19.3713 4.62868 19.875 5.25 19.875Z" fill="currentColor"/>
  </svg>
</template>

<script>
export default {
  props: {
    size: { type: String }
  }
}
</script>