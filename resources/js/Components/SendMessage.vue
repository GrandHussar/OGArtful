<template>
  <div class="p-6 max-w-lg mx-auto bg-white">
    <div class="mb-6">
      <!-- Custom select dropdown -->
      <div class="relative">
        <select v-model="recipientId" class="w-full p-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
          <option disabled value="">Pick a therapist...</option>
          <option v-for="user in users" :key="user.id" :value="user.id">
            {{ user.name }}
          </option>
        </select>
        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
          <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
          </svg>
        </div>
      </div>
    </div>
    <form @submit.prevent="sendMessage" class="space-y-4">
      <div>
        <input
          type="text"
          v-model="message"
          placeholder="Type your message"
          class="w-full p-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          required
        />
      </div>
      <div>
        <input type="file" @change="handleFileUpload" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
        <!-- Image preview -->
        <div v-if="imagePreview" class="mt-4">
          <img :src="imagePreview" alt="Image preview" class="max-w-full h-auto rounded-lg border border-gray-300 shadow-md" />
        </div>
      </div>
      <div class="flex justify-end gap-4">
        <q-btn color="positive" label="Send" type="submit" :loading="loading" class="shadow-md" />
        <q-btn flat label="Cancel" @click="cancel" color="negative" class="shadow-md" />
      </div>
    </form>
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';

export default {
  setup(_, { emit }) {
    const users = ref([]);
    const recipientId = ref(null);
    const message = ref('');
    const file = ref(null);
    const imagePreview = ref(null);
    const loading = ref(false);

    const handleFileUpload = (event) => {
      const selectedFile = event.target.files[0];
      file.value = selectedFile;

      // Generate a preview URL for the selected image
      if (selectedFile && selectedFile.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = (e) => {
          imagePreview.value = e.target.result;
        };
        reader.readAsDataURL(selectedFile);
      } else {
        imagePreview.value = null; // Reset preview if not an image
      }
    };

    const fetchUsers = async () => {
      try {
        const response = await axios.get('/users1');
        users.value = response.data;
      } catch (error) {
        console.error('Error fetching users:', error);
      }
    };

    const sendMessage = async () => {
      if (!recipientId.value) {
        alert('Please select a recipient.');
        return;
      }

      loading.value = true;
      const formData = new FormData();
      formData.append('message', message.value);

      if (file.value) {
        formData.append('file', file.value);
      }

      formData.append('id', recipientId.value);

      const temporaryMsgId = `temp-${Date.now()}`;
      formData.append('temporaryMsgId', temporaryMsgId);

      try {
        const response = await axios.post(route('send.predefined.message'), formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
          },
        });
        console.log(response.data);
        message.value = '';
        file.value = null;
        imagePreview.value = null; // Clear image preview after sending
      } catch (error) {
        console.error('Error sending message:', error);
      } finally {
        loading.value = false;
      }
    };

    const cancel = () => {
      recipientId.value = null;
      message.value = '';
      file.value = null;
      imagePreview.value = null; // Clear image preview on cancel
      emit('close');
    };

    onMounted(() => {
      fetchUsers();
    });

    return {
      users,
      recipientId,
      message,
      file,
      imagePreview,
      loading,
      handleFileUpload,
      sendMessage,
      cancel,
    };
  },
};
</script>


<style scoped>
/* Custom styling for the file input button */
input[type="file"]::file-selector-button {
  background-color: #3b82f6; /* Blue background */
  color: white; /* White text */
  border: none; /* Remove border */
  border-radius: 8px; /* Rounded corners */
  padding: 8px 16px; /* Padding */
  cursor: pointer; /* Pointer cursor on hover */
}

/* Hover state for file input button */
input[type="file"]::file-selector-button:hover {
  background-color: #2563eb; /* Darker blue on hover */
}

/* Styles for select dropdown */
select:focus {
  outline: none;
}

/* Styles for image preview */
img {
  max-width: 100%;
  height: auto;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}
</style>