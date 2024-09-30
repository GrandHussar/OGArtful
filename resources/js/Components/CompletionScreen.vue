<template>
  <div class="completion-modal" v-if="visible">
    <div class="modal-content">
      <h1>Collage Saved Successfully!</h1>
      <p>Your collage has been saved.</p>
      <button @click="createNewCollage">Create New Collage</button>
      <button @click="returnToDashboard">Return to Dashboard</button>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    visible: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    createNewCollage() {
      this.$emit('create-new-collage');
    },
    returnToDashboard() {
      window.location.href = '/dashboard'; // Navigate to the dashboard route using plain JavaScript
    },
  },
};
</script>

<style scoped>
.completion-modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 20px 40px;
  border-radius: 8px;
  width: 50%;
  max-width: 500px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
  text-align: center;
}

h1 {
  margin-bottom: 20px;
  font-size: 1.8em;
  color: #333;
}

button {
  margin: 10px;
  padding: 10px 20px;
  font-size: 1em;
  cursor: pointer;
  border: none;
  border-radius: 4px;
  background-color: #007bff;
  color: white;
  transition: background-color 0.3s;
}

button:hover {
  background-color: #0056b3;
}
</style>