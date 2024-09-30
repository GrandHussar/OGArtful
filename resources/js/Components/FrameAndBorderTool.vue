<template>
  <div class="frame-border-tool">
    <h3>Frame and Border Tool</h3>
    <div>
      <label for="frame-border-color">Color:</label>
      <input type="color" v-model="frameBorderColor" id="frame-border-color" />
    </div>
    <div>
      <label for="frame-border-thickness">Thickness:</label>
      <input type="number" v-model="frameBorderThickness" id="frame-border-thickness" />
    </div>
    <div>
      <button @click="applyToCanvas">Apply to Canvas</button>
      <button @click="applyToObject">Apply to Selected Object</button>
    </div>
    <div>
      <h4>Preview:</h4>
      <div class="preview" :style="previewStyle"></div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      frameBorderColor: '#000000',
      frameBorderThickness: 5,
    };
  },
  computed: {
    previewStyle() {
      return {
        border: `${this.frameBorderThickness}px solid ${this.frameBorderColor}`,
        width: '100px',
        height: '100px',
      };
    },
  },
  methods: {
    applyToCanvas() {
      this.$emit('apply-frame-border', {
        color: this.frameBorderColor,
        thickness: this.frameBorderThickness,
        target: 'canvas',
      });
    },
    applyToObject() {
      this.$emit('apply-frame-border', {
        color: this.frameBorderColor,
        thickness: this.frameBorderThickness,
        target: 'object',
      });
    },
  },
};
</script>

<style scoped>
.frame-border-tool {
  padding: 10px;
  border: 1px solid #ccc;
  background-color: #f9f9f9;
  width: 100%;
}

.preview {
  margin-top: 10px;
  background-color: #ffffff;
}
</style>