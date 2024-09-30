<template>
  <div class="sidebar">
    <!-- Brush Settings -->
    <div v-if="brushMode">
      <h3>Brush Settings</h3>
      <div class="brush-settings">
        <label for="brush-color">Color:</label>
        <input type="color" id="brush-color" :value="localBrushColor" @input="updateBrushColor($event.target.value)" />

        <label for="brush-size">Size:</label>
        <input type="range" id="brush-size" :value="localBrushSize" min="1" max="100" @input="updateBrushSize($event.target.value)" />

        <label for="brush-style">Style:</label>
        <select id="brush-style" :value="localBrushStyle" @change="updateBrushStyle($event.target.value)">
          <option value="solid">Solid</option>
          <option value="dotted">Dotted</option>
          <option value="dashed">Dashed</option>
        </select>
      </div>
    </div>

    <!-- Eraser Settings -->
    <div v-else-if="eraserActive">
      <h3>Eraser Settings</h3>
      <div class="eraser-settings">
        <label for="eraser-size">Size:</label>
        <input type="range" id="eraser-size" :value="eraserSize" min="1" max="100" @input="updateEraserSize($event.target.value)" />
      </div>
    </div>

    <!-- Frame and Border Settings -->
    <div v-if="frameAndBorderActive">
      <FrameAndBorderTool @apply-frame-border="applyFrameBorder" />
    </div>

    <!-- Shape Settings -->
    <div v-else-if="shapesActive">
      <h3>Add Shape</h3>
      <div class="shape-settings">
        <label for="shape-type">Shape:</label>
        <select id="shape-type" v-model="selectedShapeType">
          <option value="rectangle">Rectangle</option>
          <option value="circle">Circle</option>
          <option value="ellipse">Ellipse</option>
          <option value="triangle">Triangle</option>
          <option value="polygon">Polygon</option>
        </select>

        <label for="shape-width" v-if="shapeRequiresWidth">Width:</label>
        <input type="number" id="shape-width" v-if="shapeRequiresWidth" v-model="shapeWidth" min="1" />

        <label for="shape-height" v-if="shapeRequiresHeight">Height:</label>
        <input type="number" id="shape-height" v-if="shapeRequiresHeight" v-model="shapeHeight" min="1" />

        <label for="shape-radius" v-if="shapeRequiresRadius">Radius:</label>
        <input type="number" id="shape-radius" v-if="shapeRequiresRadius" v-model="shapeRadius" min="1" />

        <label for="shape-sides" v-if="shapeRequiresSides">Number of Sides:</label>
        <input type="number" id="shape-sides" v-if="shapeRequiresSides" v-model="shapeSides" min="3" />

        <label for="shape-stroke-color">Stroke Color:</label>
        <input type="color" id="shape-stroke-color" v-model="shapeStrokeColor" />

        <label for="shape-stroke-width">Stroke Width:</label>
        <input type="number" id="shape-stroke-width" v-model="shapeStrokeWidth" min="1" />

        <label for="shape-fill-color">Fill Color:</label>
        <input type="color" id="shape-fill-color" v-model="shapeFillColor" />

        <button @click="emitShapeData">Add Shape</button>
      </div>
    </div>

    <!-- Layer Management -->
    <div v-else-if="layerManagementActive">
      <h3>Layer Management</h3>
      <div class="layer-management">
        <button @click="bringForward">Bring Forward</button>
        <button @click="sendBackward">Send Backward</button>
        <button @click="bringToFront">Bring to Front</button>
        <button @click="sendToBack">Send to Back</button>
      </div>
    </div>

    <!-- Default Section -->
    <div v-else>
      <p>Select an object to edit its properties</p>
    </div>
  </div>
</template>

<script>
import FrameAndBorderTool from '@/Components/FrameAndBorderTool.vue';

