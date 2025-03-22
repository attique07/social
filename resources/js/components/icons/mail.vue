<template>
    <svg :width="size" :height="size" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M7 9.49866L12 12.9987L17 9.49866" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        <path d="M2 17.4987V7.49866C2 6.96822 2.21071 6.45952 2.58579 6.08444C2.96086 5.70937 3.46957 5.49866 4 5.49866H20C20.5304 5.49866 21.0391 5.70937 21.4142 6.08444C21.7893 6.45952 22 6.96822 22 7.49866V17.4987C22 18.0291 21.7893 18.5378 21.4142 18.9129C21.0391 19.2879 20.5304 19.4987 20 19.4987H4C3.46957 19.4987 2.96086 19.2879 2.58579 18.9129C2.21071 18.5378 2 18.0291 2 17.4987Z" stroke="currentColor" stroke-width="1.5"/>
    </svg>
</template>

<script>
export default {
  props: {
    size: { type: String }
  }
}
</script>