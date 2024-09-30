
<template>
  <div class="fixed inset-0 flex items-center justify-center bg-transparent z-50">
    <div class="bg-white bg-opacity-90 p-8 rounded-lg shadow-lg max-w-md w-full">
      <h2 class="text-xl font-semibold mb-4">Save Your Work</h2>
      <div class="mb-4">
        <input
          type="text"
          v-model="localTitle"
          placeholder="Enter collage title"
          class="w-full p-2 border border-gray-300 rounded"
        />
      </div>
      <div class="mb-4">
        <button
          @click="handleSave"
          class="w-full py-2 px-4 bg-blue-500 text-white rounded hover:bg-blue-600"
        >
          Save 
        </button>
      </div>
      <div class="mb-4">
        <select
          v-model="format"
          class="w-full p-2 border border-gray-300 rounded"
        >
          <option value="png">PNG</option>
          <option value="jpg">JPG</option>
          <option value="pdf">PDF</option>
        </select>
      </div>
      <div class="mb-4">
        <button
          @click="handleDownload"
          class="w-full py-2 px-4 bg-green-500 text-white rounded hover:bg-green-600"
        >
          Download as Image
        </button>
      </div>
      <div class="text-right">
        <button
          @click="close"
          class="py-2 px-4 bg-gray-300 text-black rounded hover:bg-gray-400"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { template } from 'lodash';

export default {
  props: ['initialTitle', 'canvasRef', 'backgroundColor', 'canvasDimensions', 'shareImage'],
  data() {
    return {
      localTitle: this.initialTitle,
      format: 'png',
    };
  },
  methods: {
    handleSave() {
      if (this.canvasRef && this.canvasRef.layer && this.canvasRef.brushLayer) {
        const elements = this.canvasRef.layer.getChildren().map((node) => {
          const obj = node.toObject();
          if (obj.className === 'Image') {
            obj.attrs.src = node.getAttr('image').src; // Include image source for images
          }
          return obj;
        });

        const brushStrokes = this.canvasRef.brushLayer.getChildren().map((node) => node.toObject());

        // Emit the save event with all necessary details
        this.$emit('save', { 
          title: this.localTitle, 
          elements, 
          brushStrokes, 
          backgroundColor: this.backgroundColor, 
          template: this.shareImage,
          dimensions: this.canvasDimensions 
        });
      } else {
        console.error('Canvas reference or layers are not available');
      }
    },
    handleDownload() {
  if (this.canvasRef) {
    const stage = this.canvasRef.stage;
    const { width: canvasWidth, height: canvasHeight } = this.canvasRef.localCanvasDimensions;

    // Ensure the canvas is at its original scale and position
    stage.scale({ x: 1, y: 1 });
    stage.position({ x: 0, y: 0 });
    stage.batchDraw();

    // Calculate the cropping area relative to the canvas dimensions
    const xOffset = (stage.width() - canvasWidth) / 2;
    const yOffset = (stage.height() - canvasHeight) / 2;

    // Log the offsets and canvas dimensions
    console.log('Canvas Width:', canvasWidth);
    console.log('Canvas Height:', canvasHeight);
    console.log('X Offset:', xOffset);
    console.log('Y Offset:', yOffset);

    // Export the A4 centered area to dataURL
    const dataURL = stage.toDataURL({
      x: xOffset,
      y: yOffset,
      width: canvasWidth,
      height: canvasHeight,
      pixelRatio: 2, // Adjust pixel ratio for higher quality if needed
      mimeType: this.format === 'jpg' ? 'image/jpeg' : `image/${this.format}`,
    });

    // Restore original state (if needed)
    stage.batchDraw();

    // Trigger the download
    const link = document.createElement('a');
    link.href = dataURL;
    link.download = `collage.${this.format}`;
    link.click();
  } else {
    console.error('Canvas reference is not available');
  }
},

    close() {
      this.$emit('close');
    },
  },
  watch: {
    initialTitle(newTitle) {
      this.localTitle = newTitle;
    },
    localTitle(newTitle) {
      this.$emit('update-title', newTitle);
    },
  },
};
</script>

<style scoped>
/* Add custom styles for the modal if necessary */
</style>