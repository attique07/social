<template>
  <svg :width="size" :height="size" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M4.5 3.75L19.5 20.25" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M9 18V18.75C9 19.5456 9.31607 20.3087 9.87868 20.8713C10.4413 21.4339 11.2044 21.75 12 21.75C12.7956 21.75 13.5587 21.4339 14.1213 20.8713C14.6839 20.3087 15 19.5456 15 18.75V18" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M17.4563 18H4.59379C4.46237 17.9995 4.33337 17.9645 4.21972 17.8985C4.10607 17.8325 4.01177 17.7378 3.94626 17.6239C3.88074 17.5099 3.84632 17.3808 3.84644 17.2493C3.84655 17.1179 3.8812 16.9888 3.94692 16.875C4.56567 15.8063 5.26879 13.8562 5.26879 10.5V9.75C5.26802 8.37786 5.68315 7.0377 6.45942 5.90625" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
    <path d="M8.67188 3.88125C9.69819 3.29186 10.8634 2.9876 12.0469 3C15.7594 3.02812 18.7313 6.1125 18.7313 9.83437V10.5C18.7313 13.0031 19.125 14.7187 19.575 15.8719" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
</template>

<script>
export default {
  props: {
    size: { type: String }
  }
}
</script>