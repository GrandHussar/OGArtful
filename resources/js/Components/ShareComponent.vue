<template>
  <div class="share-popup q-pa-md q-mb-md">
    <h2 class="text-h4 q-mb-md">Share Your Collage</h2>
    <div v-if="uploading" style="display:flex; justify-content: center;">

      <q-spinner
        color="primary"
        size="3em"
        :thickness="10"
      />
    </div>
    <div v-else class="share-buttons">
      <q-btn
        label="Share on ArtfulMind"
        color="accent"
        icon="fa fa-paint-brush"
        @click="openArtfulmindDialog"
      />
      <q-btn
        label="Chat with Therapist"
        color="primary"
        icon="fas fa-user-md"
        @click="openTherapistChat"
      />
    </div>
    <q-btn flat label="Close" color="negative" @click="$emit('close')" class="q-mt-md" />
    
    <!-- Artful Mind Dialog -->
    <q-dialog v-model="showArtfulmindDialog" persistent>
      <div class="instagram-card bg-white rounded-lg shadow-lg overflow-hidden max-w-full">
        <div class="flex justify-start p-8 bg-gray-100">
          <h4 class="text-2xl font-bold">Create Post</h4>
        </div>

        <div class="flex flex-col gap-8 p-8 overflow-y-auto" style="height: calc(100% - 64px);">
          <form @submit.prevent="submit">
            <!-- Image Preview -->
            <div v-if="uploadedImageUrl" class="q-pa-md">
              <img :src="uploadedImageUrl" class="rounded-lg max-w-full mx-auto" alt="Uploaded Image" style="max-height: 300px;" />
            </div>

            <!-- Title input -->
            <div class="q-pa-md">
              <q-input v-model="form.title" rounded outlined type="text" label="Title" class="w-full text-lg" />
            </div>

            <!-- Description input -->
            <div class="q-pa-md">
              <q-input v-model="form.description" rounded outlined type="textarea" label="Description" class="w-full text-lg" />
            </div>

            <!-- Error message display -->
            <div v-if="errorMessage" class="q-pa-md text-negative text-lg">
              {{ errorMessage }}
            </div>

            <!-- Submit button -->
            <div class="q-pa-md flex justify-end gap-4">
              <q-btn color="positive" label="Submit" type="submit" class="text-lg" />
              <q-btn flat color="negative" label="Cancel" @click="showArtfulmindDialog = false" class="text-lg" />
            </div>
          </form>
        </div>
      </div>
    </q-dialog>

    <!-- Therapist Chat Dialog -->
    <q-dialog v-model="showTherapistChatDialog" persistent>
      <div class="instagram-card bg-white rounded-lg shadow-lg overflow-hidden max-w-full">
        <div class="flex justify-start p-8 bg-gray-100">
          <h4 class="text-2xl font-bold">Chat with Therapist</h4>
        </div>

        <div class="flex flex-col gap-8 p-8 overflow-y-auto" style="height: calc(100% - 64px);">
          <!-- Message Component -->
          <MessageSenderComponent 
            v-if="uploadedImageUrl" 
            :image-url="uploadedImageUrl" 
            @close="showTherapistChatDialog = false" 
          />
        </div>
  </div>
</q-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useForm } from '@inertiajs/vue3';
import MessageSenderComponent from '@/Components/SendMessage.vue'; // Import your message sender component
function handleImageUploaded(imageUrl) {
  uploadedImageUrl.value = imageUrl;
}
const emit = defineEmits(['image-uploaded']);
const props = defineProps({
  imageUrl: {
    type: String,
    required: true,
  },
  canvasRef: {
    type: Object,
    required: true,
  },
  backgroundColor: {
    type: String,
    required: true,
  },
  canvasDimensions: {
    type: Object,
    required: true,
  },
});

const uploadedImageUrl = ref('');
const uploading = ref(true);
const showArtfulmindDialog = ref(false);
const showTherapistChatDialog = ref(false);

const errorMessage = ref('');
const chatErrorMessage = ref('');
const chatMessage = ref('');

