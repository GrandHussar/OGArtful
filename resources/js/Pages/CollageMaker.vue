<template>
  <AuthenticatedLayout>
    <div class="app-container">
      <Toolbar
        @load-collage="toggleLoadCollageModal"
        :brushMode="brushMode"
        :brushColor="brushColor"
        :brushSize="brushSize"
        :eraserSize="eraserSize"
        :eraserActive="isErasing"
        :fontType="fontType"
        :fontSize="fontSize"

        @update-font-type="updateFontType"
        @update-font-size="updateFontSize"
        @toggle-brush-mode="toggleBrushMode"
        @update-brushSettings="updateBrushSettings"
        @toggle-eraser-mode="toggleEraserMode"
        @update-eraserSize="updateEraserSettings"


        @apply-background="toggleBackgroundTool"
        @bring-forward="bringForward"
        @send-backward="sendBackward"
        @bring-to-front="bringToFront"
        @send-to-back="sendToBack"
        @handle-undo="handleUndo"
        @handle-redo="handleRedo"
        @toggle-text-popup="toggleTextPopup"
        @addShape="addShape"
        @toggle-file-picker="toggleFilePicker"
        @toggle-template-picker="toggleTemplatePicker"
        @toggle-save-popup="toggleSavePopup"
        @toggle-share-popup="toggleSharePopup"
        @toggle-shapes-mode="toggleShapeMode"

        @apply-frame-border="applyFrameBorder"
        @toggle-frame-border="toggleFrameBorderTool"
        :frameAndBorderActive="frameBorderToolVisible"


      />
      <div class="main-content">
        <Sidebar
          :selectedObject="selectedObject"
          :brushMode="brushMode"
          :brushColor="brushColor"
          :brushSize="brushSize"
          :brushStyle="brushStyle"
          :eraserSize="eraserSize"
          :eraserActive="isErasing"
          :shapesActive="shapesActive"
          @update-brushMode="toggleBrushMode"
          @update-brushSettings="updateBrushSettings"
          @update-eraserSize="updateEraserSettings"
          @update-eraserActive="toggleEraserMode"
          @update-shapesActive="toggleShapeMode"
          @addShape="addShape"
          :frameAndBorderActive="frameBorderToolVisible"
          @apply-frame-border="applyFrameBorder"

        />

        <Canvas
          ref="canvas"
          @object-selected="selectObject"
          @object-deselected="deselectObject"
          @add-to-history="addToHistory"
          @apply-frame-border="applyFrameBorder"
          :stage-config="stageConfig"
          :brushMode="brushMode"
          :brushColor="brushColor"
          :brushSize="brushSize"
          :brushStyle="brushStyle"
          :customCursor="customCursor"
          :brushCursor="brushCursor"
          :eraserActive="isErasing"
          :eraserSize="eraserSize"
          :canvasFormat="canvasFormat"
          :canvasDimensions="canvasDimensions"
          :backgroundColor="backgroundColor"
        />

        <PopupText
          v-if="textPopupVisible"
          @add-text="addText"
          @close="closeTextPopup"
          :fontType="fontType"
          :fontSize="fontSize"
        />
        <ImageUploader v-if="imageUploaderVisible" @upload-images="handleImageUpload" @close="closeImageUploader" />
        <TemplatePicker v-if="templatePickerVisible" @select-template="handleTemplateSelection" @close="closeTemplatePicker" />
        <SaveCollage
          v-if="savePopupVisible"
          :initialTitle="title"
          @save="saveCollage"
          @download="downloadCollage"
          @close="closeSavePopup"
          @update-title="updateTitle"
          :canvasRef="$refs.canvas"
          :backgroundColor="backgroundColor"
          :canvasDimensions="canvasDimensions"
          :shareImage="shareImageUrl"
        />
        <LayerManagement v-if="layerManagementVisible" @close="closeLayerManagement" @bring-forward="bringForward" @send-backward="sendBackward" @bringToFront="bringToFront" @sendToBack="sendToBack" :selectedObject="selectedObject" />
        <FrameAndBorderTool v-if="false" @apply-frame-border="applyFrameBorder" />
        <LandingPage v-if="landingPageVisible" @initialize-canvas="initializeCanvas" @set-canvas-dimensions="setCanvasDimensions" @close="closeLandingPage" @set-template="handleTemplateSelection" />
        <CompletionScreen :visible="completionScreenVisible" @create-new-collage="createNewCollage" />
        <ShareComponent v-if="sharePopupVisible" :image-url="shareImageUrl" @close="closeSharePopup"     :canvasRef="$refs.canvas"
          :backgroundColor="backgroundColor"
          :canvasDimensions="canvasDimensions" />
        <LoadCollage v-if="loadCollageModalVisible" @load-collage="handleLoadedCollage" @close="toggleLoadCollageModal" />
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import Toolbar from '@/Components/Toolbar.vue';
import Sidebar from '@/Components/Sidebar.vue';
import ImageUploader from '@/Components/ImageUploader.vue';
import TemplatePicker from '@/Components/TemplatePicker.vue';
import PopupText from '@/Components/PopupText.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ShapeAdder from '@/Components/ShapeAdder.vue';
import SaveCollage from '@/Components/SaveCollage.vue';
import LayerManagement from '@/Components/LayerManagement.vue';
import Canvas from '@/Components/Canvas.vue';
import FrameAndBorderTool from '@/Components/FrameAndBorderTool.vue';
import BackgroundTool from '@/Components/BackgroundTool.vue';
import LandingPage from '@/Components/LandingPage.vue';
import CompletionScreen from '@/Components/CompletionScreen.vue';
import ShareComponent from '@/Components/ShareComponent.vue';
import LoadCollage from '@/Components/LoadCollage.vue';
import axios from 'axios';
import jsPDF from 'jspdf';

