<template>
  <div class="brush-tool">
    <label>
      <input type="checkbox" v-model="localBrushMode" @change="toggleBrushMode" />
      Brush Mode
    </label>
    <div v-if="localBrushMode">
      <label>
        Color:
        <input type="color" v-model="localBrushColor" @input="updateBrushSettings" />
      </label>
      <label>
        Size:
        <input type="number" v-model="localBrushSize" @input="updateBrushSettings" min="1" max="100" />
      </label>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    brushMode: Boolean,
    brushColor: String,
    brushSize: Number,
  },
  data() {
    return {
      localBrushMode: this.brushMode,
      localBrushColor: this.brushColor,
      localBrushSize: this.brushSize,
    };
  },
  watch: {
    brushMode(newVal) {
      this.localBrushMode = newVal;
    },
    brushColor(newVal) {
      this.localBrushColor = newVal;
    },
    brushSize(newVal) {
      this.localBrushSize = newVal;
    },
  },
  methods: {
    toggleBrushMode() {
      this.$emit('update:brushMode', this.localBrushMode);
    },
    updateBrushSettings() {
      this.$emit('update:brushSettings', {
        color: this.localBrushColor,
        size: this.localBrushSize,
      });
    },
  },
};
</script>

<style scoped>
.brush-tool {
  margin: 10px;
  padding: 10px;
  border: 1px solid #ccc;
  background-color: #f9f9f9;
}

label {
  display: block;
  margin: 5px 0;
}
</style>
