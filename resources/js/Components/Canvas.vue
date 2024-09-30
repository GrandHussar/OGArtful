<template>
  <div ref="stageContainer" class="canvas-container"></div>
</template>

<script>
import { Stage, Layer, Rect, Line, Circle, Image as KonvaImage, Text, Transformer } from 'konva';
import axios from 'axios';
import jsPDF from 'jspdf';
import { template } from 'lodash';

export default {
  props: {
    stageConfig: Object,
    brushMode: Boolean,
    brushColor: String,
    brushSize: Number,
    brushStyle: String, // Added brushStyle prop
    eraserActive: Boolean,
    eraserSize: Number,
    canvasFormat: String,
    canvasDimensions: Object,
    backgroundColor: String,
    template: String,
  },
  data() {
    return {
      stage: null,
      layer: null,
      brushLayer: null,
      transformer: null,
      isDrawing: false,
      brushStrokes: [],
      selectedObject: null,
      history: [],
      historyIndex: -1,
      canvasFrame: null,
      backgroundLayer: null,
      a4Dimensions: {
        portrait: { width: 793.7, height: 1122.52 },
        landscape: { width: 1122.52, height: 793.7 },
      },
      currentScale: 1,
      localCanvasDimensions: { width: 800, height: 600 }, // Local state for canvas dimensions
      localBackgroundColor: '#ffffff', // Local state for background color
    };
  },
  watch: {
    canvasFormat: {
      handler() {
        if (this.backgroundLayer) {
          this.updateCanvasSize();
        }
      },
      immediate: true,
    },
    canvasDimensions: {
      handler(newVal) {
        this.localCanvasDimensions = { ...newVal }; // Safely update the local copy
        this.updateCanvasSize();
      },
      immediate: true,
    },
    backgroundColor: {
      handler(newColor) {
        this.localBackgroundColor = newColor; // Safely update the local copy
        this.updateBackgroundColor();
      },
      immediate: true,
    },
    brushSize(newSize) {
      this.updateCursor(); // Update cursor position after size change
    },
    brushColor() {
      this.updateCursor();
    },
    eraserSize() {
      this.updateCursor();
    },
  },
  mounted() {
    this.initializeCanvas();
    
    this.$nextTick(() => {
    window.addEventListener('resize', this.handleResize);
    window.addEventListener('keydown', this.handleUndoRedoShortcut);
    if (this.stage && this.stage.container()) {
      this.stage.container().addEventListener('wheel', this.handleZoom);
      this.stage.container().addEventListener('mouseleave', this.handleCanvasMouseLeave);
    }
  });
  },
  methods: {
    initializeCanvas(data = null) {
    // Ensure the container exists
    if (!this.$refs.stageContainer) {
        console.error('Stage container not found');
        return;
    }

    const container = this.$refs.stageContainer;
    const stageWidth = container.clientWidth;
    const stageHeight = container.clientHeight;

    // Initialize the stage
    this.stage = new Konva.Stage({
        container: container,
        width: stageWidth,
        height: stageHeight,
    });

    this.backgroundLayer = new Konva.Layer();
    this.layer = new Konva.Layer();
    this.brushLayer = new Konva.Layer();

    this.stage.add(this.backgroundLayer);
    this.stage.add(this.layer);
    this.stage.add(this.brushLayer);

    this.transformer = new Konva.Transformer();
    this.layer.add(this.transformer);

    // Load existing data if provided
    if (data) {
        this.localCanvasDimensions = { width: data.width, height: data.height };
        this.localBackgroundColor = data.backgroundColor || '#ffffff';
        this.updateCanvasSize();

        // Handle background
        if (data.template) {
            // Load the template image as the background
            const imageObj = new Image();
            imageObj.onload = () => {
                const backgroundImage = new Konva.Image({
                    image: imageObj,
                    x: (this.stage.width() - data.width) / 2,
                    y: (this.stage.height() - data.height) / 2,
                    width: data.width,
                    height: data.height,
                    listening: false, // Ensure the image is not interactive
                });

                this.backgroundLayer.add(backgroundImage);
                this.backgroundLayer.batchDraw();
            };
            imageObj.src = data.template;
        } else {
            // If no template is provided, fill background with color
            this.updateBackgroundColor();
        }

        // Load elements into canvas
        if (data.elements) {
            this.loadElements(data.elements);
        }
    }

    this.centerAndZoomCanvas();
    this.stage.scale({ x: 1, y: 1 });
    this.stage.position({ x: 0, y: 0 });
    this.stage.batchDraw();

    // Setup stage event listeners
    this.stage.on('mousedown', this.handleCanvasMouseDown);
    this.stage.on('mousemove', this.handleCanvasMouseMove);
    this.stage.on('mouseup', this.handleCanvasMouseUp);
    this.stage.on('mouseleave', this.handleCanvasMouseLeave);
    this.stage.on('mouseenter', this.handleCanvasMouseEnter);
    this.stage.on('click', this.handleCanvasClick);

    this.stage.on("mousedown", this.startPanning);
    this.stage.on("mousemove", this.panCanvas);
    this.stage.on("mouseup", this.stopPanning);
    this.stage.on("mouseleave", this.stopPanning);
},

   
    addShape(shapeData) {
    let shape;
    switch (shapeData.type) {
      case 'rectangle':
        shape = new Konva.Rect({
          x: 100,
          y: 100,
          width: shapeData.width || 100,
          height: shapeData.height || 100,
          fill: shapeData.fillColor || null, // Use null for no fill
          stroke: shapeData.strokeColor || 'black',
          strokeWidth: shapeData.strokeWidth || 2,
          draggable: true,
        });
        break;
      case 'circle':
        shape = new Konva.Circle({
          x: 200,
          y: 100,
          radius: shapeData.radius || 50,
          fill: shapeData.fillColor || null, // Use null for no fill
          stroke: shapeData.strokeColor || 'black',
          strokeWidth: shapeData.strokeWidth || 2,
          draggable: true,
        });
        break;
      case 'polygon':
        shape = new Konva.RegularPolygon({
          x: 300,
          y: 100,
          sides: shapeData.sides || 5,
          radius: shapeData.radius || 50,
          fill: shapeData.fillColor || null, // Use null for no fill
          stroke: shapeData.strokeColor || 'black',
          strokeWidth: shapeData.strokeWidth || 2,
          draggable: true,
        });
        break;
      case 'ellipse':
        shape = new Konva.Ellipse({
          x: 400,
          y: 100,
          radiusX: shapeData.width / 2 || 50,
          radiusY: shapeData.height / 2 || 25,
          fill: shapeData.fillColor || null, // Use null for no fill
          stroke: shapeData.strokeColor || 'black',
          strokeWidth: shapeData.strokeWidth || 2,
          draggable: true,
        });
        break;
      case 'triangle':
        shape = new Konva.RegularPolygon({
          x: 500,
          y: 100,
          sides: 3,
          radius: shapeData.radius || 50,
          fill: shapeData.fillColor || null, // Use null for no fill
          stroke: shapeData.strokeColor || 'black',
          strokeWidth: shapeData.strokeWidth || 2,
          draggable: true,
        });
        break;
      default:
        console.error('Unknown shape type:', shapeData.type);
        return;
    }

    this.layer.add(shape);
    this.addToHistory({ action: 'add', type: shapeData.type, object: shape });
    this.stage.batchDraw();
  },
    validateAttrs(attrs) {
      return {
        x: isNaN(attrs.x) ? 0 : attrs.x,
        y: isNaN(attrs.y) ? 0 : attrs.y,
        width: isNaN(attrs.width) ? 100 : attrs.width,
        height: isNaN(attrs.height) ? 100 : attrs.height,
        scaleX: isNaN(attrs.scaleX) ? 1 : attrs.scaleX,
        scaleY: isNaN(attrs.scaleY) ? 1 : attrs.scaleY,
        rotation: isNaN(attrs.rotation) ? 0 : attrs.rotation,
        stroke: attrs.stroke || 'black',
        strokeWidth: isNaN(attrs.strokeWidth) ? 1 : attrs.strokeWidth,
        ...attrs,
      };
    },
    startPanning(event) {
      if (!this.brushMode && !this.eraserActive) {
        this.isPanning = true;
        this.lastPanPosition = {
          x: event.evt.clientX,
          y: event.evt.clientY,
        };
      }
    },

    panCanvas(event) {
      if (!this.isPanning) return;

      const pos = {
        x: event.evt.clientX,
        y: event.evt.clientY,
      };

      const dx = pos.x - this.lastPanPosition.x;
      const dy = pos.y - this.lastPanPosition.y;

      this.lastPanPosition = pos;

      this.stage.position({
        x: this.stage.x() + dx,
        y: this.stage.y() + dy,
      });

      this.stage.batchDraw();
    },

    stopPanning() {
      this.isPanning = false;
    },

    loadElements(elements) {
  if (!elements || elements.length === 0) {
    console.error('No collage data provided');
    return;
  }

  this.layer.destroyChildren(); // Clear the canvas

  elements.forEach((element) => {
    let obj;
    switch (element.className) {
      case 'Text':
        obj = new Konva.Text(element.attrs);
        break;
      case 'Rect':
        obj = new Konva.Rect(element.attrs);
        break;
      case 'Circle':
        obj = new Konva.Circle(element.attrs);
        break;
      case 'Group':
        obj = new Konva.Group(element.attrs);
        break;
      case 'RegularPolygon':
        obj = new Konva.RegularPolygon(element.attrs);
        break;
      case 'Ellipse':
        obj = new Konva.Ellipse(element.attrs);
        break;  
      case 'Image':
        const img = new Image();
        img.src = element.attrs.src;
        img.onload = () => {
          obj = new Konva.Image({ ...element.attrs, image: img });
          this.layer.add(obj);
          this.stage.batchDraw(); // Draw the image once it's loaded
        };
        break;
      case 'Line': // Handle brush strokes (lines)
        obj = new Konva.Line(element.attrs);
        break;
      case 'Transformer':
        obj = new Konva.Transformer(element.attrs);
        this.transformer = obj;
        break;
      default:
        console.error('Unknown element type:', element.className);
        break;
    }
    if (obj && element.className !== 'Transformer') {
      this.layer.add(obj);
    }
  });

  if (this.transformer) {
    this.layer.add(this.transformer);
    this.transformer.moveToTop(); // Ensure the transformer is on top
  }

  this.stage.batchDraw();
},


    setCanvasDimensions(width, height) {
      this.canvasDimensions = { width, height };
      this.stageConfig.width = width;
      this.stageConfig.height = height;
      this.updateCanvasSize();
    },
    setBackgroundColor(color) {
      this.localBackgroundColor = color;
      this.updateBackgroundColor();
    },
    updateBackgroundColor() {

      if (!this.stage) {
        console.log('Stage is not initialized');
        return;
      }

      const { width, height } = this.localCanvasDimensions;
      this.backgroundLayer.removeChildren();
      const backgroundRect = new Rect({
        x: (this.stage.width() - width) / 2,
        y: (this.stage.height() - height) / 2,
        width: width,
        height: height,
        fill: this.localBackgroundColor,
        listening: false,
      });
      this.backgroundLayer.add(backgroundRect);
      this.backgroundLayer.batchDraw();
    },
    updateBackgroundImage(imageUrl) {
      console.log('Updating background image to:', imageUrl);
      if (!this.stage) {
        console.error('Stage is not initialized');
        return;
      }

      const { width, height } = this.localCanvasDimensions;
      this.backgroundLayer.removeChildren();

      const imageObj = new window.Image();
      imageObj.onload = () => {
        const backgroundImage = new Konva.Image({
          image: imageObj,
          x: (this.stage.width() - width) / 2,
          y: (this.stage.height() - height) / 2,
          width: width,
          height: height,
          listening: false,
        });
        this.backgroundLayer.add(backgroundImage);
        this.backgroundLayer.batchDraw();
      };
      imageObj.src = imageUrl;
},

    updateCanvasSize() {
    if (!this.stage || !this.backgroundLayer) {
      console.log("Stage or backgroundLayer is not initialized");
      return;
    }

    const container = this.$refs.stageContainer;
    if (!container) {
      console.log('Stage container is not available.');
      return;
    }

    const stageWidth = container.clientWidth;
    const stageHeight = container.clientHeight;

    this.stage.width(stageWidth);
    this.stage.height(stageHeight);

    const { width, height } = this.localCanvasDimensions;

    const xOffset = (stageWidth - width) / 2;
    const yOffset = (stageHeight - height) / 2;

    const backgroundRect = new Konva.Rect({
      x: xOffset,
      y: yOffset,
      width: width,
      height: height,
      fill: this.localBackgroundColor,
      listening: false,
    });

    this.backgroundLayer.removeChildren();
    this.backgroundLayer.add(backgroundRect);

    // Update the stage position to ensure it matches the canvas boundaries
    this.stage.position({ x: xOffset, y: yOffset });

    this.backgroundLayer.batchDraw();
  },


    centerAndZoomCanvas() {
      const container = this.$refs.stageContainer;
      if (!container) {
        console.error('Stage container is not available.');
        return;
      }

      const stageWidth = container.clientWidth;
      const stageHeight = container.clientHeight;
      const { width: canvasWidth, height: canvasHeight } = this.localCanvasDimensions;

      const scaleX = stageWidth / canvasWidth;
      const scaleY = stageHeight / canvasHeight;
      const scale = Math.min(scaleX, scaleY) * 0.8; // Scale down a bit for better fit

      this.stage.scale({ x: scale, y: scale });

      const offsetX = (stageWidth - canvasWidth * scale) / 2;
      const offsetY = (stageHeight - canvasHeight * scale) / 2;
      this.stage.position({ x: offsetX, y: offsetY });

      this.stage.batchDraw();
    },

    handleZoom(event) {
      if (!this.stage) {
      console.log('Stage is not initialized');
      return;
    }
      event.preventDefault();

      const scaleBy = 1.1;
      const oldScale = this.currentScale;
      const newScale = event.deltaY > 0 ? oldScale * scaleBy : oldScale / scaleBy;

      const stage = this.stage;
      const mousePointTo = {
        x: stage.getPointerPosition().x / oldScale - stage.x() / oldScale,
        y: stage.getPointerPosition().y / oldScale - stage.y() / oldScale,
      };

      this.currentScale = newScale;

      stage.scale({ x: newScale, y: newScale });

      const newPos = {
        x: -(mousePointTo.x - stage.getPointerPosition().x / newScale) * newScale,
        y: -(mousePointTo.y - stage.getPointerPosition().y / newScale) * newScale,
      };

      stage.position(newPos);
      stage.batchDraw();
    },

    updateCursor() {
      if (this.eraserActive) {
        this.stage.container().style.cursor = 'crosshair';
      } else if (this.brushMode) {
        this.stage.container().style.cursor = 'crosshair';
      } else {
        this.stage.container().style.cursor = 'default';
      }
    },

    getCanvasDimensions() {
      if (this.canvasFormat === 'A4Portrait') {
        return this.a4Dimensions.portrait;
      } else if (this.canvasFormat === 'A4Landscape') {
        return this.a4Dimensions.landscape;
      }
      return { width: 800, height: 600 };
    },

    addText({ text, fontType, fontSize }) {
      const textNode = new Text({
        x: this.stage.width() / 2 - 50,
        y: this.stage.height() / 2 - 20,
        text: text,
        fontSize: fontSize,
        fontFamily: fontType,
        fill: 'black',
        draggable: true,
      });

      this.layer.add(textNode);
      this.addToHistory({ action: 'add', type: 'text', object: textNode });
      this.stage.batchDraw();
    },

    handleCanvasMouseDown() {
  if (this.brushMode || this.eraserActive) {
    const pos = this.getRelativePointerPosition();
    if (!this.isWithinCanvasBoundaries(pos)) {
      return; // Don't start drawing if the initial position is outside the canvas
    }

    this.isDrawing = true;

    const lineStyle = this.brushStyle === 'dotted' ? [4, 4] : this.brushStyle === 'dashed' ? [10, 5] : [];

    const line = new Konva.Line({
      stroke: this.eraserActive ? 'rgba(255, 255, 255, 1)' : this.brushColor,
      strokeWidth: this.eraserActive ? this.eraserSize / this.currentScale : this.brushSize / this.currentScale,
      dash: lineStyle,
      globalCompositeOperation: this.eraserActive ? 'destination-out' : 'source-over',
      points: [pos.x, pos.y, pos.x, pos.y],
      draggable: false,
    });

    // Add the brush stroke to the main layer instead of a separate brushLayer
    this.layer.add(line);
    this.brushStrokes.push(line);
    this.addToHistory({
      action: 'add',
      type: this.eraserActive ? 'eraser' : 'brush',
      object: line,
    });

    this.stage.batchDraw();
  }
},

handleCanvasMouseMove() {
  this.updateCursor();
  if (!this.isDrawing) return;

  const pos = this.getRelativePointerPosition();
  if (!this.isWithinCanvasBoundaries(pos)) {
    return; // Don't draw if the current position is outside the canvas
  }

  const lastLine = this.brushStrokes[this.brushStrokes.length - 1];
  const newPoints = lastLine.points().concat([pos.x, pos.y]);
  lastLine.points(newPoints);
  this.brushLayer.batchDraw();
},
isWithinCanvasBoundaries(pos) {
  const { width, height } = this.localCanvasDimensions || this.getCanvasDimensions();
  const xOffset = (this.stage.width() - width) / 2;
  const yOffset = (this.stage.height() - height) / 2;

  const canvasBoundaries = {
    x1: xOffset,
    x2: xOffset + width,
    y1: yOffset,
    y2: yOffset + height,
  };

  return (
    pos.x >= canvasBoundaries.x1 &&
    pos.x <= canvasBoundaries.x2 &&
    pos.y >= canvasBoundaries.y1 &&
    pos.y <= canvasBoundaries.y2
  );
},


    handleCanvasMouseUp() {
      if (this.brushMode || this.eraserActive) {
        this.isDrawing = false;
      }
    },

    handleCanvasMouseLeave() {
      this.isDrawing = false;
      this.stage.batchDraw();
    },

    handleCanvasMouseEnter() {},

    handleCanvasClick(event) {
  if (this.brushMode || this.eraserActive) return;

  const pointerPosition = this.stage.getPointerPosition();
  if (pointerPosition) {
    const clickedObject = this.stage.getIntersection(pointerPosition);

    // Check if the clicked object is part of a group
    const group = clickedObject ? clickedObject.getParent() : null;

    if (group && group.className === 'Group') {
      this.selectObject(group); // Select the whole group
    } else if (clickedObject) {
      this.selectObject(clickedObject);
    } else {
      this.deselectObject();
    }
  }
},




selectObject(object) {
  try {
    this.deselectObject();

    if (object instanceof Konva.Node) {
      this.selectedObject = object;

      // If it's a group, apply the transformer to the group
      if (object.hasName('group')) {
        this.transformer.nodes([object]);
      } else {
        // If it's not a group, apply the transformer to the individual object
        object.draggable(true);
        this.transformer.nodes([object]);
      }

      this.layer.add(this.transformer); // Ensure transformer is added to the layer
      this.transformer.moveToTop(); // Bring transformer to the front
      this.transformer.show();
      this.layer.batchDraw();
      this.$emit('object-selected', object);
    } else {
      console.error('Selected object is not a valid Konva Node.');
    }
  } catch (error) {
    console.error('Error selecting object:', error);
  }
},


    deselectObject() {
      if (this.selectedObject) {
        this.selectedObject.draggable(false);
        this.transformer.nodes([]);
        this.transformer.hide();
        this.selectedObject = null;
        this.layer.batchDraw();
        this.$emit('object-deselected');
        this.destroyTextEditor();
      }
    },

    createTextEditor(node) {
      const stage = node.getStage();
      const textPosition = node.absolutePosition();
      const stageBox = stage.container().getBoundingClientRect();
      const areaPosition = {
        x: stageBox.left + textPosition.x,
        y: stageBox.top + textPosition.y,
      };

      const textarea = document.createElement('textarea');
      document.body.appendChild(textarea);

      textarea.value = node.text();
      textarea.style.position = 'absolute';
      textarea.style.top = areaPosition.y + 'px';
      textarea.style.left = areaPosition.x + 'px';
      textarea.style.width = node.width() - node.padding() * 2 + 'px';
      textarea.style.height = node.height() - node.padding() * 2 + 5 + 'px';
      textarea.style.fontSize = node.fontSize() + 'px';
      textarea.style.border = 'none';
      textarea.style.padding = '0px';
      textarea.style.margin = '0px';
      textarea.style.overflow = 'hidden';
      textarea.style.background = 'none';
      textarea.style.outline = 'none';
      textarea.style.resize = 'none';
      textarea.style.lineHeight = node.lineHeight();
      textarea.style.fontFamily = node.fontFamily();
      textarea.style.transformOrigin = 'left top';
      textarea.style.textAlign = node.align();
      textarea.style.color = node.fill();
      textarea.style.transform = 'rotateZ(' + node.rotation() + 'deg)';

      textarea.addEventListener('input', () => {
        node.text(textarea.value);
        this.layer.batchDraw();
      });

      textarea.addEventListener('keydown', (e) => {
        if (e.keyCode === 13) {
          textarea.blur();
        }
      });

      textarea.addEventListener('blur', () => {
        this.destroyTextEditor();
      });

      this.textarea = textarea;
    },

    destroyTextEditor() {
      if (this.textarea) {
        document.body.removeChild(this.textarea);
        this.textarea = null;
      }
    },

    handleUndo() {
  if (this.historyIndex >= 0) {
    const lastAction = this.history[this.historyIndex];

    switch (lastAction.action) {
      case 'add':
        // Remove the object from the layer
        lastAction.object.remove();
        break;
      case 'modify':
        // Revert the object to its previous attributes
        lastAction.object.setAttrs(lastAction.prevAttrs);
        break;
      case 'delete':
        // Re-add the deleted object to the layer
        this.layer.add(lastAction.object);
        break;
      default:
        console.error('Unknown action type:', lastAction.action);
        return;
    }

    // Move the history index back by one
    this.historyIndex--;
    this.stage.batchDraw();
  }
},

addToHistory(action) {
  // If adding a new action, remove all redo-able actions
  if (this.historyIndex < this.history.length - 1) {
    this.history = this.history.slice(0, this.historyIndex + 1);
  }

  // Add the new action to history
  this.history.push(action);

  // Update the history index
  this.historyIndex = this.history.length - 1;
},
    handleRedo() {
      if (this.historyIndex < this.history.length - 1) {
        this.historyIndex++;
        const nextAction = this.history[this.historyIndex];
        this.layer.add(nextAction.object);
        this.stage.batchDraw();
      }
    },

    uploadImages(files) {
      Array.from(files).forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
          const imageObj = new window.Image();
          imageObj.onload = () => {
            const konvaImage = new KonvaImage({
              image: imageObj,
              x: 50,
              y: 50,
              width: imageObj.width,
              height: imageObj.height,
              draggable: true,
            });
            this.layer.add(konvaImage);
            this.addToHistory({ action: 'add', type: 'image', object: konvaImage });
            this.stage.batchDraw();
          };
          imageObj.src = e.target.result;
        };
        reader.readAsDataURL(file);
      });
    },
    selectTemplate(template) {
    const imageUrl = template.imageUrl;
    this.updateBackgroundImage(imageUrl);
    this.$emit('select-template', template);
  },


    applyFrameBorder({ color, thickness, target }) {
  if (target === 'canvas') {
    // Apply border around the entire canvas
    const { width, height } = this.localCanvasDimensions || this.getCanvasDimensions();
    if (this.canvasFrame) {
      this.canvasFrame.destroy();
    }

    const xOffset = (this.stage.width() - width) / 2;
    const yOffset = (this.stage.height() - height) / 2;

    this.canvasFrame = new Konva.Rect({
      x: xOffset - thickness / 2,
      y: yOffset - thickness / 2,
      width: width + thickness,
      height: height + thickness,
      stroke: color,
      strokeWidth: thickness,
      listening: false,
    });

    this.layer.add(this.canvasFrame);
    this.canvasFrame.moveToBottom();
    
    // Add to history for undo
    this.addToHistory({
      action: 'add',
      object: this.canvasFrame,
    });

  } else if (this.selectedObject) {
    // Apply border around the selected object as a separate, selectable frame
    const objPosition = this.selectedObject.position();
    const objWidth = this.selectedObject.width() * this.selectedObject.scaleX();
    const objHeight = this.selectedObject.height() * this.selectedObject.scaleY();
    const objRotation = this.selectedObject.rotation(); // Get object rotation

    // Create the border/frame rectangle
    const frame = new Konva.Rect({
      x: objPosition.x - thickness / 2,
      y: objPosition.y - thickness / 2,
      width: objWidth + thickness,
      height: objHeight + thickness,
      stroke: color,
      strokeWidth: thickness,
      rotation: objRotation,
      draggable: true, // Make the frame draggable
      name: 'frame', // Optional: give it a name for easier identification
    });

    // Add the frame to the layer
    this.layer.add(frame);
    this.layer.batchDraw();

    // Update the transformer to include the frame as a selectable object
    this.selectedObject = frame;
    this.transformer.nodes([frame]);
    this.layer.add(this.transformer);
    this.transformer.moveToTop();
    this.layer.batchDraw();

    // Add the frame creation to the history for undo
    this.addToHistory({
      action: 'add',
      object: frame,
    });
  }
}
,
    saveCollage({ title }) {
      const elements = this.layer.getChildren().map((node) => {
        const obj = node.toObject();
        if (obj.className === 'Image') {
          obj.attrs.src = node.getAttr('image').src;
        }
        return obj;
      });

      const collageData = {
        title: title,
        elements: elements,
        width: this.localCanvasDimensions.width,
        height: this.localCanvasDimensions.height,
        backgroundColor: this.localBackgroundColor,
        template: this.template,
      };

      axios
        .post('/api/save-collage-json', collageData)
        .then((response) => {
          if (response.data.success) {
            alert('Collage saved successfully!');
          } else {
            alert('Failed to save collage.');
          }
        })
        .catch((error) => {
          console.error('Error saving collage:', error.response ? error.response.data : error.message);
          alert('An error occurred while saving the collage.');
        });
    },

    downloadCollage({ title,format }) {
      const mimeType = format === 'jpg' ? 'image/jpeg' : `image/${format}`;
      if (format === 'pdf') {
        const dataURL = this.stage.toDataURL({ mimeType: 'image/png' });
        const pdf = new jsPDF('landscape');
        pdf.addImage(
          dataURL,
          'PNG',
          0,
          0,
          pdf.internal.pageSize.getWidth(),
          pdf.internal.pageSize.getHeight()
        );
        pdf.save(`collage.${format}`);
      } else {
        const dataURL = this.stage.toDataURL({ mimeType });
        const link = document.createElement('a');
        link.href = dataURL;
        link.download = `collage.${format}`;
        link.click();
      }
    },

    bringForward() {
    if (this.selectedObject) {
      this.selectedObject.moveUp();
      this.updateTransformer();
      this.layer.batchDraw();
    }
  },

  sendBackward() {
    if (this.selectedObject) {
      this.selectedObject.moveDown();
      this.updateTransformer();
      this.layer.batchDraw();
    }
  },

  bringToFront() {
    if (this.selectedObject) {
      this.selectedObject.moveToTop();
      this.updateTransformer();
      this.layer.batchDraw();
    }
  },

  sendToBack() {
    if (this.selectedObject) {
      this.selectedObject.moveToBottom();
      this.updateTransformer();
      this.layer.batchDraw();
    }
  },

  updateTransformer() {
    if (this.selectedObject && this.transformer) {
      this.transformer.nodes([this.selectedObject]);
      this.transformer.moveToTop();
    }
  },
    toggleBrushMode(brushMode) {
      this.brushMode = brushMode;
      this.updateCursor();
    },

    updateBrushSettings(settings) {
      this.brushColor = settings.color;
      this.brushSize = settings.size;
      this.brushStyle = settings.style || 'solid'; // Set the brush style
    },

    toggleEraserMode(isActive) {
      this.isErasing = isActive;
      this.updateCursor();
    },

    updateEraserSettings(settings) {
      this.updateCursor();
    },

    handleResize() {
      if (!this.stage) return;
      this.stage.width(window.innerWidth * 0.8);
      this.stage.height(window.innerHeight);
      this.stage.batchDraw();
      this.updateCanvasSize();
    },

    handleUndoRedoShortcut(event) {
      if (event.ctrlKey && event.key === 'z') {
        this.handleUndo();
      }
      if (event.ctrlKey && event.shiftKey && event.key === 'Z') {
        this.handleRedo();
      }
      if (event.key === 'Delete') {
        this.deleteSelectedObject();
      }
    },

    deleteSelectedObject() {
      if (this.selectedObject) {
        this.selectedObject.remove();
        this.deselectObject();
        this.stage.batchDraw();
      }
    },

    addToHistory(action) {
      this.history.push(action);
      this.historyIndex = this.history.length - 1;
    },

    getRelativePointerPosition() {
      const transform = this.stage.getAbsoluteTransform().copy().invert();
      return transform.point(this.stage.getPointerPosition());
    },
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.handleResize);
    window.removeEventListener('keydown', this.handleUndoRedoShortcut);
    if (this.stage && this.stage.container()) {
      this.stage.container().removeEventListener('wheel', this.handleZoom);
      this.stage.container().removeEventListener('mouseleave', this.handleCanvasMouseLeave);
    }
  },
};
</script>

<style scoped>
.canvas-container {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-grow: 1; /* Make the canvas container take up the remaining space */
  height: 100%;
  background-color: #d3d3d3;
}
</style>