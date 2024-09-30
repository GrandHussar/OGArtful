<template>
  <div></div>
</template>

<script>
import { Circle } from 'konva';

export default {
  props: {
    stage: Object,
    cursorLayer: Object,
    brushMode: Boolean,
    brushSize: Number,
    brushColor: String,
  },
  data() {
    return {
      customCursor: null,
      brushCursor: null,
    };
  },
  mounted() {
    this.initializeCursors();
  },
  methods: {
    initializeCursors() {
      this.customCursor = new Circle({
        x: -100, // Start off-screen
        y: -100, // Start off-screen
        radius: 10 / this.stage.scaleX(), // Adjust for zoom level
        fill: 'black',
        opacity: 0.7,
        listening: false,
      });

      this.brushCursor = new Circle({
        x: -100, // Start off-screen
        y: -100, // Start off-screen
        radius: this.brushSize / this.stage.scaleX(), // Adjust for zoom level
        fill: 'none',
        stroke: this.brushColor,
        strokeWidth: 1 / this.stage.scaleX(), // Adjust stroke width for zoom level
        listening: false,
      });

      this.cursorLayer.add(this.customCursor);
      this.cursorLayer.add(this.brushCursor);
      this.stage.add(this.cursorLayer);
      this.stage.batchDraw();

      this.stage.on('mousemove', this.updateCursor);
      this.stage.on('mouseleave', this.hideCursors);
      this.stage.on('wheel', this.updateCursorSize); // Update on zoom
    },
    updateCursor() {
      const pos = this.stage.getPointerPosition();
      if (pos) {
        const stageScale = this.stage.scaleX(); // Assuming uniform scaling

        if (this.brushMode) {
          this.brushCursor.position({
            x: pos.x,
            y: pos.y,
          });
          this.customCursor.position({ x: -100, y: -100 }); // Hide custom cursor
        } else {
          this.customCursor.position({
            x: pos.x,
            y: pos.y,
          });
          this.brushCursor.position({ x: -100, y: -100 }); // Hide brush cursor
        }
        this.cursorLayer.batchDraw();
      }
    },
    updateCursorSize() {
      const stageScale = this.stage.scaleX(); // Assuming uniform scaling
      this.customCursor.radius(10 / stageScale); // Adjust radius for custom cursor
      this.brushCursor.radius(this.brushSize / stageScale);
      this.brushCursor.strokeWidth(1 / stageScale); // Adjust stroke width
      this.cursorLayer.batchDraw();

      const pos = this.stage.getPointerPosition();
      if (pos) {
        this.updateCursor();
      }
    },
    hideCursors() {
      this.customCursor.position({ x: -10000, y: -10000 });
      this.brushCursor.position({ x: -10000, y: -10000 });
      this.cursorLayer.batchDraw();
    },
  },
};
</script>