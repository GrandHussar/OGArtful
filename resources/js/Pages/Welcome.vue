<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, defineProps } from 'vue';
import axios from 'axios';

// Define the props
const props = defineProps({
  canLogin: {
    type: Boolean,
    required: true,
  },
  canRegister: {
    type: Boolean,
    required: false,
  },
});

const siteSettings = ref({
  logo_path: '',
  background_color: '#f0f0f0',
  navbar_color: '#fdecdf',
  button_color: '#efd6d5',
  parallax_image_path: '',
  carousel_image_paths: [],
  welcome_text: 'Unleash your creativity, draw anything you want.',
});

const slide = ref(1);
const imagesLoaded = ref(false);

onMounted(() => {
  fetchSiteSettings();
});

const fetchSiteSettings = async () => {
  try {
    const response = await axios.get('/api/site-settings');
    siteSettings.value = response.data;
    imagesLoaded.value = true; // Set imagesLoaded to true once data is fetched
  } catch (error) {
    console.error('Failed to fetch site settings:', error);
  }
};
const preloadImages = (imagePaths) => {
  let loadedCount = 0;

  imagePaths.forEach((path) => {
    const img = new Image();
    img.src = `/storage/${path}`;
    img.onload = () => {
      loadedCount++;
      if (loadedCount === imagePaths.length) {
        imagesLoaded.value = true;
      }
    };
    img.onerror = onImageError;
  });
};

const onImageError = (e) => {
  console.error('Image failed to load:', e.target.src);
};
</script>

<template>
  <Head title="Welcome" />
  <div :style="{ backgroundColor: siteSettings.background_color }" class="min-h-screen flex flex-col">
    <nav :style="{ backgroundColor: siteSettings.navbar_color }" class="navbar justify-between gap-4 shadow-lg z-10">
      <q-toolbar-title shrink class="ml-10 row items-center no-wrap">
        <button class="btn btn-ghost flex items-center gap-2 text-2xl text-black">
          <img :src="siteSettings.logo_path ? `/storage/${siteSettings.logo_path}` : '/storage/images/LOGOIMG.png'" alt="Logo" class="w-8 h-8" />
          ArtfulMind 
        </button>
      </q-toolbar-title>

      <div class="sm:hidden lg:flex gap-4 mr-10">
        <template v-if="props.canLogin">
          <Link
            v-if="$page.props.auth.user"
            :href="route('dashboard')"
            class="btn btn-sm btn-ghost text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
          >
            Dashboard
          </Link>
          <template v-else>
            <Link
              :href="route('login')"
              class="btn btn-sm btn-ghost text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
            >
              Log in
            </Link>
            <Link
              v-if="props.canRegister"
              :href="route('register')"
              class="btn btn-sm" :style="{ backgroundColor: siteSettings.button_color }"
            >
              Register
            </Link>
          </template>
        </template>
      </div>
      <div class="lg:hidden dropdown dropdown-end">
        <button class="btn btn-ghost">
          <i class="fa-solid fa-bars text-lg"></i>
        </button>
        <ul tabindex="0" class="dropdown-content menu z-[1] bg-base-200 p-4 rounded-box shadow w-56 gap-2">
          <li>
            <template v-if="props.canLogin">
              <Link
                v-if="$page.props.auth.user"
                :href="route('dashboard')"
                class="btn btn-sm btn-ghost text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
              >
                Dashboard
              </Link>
              <template v-else>
                <Link
                  :href="route('login')"
                  class="btn btn-sm btn-ghost text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white"
                >
                  Log in
                </Link>
                <Link
                  v-if="props.canRegister"
                  :href="route('register')"
                  class="btn btn-sm" :style="{ backgroundColor: siteSettings.button_color }"
                >
                  Register
                </Link>
              </template>
            </template>
          </li>
        </ul>
      </div>
    </nav>
    <div 
    class="background-section" 
    :style="{ backgroundImage: `url(${siteSettings.parallax_image_path ? '/storage/' + siteSettings.parallax_image_path : '/storage/images/NEWBGIMG.png'})` }"
  >
    <div class="content">
      <div class="text-h2 text-black text-center">{{ siteSettings.welcome_text }}</div>
      <div class="text-h5 text-black text-center mb-5 mt-5">
        Our community cares for your mental health. Let a therapist assess your artwork.
      </div>
      <Link :href="route('register')">
        <q-btn push rounded no-caps padding="md xl" class="btn text-black" size="lg" :style="{ backgroundColor: siteSettings.button_color }">
          Get Started
        </q-btn>
      </Link>
    </div>
  </div>
  <div style="width:100%; height:100%; object-fit:contain;">
    <q-carousel
  v-model="slide"
  swipeable
  animated
  control-color="black"
  navigation
  padding
  height="850px"
  keep-alive
  infinite
  autoplay
  arrows
  transition-prev="slide-right"
  transition-next="slide-left"
  class="bg-secondary text-black shadow-1 rounded-borders w-full"
  v-if="imagesLoaded"
>
  <template v-slot:navigation-icon="{ active, btnProps, onClick }">
    <q-btn v-if="active" size="md" :icon="btnProps.icon" color="black" flat round dense @click="onClick" />
    <q-btn v-else size="sm" :icon="btnProps.icon" color="grey" flat round dense @click="onClick" />
  </template>

  <q-carousel-slide
    v-for="(image, index) in siteSettings.carousel_image_paths"
    :key="index"
    :name="`slide-${index}`"
    class="column no-wrap flex-center"
  >
    <!-- <q-img
      :src="`/storage/${image}`"
      spinner-color="white"
      @error="onImageError"
      loading
      class="full-height"
    /> -->
    <div class="row fit justify-center items-center q-gutter-xs q-col-gutter no-wrap">
          <q-img class="rounded-borders col-12 full-height" :src="`/storage/${image}`"
      spinner-color="white"
      @error="onImageError"
      loading />
    </div>
  </q-carousel-slide>
</q-carousel>
</div>
  </div>

  <footer :style="{ backgroundColor: siteSettings.navbar_color }" class="flex flex-col sm:flex-row gap-8 justify-between p-10">
    <aside>
      <q-toolbar-title v-if="$q.screen.gt.sm" shrink class="row items-center no-wrap">
 
        <button class="btn btn-ghost text-3xl flex items-center gap-2 text-black">
          <img :src="siteSettings.logo_path ? `/storage/${siteSettings.logo_path}` : '/storage/images/LOGOIMG.png'" alt="Logo" class="w-8 h-8" />
          ArtfulMind
        </button>
     
      </q-toolbar-title>
      <small class="ml-6">Copyright © 2024 - All rights reserved</small>
    </aside>
  </footer>
</template>
<style scoped>
.text-brand {
  color: #a2aa33 !important;
}

.bg-base-200 {
  background-color: #f5f5f5;
}

.background-section {
  height: 750px;
  background-size: cover;
  background-position: center;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  background-attachment: fixed; /* This is key for the parallax effect */
}

.content {
  text-align: center;
  color: black;
  z-index: 2;
}

.background-section::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.2); /* Optional: Darken the image with an overlay */
  z-index: 1;
}

/* Additional styles to ensure the content is spaced correctly */
body, html {
  margin: 0;
  padding: 0;
  height: 100%;
  overflow-x: hidden;
}

.min-h-screen {
  min-height: 100vh;
}

.q-carousel-slide img {
  width: 100%;
  height: 100%;
  object-fit: contain; /* Ensures the image fits within the slide without being cut off */
  max-height: 750px;
}


</style>
