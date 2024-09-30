<template>
  <div class="popup">
    <button @click="emitShapeData('rect')">Add Rectangle</button>
    <button @click="emitShapeData('circle')">Add Circle</button>
    <button @click="emitShapeData('polygon')">Add Polygon</button>
    <button @click="hide">Close</button>
  </div>
</template>

<script>
export default {
  methods: {
    emitShapeData(type) {
      const shapeData = {
        type,
        x: 100,
        y: 100,
        width: type === 'rect' ? 100 : undefined,
        height: type === 'rect' ? 100 : undefined,
        radius: type === 'circle' || type === 'polygon' ? 50 : undefined,
        sides: type === 'polygon' ? 5 : undefined, // Example for a polygon
        color: type === 'rect' ? 'red' : type === 'circle' ? 'blue' : 'green',
      };
      this.$emit('addShape', shapeData);
    },
    hide() {
      this.$emit('close');
    },
  },
};
</script>