<!-- <script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const siteSettings = ref({
  logo_path: '',
  background_color: '#f0f0f0',
  navbar_color: '#fdecdf',
  button_color: '#efd6d5',
  toolbar_color: '#fdecdf',
});
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
onMounted(() => {
  fetchSiteSettings();
});

const fetchSiteSettings = async () => {
  try {
    const response = await axios.get('/api/site-settings');
    siteSettings.value = response.data;
  } catch (error) {
    console.error('Failed to fetch site settings:', error);
  }
};
</script>

<template>
  <div :style="{ backgroundColor: siteSettings.background_color }" class="min-h-screen flex flex-col">
    <nav :style="{ backgroundColor: siteSettings.navbar_color }" class="navbar justify-between gap-4 shadow-lg z-10">
      <q-toolbar-title v-if="$q.screen.gt.sm" shrink class="ml-10 row items-center no-wrap">
        <Link href="/">
        <button class="btn btn-ghost flex items-center gap-2 text-2xl text-black">
            <img :src="siteSettings.logo_path ? `/storage/${siteSettings.logo_path}` : '/storage/images/LOGOIMG.png'" alt="Logo" class="w-8 h-8" />
          Artful Mind
        </button>
      </Link>
      </q-toolbar-title>
      
      <div class="sm:hidden lg:flex gap-4 mr-10">
        <template v-if="canLogin">
          <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn btn-sm btn-ghost text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
            Dashboard
          </Link>
          <template v-else>
            <Link :href="route('login')" class="btn btn-sm btn-ghost text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
              Log in
            </Link>
            <Link v-if="canRegister" :href="route('register')" class="btn btn-sm" :style="{ backgroundColor: siteSettings.button_color }">
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
            <template v-if="canLogin">
              <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn btn-sm btn-ghost text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                Dashboard
              </Link>
              <template v-else>
                <Link :href="route('login')" class="btn btn-sm btn-ghost text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                  Log in
                </Link>
                <Link v-if="canRegister" :href="route('register')" class="btn btn-sm" :style="{ backgroundColor: siteSettings.button_color }">
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
     
      <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-lg overflow-hidden sm:rounded-lg">
        <slot />
    </div>
    
    </div>
    
  </div>
  <footer :style="{ backgroundColor: siteSettings.navbar_color }" class="flex flex-col sm:flex-row gap-8 justify-between p-10">
    <aside>
      <q-toolbar-title v-if="$q.screen.gt.sm" shrink class="row items-center no-wrap">
 
        <button class="btn btn-ghost text-3xl flex items-center gap-2 text-black">
          <img :src="siteSettings.logo_path ? `/storage/${siteSettings.logo_path}` : '/storage/images/LOGOIMG.png'" alt="Logo" class="w-8 h-8" />
          Artful Mind
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
</style> -->

<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const siteSettings = ref({
  logo_path: '',
  background_color: '#f0f0f0',
  navbar_color: '#fdecdf',
  button_color: '#efd6d5',
  toolbar_color: '#fdecdf',
  parallax_image_path: '', // Assuming you have this in your API response
});

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

onMounted(() => {
  fetchSiteSettings();
});

const fetchSiteSettings = async () => {
  try {
    const response = await axios.get('/api/site-settings');
    siteSettings.value = response.data;
  } catch (error) {
    console.error('Failed to fetch site settings:', error);
  }
};
</script>

<template>
  <div 
    :style="{ 
      backgroundColor: siteSettings.background_color, 
      backgroundImage: `url(${siteSettings.parallax_image_path ? '/storage/' + siteSettings.parallax_image_path : '/storage/images/NEWBGIMG.png'})`,
      backgroundSize: 'cover',
      backgroundPosition: 'center',
      backgroundAttachment: 'fixed',
    }" 
    class="min-h-screen flex flex-col"
  >
    <nav :style="{ backgroundColor: siteSettings.navbar_color }" class="navbar justify-between gap-4 shadow-lg z-10">
      <q-toolbar-title v-if="$q.screen.gt.sm" shrink class="ml-10 row items-center no-wrap">
        <Link href="/">
          <button class="btn btn-ghost flex items-center gap-2 text-2xl text-black">
            <img :src="siteSettings.logo_path ? `/storage/${siteSettings.logo_path}` : '/storage/images/LOGOIMG.png'" alt="Logo" class="w-8 h-8" />
            ArtfulMind
          </button>
        </Link>
      </q-toolbar-title>

      <div class="sm:hidden lg:flex gap-4 mr-10">
        <template v-if="canLogin">
          <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn btn-sm btn-ghost text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
            Dashboard
          </Link>
          <template v-else>
            <Link :href="route('login')" class="btn btn-sm btn-ghost text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
              Log in
            </Link>
            <Link v-if="canRegister" :href="route('register')" class="btn btn-sm" :style="{ backgroundColor: siteSettings.button_color }">
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
            <template v-if="canLogin">
              <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="btn btn-sm btn-ghost text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                Dashboard
              </Link>
              <template v-else>
                <Link :href="route('login')" class="btn btn-sm btn-ghost text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-none focus-visible:ring-[#FF2D20] dark:text-white dark:hover:text-white/80 dark:focus-visible:ring-white">
                  Log in
                </Link>
                <Link v-if="canRegister" :href="route('register')" class="btn btn-sm" :style="{ backgroundColor: siteSettings.button_color }">
                  Register
                </Link>
              </template>
            </template>
          </li>
        </ul>
      </div>
    </nav>

    <div class="flex-grow flex justify-center items-center">
      <div class="w-full sm:max-w-md px-6 py-4 bg-white shadow-lg overflow-hidden sm:rounded-lg">
        <slot />
      </div>
    </div>
<!-- 
    <footer :style="{ backgroundColor: siteSettings.navbar_color }" class="flex flex-col sm:flex-row gap-8 justify-between p-10">
      <aside>
        <q-toolbar-title v-if="$q.screen.gt.sm" shrink class="row items-center no-wrap">
          <button class="btn btn-ghost text-3xl flex items-center gap-2 text-black">
            <img :src="siteSettings.logo_path ? `/storage/${siteSettings.logo_path}` : '/storage/images/LOGOIMG.png'" alt="Logo" class="w-8 h-8" />
            Artful Mind
          </button>
        </q-toolbar-title>
        <small class="ml-6">Copyright © 2024 - All rights reserved</small>
      </aside>
    </footer> -->
  </div>
</template>

<style scoped>
.text-brand {
  color: #a2aa33 !important;
}

.bg-base-200 {
  background-color: #f5f5f5;
}

.min-h-screen {
  min-height: 100vh;
}

.flex-grow {
  flex-grow: 1;
}

body, html {
  margin: 0;
  padding: 0;
  height: 100%;
  overflow-x: hidden;
}
</style>