<template>
  <div class="image-uploader">
    <div class="header">
      <span>Image Uploader</span>
      <button @click="$emit('close')" class="close-button">X</button>
    </div>
    <div class="drop-area" @click="triggerFileInput" @drop.prevent="handleDrop" @dragover.prevent>
      <span>Drag & Drop Images Here or Click to Upload</span>
      <input type="file" accept="image/*" multiple @change="handleFileInput" ref="fileInput" />
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    handleFileInput(event) {
      const files = event.target.files;
      this.uploadFiles(files);
    },
    handleDrop(event) {
      const files = event.dataTransfer.files;
      this.uploadFiles(files);
    },
    uploadFiles(files) {
      this.$emit('upload-images', files);
    },
    triggerFileInput() {
      this.$refs.fileInput.click();
    },
  },
};
</script>

<style scoped>
.image-uploader {
  position: fixed;
  top: 10%;
  left: 30%;
  width: 40%;
  background: white;
  border: 1px solid #ccc;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  z-index: 1000;
}

.header {
  display: flex;
  justify-content: space-between;
  padding: 10px;
  border-bottom: 1px solid #ccc;
}

.close-button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 16px;
}

.drop-area {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 200px;
  padding: 20px;
  border: 2px dashed #ccc;
  cursor: pointer;
}

.drop-area input {
  display: none;
}

.drop-area span {
  text-align: center;
  color: #ccc;
}
</style>