// Call uploadImage function on mount
onMounted(() => {
  uploadImage();
});

async function uploadImage() {
  try {
    const formData = new FormData();
    
    // Check if canvasRef is available
    if (!props.canvasRef) {
      throw new Error('Canvas reference is not available');
    }
    
    const stage = props.canvasRef.stage;
    const { width: canvasWidth, height: canvasHeight } = props.canvasDimensions;

    // Ensure the canvas is at its original scale and position
    stage.scale({ x: 1, y: 1 });
    stage.position({ x: 0, y: 0 });
    stage.batchDraw();

    // Calculate the cropping area relative to the canvas dimensions
    const xOffset = (stage.width() - canvasWidth) / 2;
    const yOffset = (stage.height() - canvasHeight) / 2;

    // Export the A4 centered area to dataURL
    const dataURL = stage.toDataURL({
      x: xOffset,
      y: yOffset,
      width: canvasWidth,
      height: canvasHeight,
      pixelRatio: 2, // Adjust pixel ratio for higher quality if needed
      mimeType: 'image/jpeg', // Adjust MIME type based on your format
    });

    // Convert the data URL to a Blob
    const blob = dataURItoBlob(dataURL);

    // Append the image Blob to the FormData
    formData.append('image', blob);

    // Send the FormData to the server using Axios
    const response = await axios.post('/api/upload-image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    // Update the uploaded image URL and form data
    uploadedImageUrl.value = response.data.url;
    form.image_url = response.data.url;
    uploading.value = false;

    // Emit the image URL to the parent component or directly to the MessageSenderComponent
    emit('image-uploaded', response.data.url);
  } catch (error) {
    console.error('Error uploading image:', error);
    alert('Failed to upload image for sharing.');
    uploading.value = false;
  }
}
function dataURItoBlob(dataURI) {
  const byteString = atob(dataURI.split(',')[1]);
  const mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];
  const ab = new ArrayBuffer(byteString.length);
  const ia = new Uint8Array(ab);
  for (let i = 0; i < byteString.length; i++) {
    ia[i] = byteString.charCodeAt(i);
  }
  return new Blob([ab], { type: mimeString });
}
const form = useForm({
  title: '',
  description: '',
  image_url: uploadedImageUrl.value,
});



function shareToFacebook() {
  if (uploadedImageUrl.value) {
    const url = `https://www.facebook.com/dialog/share?app_id=YOUR_APP_ID&href=${encodeURIComponent(uploadedImageUrl.value)}&display=popup`;
    window.open(url, '_blank');
  }
}

function shareToTwitter() {
  if (uploadedImageUrl.value) {
    const url = `https://twitter.com/intent/tweet?url=${encodeURIComponent(uploadedImageUrl.value)}`;
    window.open(url, '_blank');
  }
}

function openArtfulmindDialog() {
  showArtfulmindDialog.value = true;
}

function openTherapistChat() {
  showTherapistChatDialog.value = true;
}

const submit = () => {
  errorMessage.value = ''; // Clear any previous error messages

  // Print the form data for debugging purposes
  console.log('Form Data:', {
    title: form.title,
    description: form.description,
    image_url: form.image_url,
  });

  // Check if all required fields are filled
  if (!form.title || !form.description) {
    errorMessage.value = 'Image, Title, and Description are required.';
    return;
  }

  form.post(route('post.sharePost'), {
    onSuccess: () => {
      // Handle successful form submission
      console.log('Form submitted successfully!');
    },
    onError: (errors) => {
      // Handle errors from the server
      console.error('Form submission error:', errors);
      errorMessage.value = 'An error occurred while submitting the form.';
    },
  });
};
</script>

<style scoped>
.share-popup {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 100%;
  max-width: 500px;
  background-color: white;
  text-align: center;
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
  z-index: 10;
}

.loading {
  
  gap: 10px;
  width: 50px; /* Adjust height as needed */
}

.share-buttons {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 10px;
  margin-bottom: 20px;
}

.instagram-card {
  width: 80vw;
  height: 80vh;
  max-height: 600px;
  max-width: 600px;
}

</style>