export default {
  props: {
    brushMode: Boolean,
    brushColor: String,
    brushSize: Number,
    brushStyle: String,
    eraserActive: Boolean,
    eraserSize: Number,
    frameAndBorderActive: Boolean,
    shapesActive: Boolean,
    layerManagementActive: Boolean,
    selectedShape: Object,
    shapeObject: Object,
  },
  components:{
    FrameAndBorderTool,
  },
  data() {
    return {
      localBrushColor: this.brushColor,
      localBrushSize: this.brushSize,
      localBrushStyle: this.brushStyle || 'solid',
      frameColor: '#000000',
      frameThickness: 10,
      selectedShapeType: 'rectangle',
      shapeWidth: 100,
      shapeHeight: 100,
      shapeRadius: 50,
      shapeSides: 5,
      shapeStrokeColor: '#000000',  // Added property
      shapeStrokeWidth: 2,          // Added property
      shapeFillColor: '#ff0000',    // Added property
    };
  },
  computed: {
    shapeRequiresWidth() {
      return this.selectedShapeType === 'rectangle' || this.selectedShapeType === 'ellipse';
    },
    shapeRequiresHeight() {
      return this.selectedShapeType === 'rectangle' || this.selectedShapeType === 'ellipse';
    },
    shapeRequiresRadius() {
      return this.selectedShapeType === 'circle' || this.selectedShapeType === 'ellipse' || this.selectedShapeType === 'polygon';
    },
    shapeRequiresSides() {
      return this.selectedShapeType === 'polygon';
    },
  },
  watch: {
    brushMode(newVal) {
      if (newVal) {
        this.resetActiveSections('brush');
      }
    },
    eraserActive(newVal) {
      if (newVal) {
        this.resetActiveSections('eraser');
      }
    },
    frameAndBorderActive(newVal) {
      if (newVal) {
        this.resetActiveSections('frameAndBorder');
      }
    },
    shapesActive(newVal) {
      if (newVal) {
        this.resetActiveSections('shapes');
      }
    },
    layerManagementActive(newVal) {
      if (newVal) {
        this.resetActiveSections('layerManagement');
      }
    },
  },
  methods: {
    updateBrushColor(color) {
      this.localBrushColor = color;
      this.$emit('update-brushSettings', {
        color: this.localBrushColor,
        size: this.localBrushSize,
        style: this.localBrushStyle,
      });
    },
 
    updateBrushSize(size) {
      this.localBrushSize = size;
      this.$emit('update-brushSettings', {
        color: this.localBrushColor,
        size: this.localBrushSize,
        style: this.localBrushStyle,
      });
    },
    updateBrushStyle(style) {
      this.localBrushStyle = style;
      this.$emit('update-brushSettings', {
        color: this.localBrushColor,
        size: this.localBrushSize,
        style: this.localBrushStyle,
      });
    },
    updateEraserSize(size) {
      this.$emit('update-eraserSize', size);
    },
    applyFrameBorder(options) {
      this.$emit('apply-frame-border', options);
    },
    emitShapeData() {
      const shapeData = {
        type: this.selectedShapeType,
        width: this.shapeRequiresWidth ? this.shapeWidth : undefined,
        height: this.shapeRequiresHeight ? this.shapeHeight : undefined,
        radius: this.shapeRequiresRadius ? this.shapeRadius : undefined,
        sides: this.shapeRequiresSides ? this.shapeSides : undefined,
        strokeColor: this.shapeStrokeColor,  // Pass stroke color
        strokeWidth: this.shapeStrokeWidth,  // Pass stroke width
        fillColor: this.shapeFillColor,      // Pass fill color
      };
      this.$emit('addShape', shapeData);
    },
    bringForward() {
      this.$emit('bring-forward');
    },
    sendBackward() {
      this.$emit('send-backward');
    },
    bringToFront() {
      this.$emit('bring-to-front');
    },
    sendToBack() {
      this.$emit('send-to-back');
    },
    resetActiveSections(activeSection) {
      this.$emit('update-brushMode', activeSection === 'brush');
      this.$emit('update-eraserActive', activeSection === 'eraser');
      this.$emit('update-frameAndBorderActive', activeSection === 'frameAndBorder');
      this.$emit('update-shapesActive', activeSection === 'shapes');
      this.$emit('update-layerManagementActive', activeSection === 'layerManagement');
    },
  },
};
</script>

<style scoped>
.sidebar {
  background-color: #f0f0f0;
  width: 20%;
  height: 100%;
  overflow-y: auto;
  padding: 10px;
  border-right: 1px solid #ccc;
}

.sidebar h3 {
  margin-top: 0;
  margin-bottom: 10px;
  font-size: 16px;
  font-weight: 600;
}

.sidebar label {
  display: block;
  margin: 5px 0;
  font-size: 14px;
}

.sidebar input[type="color"],
.sidebar input[type="range"],
.sidebar select,
.sidebar button,
.sidebar input[type="number"] {
  width: 100%;
  padding: 5px;
  margin-bottom: 10px;
  font-size: 14px;
}

.brush-settings,
.eraser-settings,
.frame-border-settings,
.shape-settings,
.layer-management {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.sidebar button {
  background-color: #007bff;
  color: #fff;
  border: none;
  cursor: pointer;
  text-align: center;
  font-size: 14px;
  padding: 8px;
  border-radius: 5px;
  transition: background-color 0.3s ease;
}

.sidebar button:hover {
  background-color: #0056b3;
}

.sidebar .brush-settings input[type="color"] {
  padding: 0;
}

.sidebar .brush-settings input[type="range"],
.sidebar .frame-border-settings input[type="range"],
.sidebar .eraser-settings input[type="range"] {
  -webkit-appearance: none;
  appearance: none;
  width: 100%;
  height: 8px;
  background: #ddd;
  outline: none;
  border-radius: 5px;
}

.sidebar .brush-settings input[type="range"]::-webkit-slider-thumb,
.sidebar .frame-border-settings input[type="range"]::-webkit-slider-thumb,
.sidebar .eraser-settings input[type="range"]::-webkit-slider-thumb {
  -webkit-appearance: none;
  appearance: none;
  width: 20px;
  height: 20px;
  background: #007bff;
  cursor: pointer;
  border-radius: 50%;
}

.sidebar p {
  margin: 20px 0;
  text-align: center;
  font-size: 14px;
}
</style>