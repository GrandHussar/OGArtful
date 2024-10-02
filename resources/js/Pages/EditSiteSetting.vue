<template>
  <AuthenticadeLayout>
  <div class="min-h-screen flex items-center justify-center py-10">
    <div class="bg-white shadow-lg rounded-lg p-6 w-full max-w-2xl">
      <h1 class="text-2xl font-semibold mb-6 text-center text-gray-700">Edit Site Settings</h1>
      <form @submit.prevent="updateSettings">
        <!-- General Settings Section -->
        <div class="mb-8">
          <h2 class="text-xl font-semibold mb-4 text-gray-700">General Settings</h2>

          <!-- Logo Upload -->
          <div class="mb-4">
            <label for="logo" class="block text-sm font-medium text-gray-700 mb-2">Logo</label>
            <input type="file" id="logo" @change="handleFileUpload" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            <img v-if="logoUrl" :src="logoUrl" alt="Current Logo" class="mt-4 rounded-lg w-24 h-24 object-cover">
          </div>

          <!-- Background Color -->
          <div class="mb-4">
            <label for="background_color" class="block text-sm font-medium text-gray-700 mb-2">Background Color</label>
            <input type="color" v-model="form.background_color" id="background_color" class="w-full h-10 p-2 rounded-lg">
          </div>

          <!-- Navbar Color -->
          <div class="mb-4">
            <label for="navbar_color" class="block text-sm font-medium text-gray-700 mb-2">Navbar Color</label>
            <input type="color" v-model="form.navbar_color" id="navbar_color" class="w-full h-10 p-2 rounded-lg">
          </div>

          <!-- Button Color -->
          <div class="mb-4">
            <label for="button_color" class="block text-sm font-medium text-gray-700 mb-2">Button Color</label>
            <input type="color" v-model="form.button_color" id="button_color" class="w-full h-10 p-2 rounded-lg">
          </div>

          <!-- Toolbar Color -->
          <div class="mb-4">
            <label for="toolbar_color" class="block text-sm font-medium text-gray-700 mb-2">Toolbar Color</label>
            <input type="color" v-model="form.toolbar_color" id="toolbar_color" class="w-full h-10 p-2 rounded-lg">
          </div>

          <!-- Icon Color -->
          <div class="mb-4">
            <label for="icon_color" class="block text-sm font-medium text-gray-700 mb-2">Icon Color</label>
            <input type="color" v-model="form.icon_color" id="icon_color" class="w-full h-10 p-2 rounded-lg">
          </div>
        </div>

        <!-- Welcome Page Customization -->
        <div class="mt-8">
          <h2 class="text-xl font-semibold mb-4 text-gray-700">Welcome Page Customization</h2>

          <!-- Parallax Image Upload -->
          <div class="mb-4">
            <label for="parallax_image" class="block text-sm font-medium text-gray-700 mb-2">Parallax Image</label>
            <input type="file" id="parallax_image" @change="handleParallaxUpload" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            <img v-if="parallaxUrl" :src="parallaxUrl" alt="Parallax Image" class="mt-4 rounded-lg w-full h-40 object-cover">
          </div>

          <!-- Carousel Image Uploads -->
          <div class="mb-4">
            <label for="carousel_images" class="block text-sm font-medium text-gray-700 mb-2">Carousel Images</label>
            <input type="file" id="carousel_images" multiple @change="handleCarouselUpload" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            <div v-if="carouselUrls.length" class="mt-4 grid grid-cols-3 gap-4">
              <img v-for="(image, index) in carouselUrls" :key="index" :src="image" alt="Carousel Image" class="rounded-lg w-full object-cover">
            </div>
          </div>

          <!-- Welcome Page Text Editing -->
          <div class="mb-4">
            <label for="welcome_text" class="block text-sm font-medium text-gray-700 mb-2">Welcome Page Text</label>
            <textarea v-model="form.welcome_text" id="welcome_text" rows="4" class="w-full p-2 border rounded-lg"></textarea>
          </div>
        </div>

        <!-- Save Button -->
        <button type="submit" class="w-full py-3 mt-6 bg-indigo-600 text-white rounded-lg shadow-lg hover:bg-indigo-700 transition">
          Save Settings
        </button>
      </form>
    </div>
  </div>
</AuthenticadeLayout>
</template>

<script setup>
import { ref, watchEffect } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const props = defineProps({
  siteSetting: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  logo: null,
  background_color: props.siteSetting.background_color || '#ffffff',
  navbar_color: props.siteSetting.navbar_color || '#fdecdf',
  button_color: props.siteSetting.button_color || '#efd6d5',
  toolbar_color: props.siteSetting.toolbar_color || '#fdecdf',
  icon_color: props.siteSetting.icon_color || '#000000',
  parallax_image: null,
  carousel_images: [],
  welcome_text: props.siteSetting.welcome_text || 'Unleash your creativity, draw anything you want.',
});

const logoUrl = ref(null);
const parallaxUrl = ref(null);
const carouselUrls = ref([]);

const handleFileUpload = (event) => {
  const file = event.target.files[0];
  form.logo = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    logoUrl.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const handleParallaxUpload = (event) => {
  const file = event.target.files[0];
  form.parallax_image = file;
  const reader = new FileReader();
  reader.onload = (e) => {
    parallaxUrl.value = e.target.result;
  };
  reader.readAsDataURL(file);
};

const handleCarouselUpload = (event) => {
  const files = event.target.files;
  form.carousel_images = [...files];
  carouselUrls.value = [];

  Array.from(files).forEach(file => {
    const reader = new FileReader();
    reader.onload = (e) => {
      carouselUrls.value.push(e.target.result);
    };
    reader.readAsDataURL(file);
  });
};

const updateSettings = () => {
  form.post(route('site-settings.update'), {
    preserveScroll: true,
  });
};

watchEffect(() => {
  if (props.siteSetting.logo_path) {
    logoUrl.value = `/storage/${props.siteSetting.logo_path}`;
  }
  if (props.siteSetting.parallax_image_path) {
    parallaxUrl.value = `/storage/${props.siteSetting.parallax_image_path}`;
  }
  if (props.siteSetting.carousel_image_paths && props.siteSetting.carousel_image_paths.length > 0) {
    carouselUrls.value = props.siteSetting.carousel_image_paths.map(path => `/storage/${path}`);
  }
});
</script>

<style scoped>
/* Additional scoped styles can be added here if necessary */
</style>
