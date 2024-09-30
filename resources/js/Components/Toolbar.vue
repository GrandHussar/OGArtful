<template>
  <div class="toolbar">
    <!-- Add Image -->
    <div class="toolbar-group dropdown">
      <button @click="$emit('toggle-file-picker')" title="Add Image(s)">
        <i class="fas fa-file-image"></i>
      </button>
    </div>
    <div class="toolbar-group dropdown">
      <button @click="$emit('toggle-template-picker')" title="Pick Image(s)">
        <i class="fas fa-images"></i>
      </button>
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
      <button @click="toggleShapeMode" title="Add Shape" :class="{ active: shapesActive }">
        <i class="fas fa-shapes"></i>
      </button>
    </div>

    <!-- Frame and Border Options -->
    <div class="toolbar-group dropdown" :class="{ open: frameDropdownOpen }">
      <button 
        @click="toggleFrameBorderTool" 
        title="Frame and Border Options" 
        :class="{ active: frameAndBorderActive }">
        <i class="fas fa-border-style"></i>
      </button>
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
          <i class="fas fa-arrow-right-from-bracket"></i> Bring to Front
        </button>
        <button @click="sendToBack" title="Send to Back">
          <i class="fas fa-arrow-right-to-bracket"></i> Send to Back
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

    <div class="toolbar-group">
      <button @click="$emit('load-collage')" title="Load Collage">
        <i class="fas fa-folder-open"></i>
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
import FrameAndBorderTool from '@/Components/FrameAndBorderTool.vue';
import BackgroundTool from '@/Components/BackgroundTool.vue';

export default {
  components: {
    BrushTool,
    EraserTool,
    ShapeAdder,
    FrameAndBorderTool,
    BackgroundTool
  },
  props: {
    frameAndBorderActive: Boolean,
  },
  data() {
    return {
      brushMode: false,
      brushColor: '#000000',
      brushSize: 10,
      eraserSize: 10,
      eraserActive: false,
      shapesActive: false,
      brushDropdownOpen: false,
      eraserDropdownOpen: false,
      shapeDropdownOpen: false,
      frameDropdownOpen: false,
      backgroundDropdownOpen: false,
      layerDropdownOpen: false,
    };
  },
  methods: {
    toggleBrushMode() {
      // Toggle brush mode and deactivate others
      this.brushMode = !this.brushMode;
      if (this.brushMode) {
        this.eraserActive = false;
        this.shapesActive = false;
        this.$emit('toggle-frame-border', false);
      }
      this.$emit('toggle-brush-mode', this.brushMode);
    },
    toggleEraserMode() {
      // Toggle eraser mode and deactivate others
      this.eraserActive = !this.eraserActive;
      if (this.eraserActive) {
        this.brushMode = false;
        this.shapesActive = false;
        this.$emit('toggle-frame-border', false);
      }
      this.$emit('toggle-eraser-mode', this.eraserActive);
    },
    toggleShapeMode() {
      // Toggle shape mode and deactivate others
      this.shapesActive = !this.shapesActive;
      if (this.shapesActive) {
        this.brushMode = false;
        this.eraserActive = false;
        this.$emit('toggle-frame-border', false);
      }
      this.$emit('toggle-shapes-mode', this.shapesActive);
    },
    toggleFrameBorderTool() {
      // Toggle frame and border tool and deactivate others
      const isActive = !this.frameAndBorderActive;
      this.$emit('toggle-frame-border', isActive);
      if (isActive) {
        this.brushMode = false;
        this.eraserActive = false;
        this.shapesActive = false;
      }
    },
    toggleDropdown(dropdown) {
      this[`${dropdown}DropdownOpen`] = !this[`${dropdown}DropdownOpen`];
    },
    applyBackground(options) {
      this.$emit('apply-background', options);
    },
    triggerLoadCollage() {
      const collageId = prompt('Enter Collage ID to Load:');
      if (collageId) {
        this.$emit('load-collage', collageId);
      }
    },
    updateBrushMode(brushMode) {
      this.brushMode = brushMode;
      this.$emit('update-brushMode', brushMode);
    },
    updateEraserSize(size) {
      this.eraserSize = size;
      this.$emit('update-eraserSize', size);
    },
    updateEraserActive(active) {
      this.eraserActive = active;
      this.$emit('update-eraserActive', active);
    },
    applyFrameBorder(options) {
      this.$emit('apply-frame-border', options);
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
  position: relative;
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
  min-width: 200px;
  transform: translateY(10px); /* Adjusted for visibility */
  transition: opacity 0.3s ease, transform 0.3s ease;
  top: 100%; /* Ensures the dropdown appears below the button */
  left: 0; /* Aligns the dropdown to the left of the button */
}

.dropdown.open .dropdown-content {
  display: block;
  opacity: 1;
  transform: translateY(0); /* Adjusted to ensure it's fully visible */
}

.dropdown-content button {
  display: block;
  width: 100%;
  margin: 5px 0;
  padding: 10px;
  background-color: #f0f0f0;
  border: 1px solid #ccc;
  cursor: pointer;
  font-size: 16px;
  text-align: left;
}

.dropdown-content button i {
  margin-right: 10px;
}

.dropdown-content button:disabled {
  background-color: #ddd;
  cursor: not-allowed;
}
</style>