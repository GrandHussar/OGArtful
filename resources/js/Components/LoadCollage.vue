
<template>
  <div class="fixed inset-0 flex items-center justify-center bg-transparent z-50"> <!-- Changed background to transparent -->
    <div class="bg-white p-6 rounded-lg shadow-lg max-w-lg w-full">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-bold">My Previous Works</h2>
        <button @click="$emit('close')" class="text-red-500">X</button>
      </div>
      <div class="collage-grid">
        <div
          v-for="collage in collages"
          :key="collage.id"
          class="collage-item"
        >
          
          <h3 class="font-semibold text-center mt-2">{{ collage.title }}</h3>
          <p class="text-sm text-gray-500 text-center">Created on {{ new Date(collage.created_at).toLocaleDateString() }}</p>
          <div class="flex justify-between items-center mt-2">
            <button
              @click="loadCollage(collage.id)"
              class="text-blue-500 hover:underline"
            >
              Load
            </button>
            <button
              @click="confirmDelete(collage.id)"
              class="text-red-500 hover:underline"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Custom Confirm Delete Modal -->
    <div v-if="showDeleteConfirmModal" class="fixed inset-0 flex items-center justify-center bg-transparent z-60"> <!-- Changed background to transparent -->
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
        <h3 class="text-lg font-bold mb-4">Are you sure you want to delete this collage?</h3>
        <div class="flex justify-between">
          <button @click="performDelete" class="px-4 py-2 bg-red-500 text-white rounded">Yes</button>
          <button @click="cancelDelete" class="px-4 py-2 bg-gray-300 text-black rounded">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="fixed inset-0 flex items-center justify-center bg-transparent z-60"> <!-- Changed background to transparent -->
      <div class="bg-white p-6 rounded-lg shadow-lg max-w-sm w-full">
        <h3 class="text-lg font-bold mb-4">{{ successMessage }}</h3>
        <button @click="closeSuccessModal" class="px-4 py-2 bg-blue-500 text-white rounded">OK</button>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      collages: [],
      showDeleteConfirmModal: false,
      collageIdToDelete: null,
      showSuccessModal: false,
      successMessage: '',
    };
  },
  mounted() {
    this.fetchCollages();
  },
  methods: {
    fetchCollages() {
      axios.get('/api/collages')
        .then(response => {
          if (response.data.success) {
            this.collages = response.data.collages;
          } else {
            console.error('Failed to load collages');
          }
        })
        .catch(error => {
          console.error('Error loading collages:', error);
        });
    },
    loadCollage(collageId) {
        axios.get(`/api/load-collage/${collageId}`)
            .then(response => {
                // Log the full API response
                console.log('Loadcollage.vue start');
                console.log('API response:', response.data);
                console.log('Collage ID:', response.data.elements);
                console.log('Loadcollage.vue end');

                if (response.data.success) {
                    const collageData = {
                      
                        elements: response.data.elements,
                        brushStrokes: response.data.brushStrokes,
                        width: response.data.width,
                        height: response.data.height,
                        template: response.data.template,
                        backgroundColor: response.data.background_color,
                    };
                    console.log('Collage data present:', collageData)

                    // Emit the full collage data
                    this.$emit('load-collage', collageData);
                } else {
                    console.error('API responded with success: false');
                    alert('Failed to load collage.');
                }
            })
            .catch(error => {
                console.error('Error loading collage:', error);
                alert('An error occurred while loading the collage.');
            });
    },

    closeSuccessModal() {
      this.showSuccessModal = false;
    },
    confirmDelete(collageId) {
      this.collageIdToDelete = collageId;
      this.showDeleteConfirmModal = true;
    },
    performDelete() {
      const collageId = this.collageIdToDelete;
      this.showDeleteConfirmModal = false;

      axios.delete(`/api/collages/${collageId}`)
        .then(response => {
          if (response.data.success) {
            this.collages = this.collages.filter(collage => collage.id !== collageId);
            alert('Collage deleted successfully');
          } else {
            alert('Failed to delete collage');
          }
        })
        .catch(error => {
          console.error('Error deleting collage:', error);
          alert('An error occurred while deleting the collage.');
        });
    },
    cancelDelete() {
      this.showDeleteConfirmModal = false;
      this.collageIdToDelete = null;
    },
  },
};
</script>

<style scoped>
.collage-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
  gap: 20px;
  max-height: 400px;
  overflow-y: auto;
  padding-right: 10px;
}

.collage-item {
  border: 1px solid #e5e5e5;
  padding: 10px;
  border-radius: 8px;
  background-color: #f9f9f9;
}

.collage-thumbnail {
  width: 100%;
  height: auto;
  border-radius: 4px;
}

.collage-item h3 {
  margin: 0;
}

.collage-item p {
  margin: 5px 0;
}

.collage-item button {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0;
}

.collage-item button:hover {
  text-decoration: underline;
}
</style>