export default {
  components: {
    Toolbar,
    Sidebar,
    TemplatePicker,
    ImageUploader,
    PopupText,
    ShapeAdder,
    SaveCollage,
    LayerManagement,
    Canvas,
    BackgroundTool,
    FrameAndBorderTool,
    LandingPage,
    CompletionScreen,
    ShareComponent,
    AuthenticatedLayout,
    LoadCollage,
  },
  data() {
    return {
      shapesActive: false,
      loadCollageModalVisible: false,
      textPopupVisible: false,
      imageUploaderVisible: false,
      templatePickerVisible: false,
      savePopupVisible: false,
      layerManagementVisible: false,
      backgroundToolVisible: false,
      frameBorderToolVisible: false,
      landingPageVisible: true,
      completionScreenVisible: false,
      sharePopupVisible: false,
      shareImageUrl: '',
      brushMode: false,
      brushColor: '#000000',
      brushSize: 5,
      brushStyle: 'solid', // Add this line
      title: '',
      selectedObject: null,
      history: [],
      historyIndex: -1,
      stageConfig: {
        container: 'stageContainer',
        width: window.innerWidth * 0.8,
        height: window.innerHeight,
      },
      isErasing: false,
      eraserSize: 10,
      fontType: 'Arial',
      fontSize: 24,
      brushCursor: null,
      customCursor: null,
      canvasFormat: 'A4Portrait',
      canvasDimensions: { width: 595, height: 842 },
      backgroundColor: '#ffffff',
      template: 'null'
    };
  },
  mounted() {
    window.addEventListener('resize', this.handleResize);
    window.addEventListener('keydown', this.handleUndoRedoShortcut);
  },
  beforeDestroy() {
    window.removeEventListener('resize', this.handleResize);
    window.removeEventListener('keydown', this.handleUndoRedoShortcut);
  },
  methods: {
    setCanvasDimensions({ dimensions, backgroundColor }) {
      this.canvasDimensions = dimensions;
      this.backgroundColor = backgroundColor;
      this.stageConfig.width = dimensions.width;
      this.stageConfig.height = dimensions.height;
    },
    toggleShapeMode(shapesActive) {
        this.shapesActive = shapesActive;


},
addShape(shapeData) {
    console.log('Shape data received in CollageMaker:', shapeData); // Add this log in CollageMaker
    this.$refs.canvas.addShape(shapeData); // Ensure this method is called correctly
  },

    initializeCanvas() {
      this.$refs.canvas.initializeCanvas({
        width: this.canvasDimensions.width,
        height: this.canvasDimensions.height,
        backgroundColor: this.backgroundColor,
      });
    },
    toggleTemplatePicker() {
      this.templatePickerVisible = !this.templatePickerVisible;
    },
    closeTemplatePicker() {
      this.templatePickerVisible = false;
    },
    handleImageUpload(files) {
      if (!files) return;
      this.$refs.canvas.uploadImages(files);
      this.closeImageUploader();
    },
    applyBackground({ backgroundImage, target }) {
      if (target === 'canvas') {
        this.$refs.canvas.setBackgroundImage(backgroundImage);
      } else if (target === 'object') {

      }
    },
    toggleBackgroundTool() {
      this.backgroundToolVisible = !this.backgroundToolVisible;
    },
    loadSavedCollage(collageId) {
      this.toggleLoadCollageModal();
    },
    handleLoadedCollage(collageData) {
      console.log('Loaded collage data:', collageData);
      this.$refs.canvas.initializeCanvas({
        elements: collageData.elements,
        brushStrokes: collageData.brushStrokes,
        width: collageData.width,
        height: collageData.height,
        backgroundColor: collageData.backgroundColor,
        template: collageData.template,
      });
    },
    toggleLoadCollageModal() {
      this.loadCollageModalVisible = !this.loadCollageModalVisible;
    },
    loadCollageFromServer(collageId) {
      axios
        .get(`/api/load-collage/${collageId}`)
        .then((response) => {
          if (response.data.success) {
            const collageData = response.data.collage;
            this.$refs.canvas.initializeCanvas({
              elements: collageData.elements,
              brushStrokes: collageData.brushStrokes,
              width: collageData.width,
              height: collageData.height,
              backgroundColor: collageData.background_color,
              template: collageData.template,
            });
          } else {
            alert('Failed to load collage.');
          }
        })
        .catch((error) => {
          console.error('Error loading collage:', error);
          alert('An error occurred while loading the collage.');
        });
    },
    handleTemplateSelection(template) {
      if (!template) return;

      this.$refs.canvas.selectTemplate(template);
      const dataURL = this.$refs.canvas.stage.toDataURL({ mimeType: 'image/jpeg' });
      this.shareImageUrl = template.imageUrl;
      this.closeTemplatePicker();
    },
    setupDrawingTools(stage, layer) {
      const drawingLayer = new Konva.Layer();
      stage.add(drawingLayer);

      let isDrawing = false;

      stage.on('mousedown touchstart', () => {
        isDrawing = true;
        const pos = stage.getPointerPosition();
        drawingLayer.startStroke({
          lineJoin: 'round',
          lineCap: 'round',
          strokeWidth: 5,
          stroke: 'black',
          points: [pos.x, pos.y],
        });
      });

      stage.on('mousemove touchmove', () => {
        if (!isDrawing) return;
        const pos = stage.getPointerPosition();
        drawingLayer.extendStroke({
          points: [pos.x, pos.y],
        });
        drawingLayer.batchDraw();
      });

      stage.on('mouseup touchend', () => {
        isDrawing = false;
      });
    },
    toggleFilePicker() {
      this.imageUploaderVisible = !this.imageUploaderVisible;
    },
    closeImageUploader() {
      this.imageUploaderVisible = false;
    },
    toggleTextPopup() {
      this.textPopupVisible = !this.textPopupVisible;
    },
    closeTextPopup() {
      this.textPopupVisible = false;
    },
    addText({ text, fontType, fontSize }) {
      this.$refs.canvas.addText({
        text,
        fontType,
        fontSize,
      });
    },

    addShape(shape) {
      this.$refs.canvas.addShape(shape);
    },
    handleUndo() {
      this.$refs.canvas.handleUndo();
    },
    handleRedo() {
      this.$refs.canvas.handleRedo();
    },
    toggleSavePopup() {
      this.savePopupVisible = !this.savePopupVisible;

    },
    closeSavePopup() {
      this.savePopupVisible = false;
    },
    toggleLayerManagement() {
      this.layerManagementVisible = !this.layerManagementVisible;
    },
    closeLayerManagement() {
      this.layerManagementVisible = false;
    },
    updateTitle(newTitle) {
      this.title = newTitle;
    },
    saveCollage(collageData) {
      axios
        .post('/api/save-collage-json', {
          title: collageData.title,
          elements: collageData.elements,
          brushStrokes: collageData.brushStrokes,
          backgroundColor: collageData.backgroundColor,
          template: collageData.template,
          width: collageData.dimensions.width,
          height: collageData.dimensions.height,
        })
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
    downloadCollage({ format }) {
      const mimeType = format === 'jpg' ? 'image/jpeg' : `image/${format}`;
      if (format === 'pdf') {
        const dataURL = this.$refs.canvas.stage.toDataURL({ mimeType: 'image/png' });
        const pdf = new jsPDF('landscape');
        pdf.addImage(dataURL, 'PNG', 0, 0, pdf.internal.pageSize.getWidth(), pdf.internal.pageSize.getHeight());
        pdf.save(`collage.${format}`);
      } else {
        const dataURL = this.$refs.canvas.stage.toDataURL({ mimeType });
        const link = document.createElement('a');
        link.href = dataURL;
        link.download = `collage.${format}`;
        link.click();
      }
    },
    bringForward() {
      this.$refs.canvas.bringForward();
    },
    sendBackward() {
      this.$refs.canvas.sendBackward();
    },
    bringToFront() {
      this.$refs.canvas.bringToFront();
    },
    sendToBack() {
      this.$refs.canvas.sendToBack();
    },
    toggleBrushMode(brushMode) {
      this.brushMode = brushMode;
    },
    updateBrushSettings(settings) {
      this.brushColor = settings.color;
      this.brushSize = settings.size;
      this.brushStyle = settings.style; // Ensure brush style is updated
    },
    toggleEraserMode(isActive) {
      this.isErasing = isActive;
    },
    updateEraserSettings(size) {
      this.eraserSize = size;
    },
    addToHistory(action) {
      this.history.push(action);
      this.historyIndex = this.history.length - 1;
    },
    handleResize() {
      if (this.$refs.canvas) {
        this.$refs.canvas.handleResize();
      }
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
      this.$refs.canvas.deleteSelectedObject();
    },
    selectObject(object) {
      this.selectedObject = object;
    },
    deselectObject() {
      this.selectedObject = null;
    },
    applyFrameBorder(options) {
      this.$refs.canvas.applyFrameBorder(options);
    },
    toggleFrameBorderTool(frameBorderToolVisible) {
      this.frameBorderToolVisible = frameBorderToolVisible;
    },
    closeLandingPage() {
      this.landingPageVisible = false;
    },
    toggleSharePopup() {
      const dataURL = this.$refs.canvas.stage.toDataURL({
        mimeType: 'image/jpeg',
      });
      this.shareImageUrl = dataURL;
      this.sharePopupVisible = !this.sharePopupVisible;
    },
    closeSharePopup() {
      this.sharePopupVisible = false;
    },
    createNewCollage() {
      this.landingPageVisible = true;
      this.completionScreenVisible = false;
    },
    updateFontType(newFontType) {
      this.fontType = newFontType;
    },
    updateFontSize(newFontSize) {
      this.fontSize = newFontSize;
    },
  },
};
</script>

<style scoped>
.app-container {
  display: flex;
  height: 100vh;
  flex-direction: column;
}

.main-content {
  display: flex;
  flex-grow: 1;
  flex-direction: row;
}

.sidebar {
  width: 250px; /* or any fixed value */
}

.canvas-container {
  flex-grow: 1;
}

.toolbar {
  flex: 0 0 50px;
}
</style>
