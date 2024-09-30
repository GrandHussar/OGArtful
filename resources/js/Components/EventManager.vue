<template></template>

<script>
export default {
  methods: {
    handleCanvasClick(event, stage, brushMode) {
      if (brushMode) return;
      const pointerPosition = stage.getPointerPosition();
      if (pointerPosition) {
        const clickedObject = stage.getIntersection(pointerPosition);
        return clickedObject;
      }
    },
    handleUndoRedoShortcut(event, undoCallback, redoCallback, deleteCallback) {
      if (event.ctrlKey && event.key === 'z') {
        undoCallback();
      }
      if (event.ctrlKey && event.shiftKey && event.key === 'Z') {
        redoCallback();
      }
      if (event.key === 'Delete') {
        deleteCallback();
      }
    },
  },
};
</script>