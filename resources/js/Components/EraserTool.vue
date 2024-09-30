<template>
  <div class="eraser-tool">
    <h3>Eraser Tool</h3>
    <div>
      <label for="eraser-size">Eraser Size:</label>
      <input type="range" id="eraser-size" min="1" max="50" v-model="localEraserSize" @input="updateEraserSize" />
      <span>{{ localEraserSize }}</span>
    </div>
    <button @click="toggleEraserMode">Toggle Eraser Mode</button>
  </div>
</template>

<script>
export default {
  props: {
    eraserSize: Number,
    eraserActive: Boolean,
  },
  data() {
    return {
      localEraserSize: this.eraserSize,
      localEraserActive: this.eraserActive,
    };
  },
  watch: {
    eraserSize(newVal) {
      this.localEraserSize = newVal;
    },
    eraserActive(newVal) {
      this.localEraserActive = newVal;
    },
  },
  methods: {
    updateEraserSize() {
      this.$emit('update:eraserSize', this.localEraserSize);
    },
    toggleEraserMode() {
      this.localEraserActive = !this.localEraserActive;
      this.$emit('update:eraserActive', this.localEraserActive);
    },
  },
};
</script>

<style scoped>
.eraser-tool {
  padding: 10px;
  border: 1px solid #ccc;
  background-color: #f9f9f9;
  margin-top: 10px;
}

.eraser-tool h3 {
  margin: 0 0 10px;
}

.eraser-tool label {
  display: block;
  margin-bottom: 5px;
}

.eraser-tool input[type="range"] {
  width: 100%;
}

.eraser-tool button {
  margin-top: 10px;
  padding: 8px;
  width: 100%;
  background-color: #007bff;
  color: white;
  border: none;
  cursor: pointer;
}

.eraser-tool button:hover {
  background-color: #0056b3;
}
</style>