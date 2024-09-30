<!-- <template>
    <div class="follow-container">
      <transition name="fade" mode="out-in">
        <q-btn
          v-if="isFollowing"
          :key="'followed'"
          round
          flat
          icon="check_circle"
          class="unfollow-button"
          @click="toggleFollow"
        >
          Unfollow
        </q-btn>
        <q-btn
          v-else
          :key="'not-followed'"
          flat
          round
          icon="add_circle"
          class="follow-button"
          @click="toggleFollow"
        >
          Follow
        </q-btn>
      </transition>
    </div>
  </template>
  
  <script setup>
  import { defineProps, defineEmits } from 'vue';
  
  const props = defineProps({
    name: {
    type: String,
    required: true,
    validator: value => ['following', 'notfollowing'].includes(value)
  }
  });
  
  const emit = defineEmits(['toggle-follow']);
  
  function toggleFollow() {
    emit('toggle-follow');
  }
  </script>
  
  <style scoped>
  .follow-container {
    display: inline-flex; /* Ensure items are inline */

    margin-bottom: 5px;
    margin-left: 20px;
  }
  
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.1s ease;
  }
  
  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }
  
  /* Custom button styles */
  .follow-button {
    background-color: #3897f0;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
  }
  
  .follow-button:hover {
    background-color: #3071c3;
  }
  
  .unfollow-button {
    background-color: #b0b0b0;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
  }
  
  .unfollow-button:hover {
    background-color: #8a8a8a;
  }
  </style> -->
  <!-- <template>
    <q-btn
      :label="isFollowing ? 'Following' : 'Follow'"
      :color="isFollowing ? 'negative' : 'positive'"
      @click="handleClick"
      class="text-lg"
    />
  </template>
  
  <script setup>
  import { defineProps, defineEmits } from 'vue';
  
  const props = defineProps({
    isFollowing: {
      type: Boolean,
      required: true,
    },
  });
  
  const emit = defineEmits(['toggle-follow']);
  
  const handleClick = () => {
    emit('toggle-follow');
  };
  </script> -->

  <template>
    <div class="follow-container">
      <transition name="fade" mode="out-in">
        <q-btn
          v-if="isFollowing"
          :key="'followed'"
          round
          flat
          icon="check_circle"
          class="unfollow-button"
          @click="handleClick"
          @mouseover="hover = true"
          @mouseleave="hover = false"
        >
          <span v-if="hover">Unfollow</span>
          <span v-else>Following</span>
        </q-btn>
        <q-btn
          v-else
          :key="'not-followed'"
          flat
          round
          icon="add_circle"
          class="follow-button"
          @click="handleClick"
          @mouseover="hover = true"
          @mouseleave="hover = false"
        >
          Follow
        </q-btn>
      </transition>
    </div>
  </template>
  <script setup>
  import { ref, defineProps, defineEmits } from 'vue';
  
  const props = defineProps({
    isFollowing: {
      type: Boolean,
      required: true,
    },
  });
  
  const emit = defineEmits(['toggle-follow']);
  const hover = ref(false); // Reactive variable for hover state
  
  const handleClick = () => {
    emit('toggle-follow');
  };
  </script>
  
  <style scoped>
  .follow-container {
    display: inline-flex; /* Ensure items are inline */
    margin-bottom: 5px;
    margin-left: 20px;
  }
  
  .fade-enter-active,
  .fade-leave-active {
    transition: opacity 0.1s ease;
  }
  
  .fade-enter-from,
  .fade-leave-to {
    opacity: 0;
  }
  
  /* Custom button styles */
  .follow-button {
    background-color: #3897f0;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
  }
  
  .follow-button:hover {
    background-color: #3071c3;
  }
  
  .unfollow-button {
    background-color: #b0b0b0;
    color: white;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
  }
  
  .unfollow-button:hover {
    background-color: #ff0000;
  }
  </style>


