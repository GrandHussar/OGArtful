<!-- <template>
  <div class="heart-container">
    <transition name="fade" mode="out-in">
      <svg v-if="name === 'heart'" key="filled" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-heart-fill" viewBox="0 0 16 16">
        <path fill-rule="evenodd" d="M8 1.314C12.438-3.248 23.534 4.735 8 15-7.534 4.736 3.562-3.248 8 1.314"/>
      </svg>
      <svg v-else key="outlined" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-heart" viewBox="0 0 16 16">
        <path d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143q.09.083.176.171a3 3 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15"/>
      </svg>
    </transition>
  </div>
</template>

<script>
export default {
  props: {
    name: {
      type: String,
      required: true,
      validator: (value) => ['heart', 'heart-outline'].includes(value)
    }
  }
}
</script>

<style scoped>
.heart-container {
  display: inline-block; /* To ensure it doesn't affect layout */
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.1s ease;
  transform: scale(1.1);
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style> -->
<template>
  <div class="heart-container">
    <transition name="fade" mode="out-in">
      <q-btn
        v-if="name === 'heart'"
        :key="'filled'"
        round
        flat
        icon="favorite"
        class="text-red-500"
        color="red"
        @click="toggleLike"
      />
      <q-btn
        v-else
        :key="'outlined'"
        flat
        round
        icon="favorite_border"
        class="text-gray-500"
        color="grey"
        @click="toggleLike"
      />
    </transition>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  name: {
    type: String,
    required: true,
    validator: value => ['heart', 'heart-outline'].includes(value)
  }
});

const emit = defineEmits(['toggle-like']);

function toggleLike() {
  emit('toggle-like');
}
</script>

<style scoped>
.heart-container {
  display: inline-flex; /* Ensure items are inline */
  margin-bottom: 27px;
  margin-right: 13px;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.1s ease;
  transform: scale(1.1);
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>