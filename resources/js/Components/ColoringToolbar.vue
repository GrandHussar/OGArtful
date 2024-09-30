<template>
  <div class="toolbar">
    <!-- Add Image -->
    <div class="toolbar-group">
      <button @click="$emit('toggle-file-picker')" title="Add Image(s)">
        <i class="fas fa-file-image"></i>
      </button>
    </div>

    <div class="toolbar-group">
      <button @click="$emit('toggle-template-picker')" title="Pick Template">
        <i class="fas fa-images"></i>
      </button>
    </div>

    <!-- Font Type and Size -->
    <div class="toolbar-group">
      <label for="fontType">Font:</label>
      <select id="fontType" v-model="fontType" @change="updateFontType">
        <option value="Arial">Arial</option>
        <option value="Courier New">Courier New</option>
        <option value="Georgia">Georgia</option>
        <option value="Times New Roman">Times New Roman</option>
        <option value="Verdana">Verdana</option>
      </select>
      <label for="fontSize">Size:</label>
      <input type="number" id="fontSize" v-model="fontSize" min="10" max="100" @input="updateFontSize" />
    </div>

    <!-- Brush Options -->
    <div class="toolbar-group dropdown" :class="{ open: brushDropdownOpen }">
      <button @click="toggleBrushMode" title="Brush Mode" :class="{ active: brushMode }">
        <i class="fas fa-paint-brush"></i>
      </button>
    </div>

    <!-- Eraser Options -->
    <div class="toolbar-group dropdown" :class="{ open: eraserDropdownOpen }">
      <button @click="toggleEraserMode" title="Eraser Mode" :class="{ active: eraserActive }">
        <i class="fas fa-eraser"></i>
      </button>
    </div>

    <!-- Shape Adder Options -->
    <div class="toolbar-group dropdown" :class="{ open: shapeDropdownOpen }">
      <button @click="toggleDropdown('shape')" title="Add Shape">
        <i class="fas fa-shapes"></i>
      </button>
      <div class="dropdown-content">
        <ShapeAdder @addShape="addShape" />
      </div>
    </div>

    <!-- Frame and Border Options -->
    <div class="toolbar-group dropdown" :class="{ open: frameDropdownOpen }">
      <button @click="toggleDropdown('frame')" title="Frame and Border Options">
        <i class="fas fa-border-style"></i>
      </button>
      <div class="dropdown-content">
        <BackgroundTool @apply-background="applyBackground" />
      </div>
    </div>

    <!-- Layer Management Options -->
    <div class="toolbar-group dropdown" :class="{ open: layerDropdownOpen }">
      <button @click="toggleDropdown('layer')" title="Layer Management">
        <i class="fas fa-layer-group"></i>
      </button>
      <div class="dropdown-content">
        <button @click="bringForward" title="Bring Forward">
          <i class="fas fa-arrow-up"></i> Bring Forward
        </button>
        <button @click="sendBackward" title="Send Backward">
          <i class="fas fa-arrow-down"></i> Send Backward
        </button>
        <button @click="bringToFront" title="Bring to Front">
          <i class="fas fa-arrow-to-top"></i> Bring to Front
        </button>
        <button @click="sendToBack" title="Send to Back">
          <i class="fas fa-arrow-to-bottom"></i> Send to Back
        </button>
      </div>
    </div>

    <!-- Undo and Redo Options -->
    <div class="toolbar-group">
      <button @click="handleUndo" title="Undo">
        <i class="fas fa-undo-alt"></i>
      </button>
      <button @click="handleRedo" title="Redo">
        <i class="fas fa-redo-alt"></i>
      </button>
    </div>

    <!-- Add Text Option -->
    <div class="toolbar-group">
      <button @click="toggleTextPopup" title="Add Text">
        <i class="fas fa-font"></i>
      </button>
    </div>

    <!-- Save Option -->
    <div class="toolbar-group">
      <button @click="toggleSavePopup" title="Save">
        <i class="fas fa-save"></i>
      </button>
    </div>

    <!-- Share Option -->
    <div class="toolbar-group">
      <button @click="toggleSharePopup" title="Share">
        <i class="fas fa-share-alt"></i>
      </button>
    </div>
  </div>
</template>

<script>
import BrushTool from '@/Components/BrushTool.vue';
import EraserTool from '@/Components/EraserTool.vue';
import ShapeAdder from '@/Components/ShapeAdder.vue';
import BackgroundTool from '@/Components/BackgroundTool.vue';

export default {
  components: {
    BrushTool,
    EraserTool,
    ShapeAdder,
    BackgroundTool,
  },
  data() {
    return {
      fontType: 'Arial',
      fontSize: 24,
      brushMode: false,
      brushColor: '#000000',
      brushSize: 10,
      eraserSize: 10,
      eraserActive: false,
      brushDropdownOpen: false,
      eraserDropdownOpen: false,
      shapeDropdownOpen: false,
      frameDropdownOpen: false,
      layerDropdownOpen: false,
    };
  },
  methods: {
    toggleDropdown(dropdown) {
      this[`${dropdown}DropdownOpen`] = !this[`${dropdown}DropdownOpen`];
    },
    updateFontType() {
      this.$emit('update-font-type', this.fontType);
    },
    updateFontSize() {
      this.$emit('update-font-size', this.fontSize);
    },
    toggleBrushMode() {
      this.brushMode = !this.brushMode;
      this.$emit('toggle-brush-mode', this.brushMode);
    },
    toggleEraserMode() {
      this.eraserActive = !this.eraserActive;
      this.$emit('toggle-eraser-mode', this.eraserActive);
    },
    updateBrushMode(brushMode) {
      this.brushMode = brushMode;
      this.$emit('update-brushMode', brushMode);
    },
    updateBrushSettings(settings) {
      this.brushColor = settings.color;
      this.brushSize = settings.size;
      this.$emit('update-brushSettings', settings);
    },
    updateEraserSize(size) {
      this.eraserSize = size;
      this.$emit('update-eraserSize', size);
    },
    updateEraserActive(active) {
      this.eraserActive = active;
      this.$emit('update-eraserActive', active);
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
    handleUndo() {
      this.$emit('handle-undo');
    },
    handleRedo() {
      this.$emit('handle-redo');
    },
    toggleTextPopup() {
      this.$emit('toggle-text-popup');
    },
    addShape(shape) {
      this.$emit('addShape', shape);
    },
    toggleSavePopup() {
      this.$emit('toggle-save-popup');
    },
    toggleSharePopup() {
      this.$emit('toggle-share-popup');
    },
  },
};
</script>

<style scoped>
@import '/node_modules/@fortawesome/fontawesome-free/css/all.min.css';

.toolbar {
  display: flex;
  justify-content: space-around;
  background-color: #f0f0f0;
  padding: 10px;
  border-bottom: 1px solid #ccc;
}

.toolbar-group {
  display: flex;
  align-items: center;
}

.toolbar-group label {
  margin-right: 5px;
}

.toolbar-group input,
.toolbar-group select,
.toolbar-group button {
  margin-right: 10px;
}

.toolbar-group button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 18px;
}

.toolbar-group button.active {
  color: #007bff;
}

.dropdown {
  position: relative;
}

.dropdown-content {
  display: none;
  position: absolute;
  background-color: white;
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  padding: 10px;
  z-index: 1;
}

.dropdown.open .dropdown-content {
  display: block;
}
</style>
