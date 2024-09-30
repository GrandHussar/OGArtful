<template>
  <div class="popup">
    <button @click="bringForward" :disabled="!selectedObject">Bring Forward</button>
    <button @click="sendBackward" :disabled="!selectedObject">Send Backward</button>
    <button @click="bringToFront" :disabled="!selectedObject">Bring to Front</button>
    <button @click="sendToBack" :disabled="!selectedObject">Send to Back</button>
    <button @click="close">Close</button>
  </div>
</template>

<script>
export default {
  props: {
    selectedObject: Object,
  },
  methods: {
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
    close() {
      this.$emit('close');
    },
  },
};
</script>

<style scoped>
.popup {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: white;
  padding: 20px;
  border: 1px solid #ccc;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
</style>
