
<template>
  <div></div>
</template>

<script>
import { Transformer } from 'konva';

export default {
  props: {
    layer: Object,
    selectedObject: Object,
  },
  watch: {
    selectedObject: {
      handler(newVal) {
        if (!this.transformer) return;
        if (newVal) {
          this.transformer.nodes([newVal]);
          this.transformer.show();
        } else {
          this.transformer.nodes([]);
          this.transformer.hide();
        }
        this.layer.batchDraw();
      },
      immediate: true,
    },
  },
  mounted() {
    this.transformer = new Transformer();
    this.layer.add(this.transformer);
    this.layer.batchDraw();
  },
  methods: {
    setNodes(nodes) {
      this.transformer.nodes(nodes);
      this.layer.batchDraw();
    },
  },
};
</script>
