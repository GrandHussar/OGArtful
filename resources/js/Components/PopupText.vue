<template>
  <div class="popup">
    <div class="editor-header">
      <select v-model="fontType" class="font-selector">
        <option value="Arial">Arial</option>
        <option value="Helvetica">Helvetica</option>
        <option value="Times New Roman">Times New Roman</option>
        <option value="Georgia">Georgia</option>
        <option value="Courier New">Courier New</option>
      </select>
      <input type="number" v-model="fontSize" class="font-size-input" min="12" max="72">
    </div>
    <textarea v-model="newText" :style="textStyle" class="text-editor" placeholder="Enter text here"></textarea>
    <div class="button-group">
      <button @click="confirmText" class="button confirm-button">Add Text</button>
      <button @click="closePopup" class="button cancel-button">Cancel</button>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      newText: '',
      fontType: 'Arial',
      fontSize: 12, // Default font size set to 12
    };
  },
  computed: {
    textStyle() {
      return {
        fontFamily: this.fontType,
        fontSize: `${this.fontSize}px`,
      };
    },
  },
  methods: {
    confirmText() {
      if (this.newText.trim() !== '') {
        this.$emit('add-text', {
          text: this.newText,
          fontType: this.fontType,
          fontSize: this.fontSize,
        });
        this.newText = '';
      }
      this.closePopup();
    },
    closePopup() {
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
  width: 400px;
  background: #f9f9f9;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #ddd;
  box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
  z-index: 1000;
}

.editor-header {
  display: flex;
  justify-content: space-between;
  margin-bottom: 10px;
}

.font-selector,
.font-size-input {
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 14px;
}

.text-editor {
  width: 100%;
  height: 100px;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 4px;
  font-size: inherit;
  font-family: inherit;
  resize: vertical;
  margin-bottom: 15px;
}

.button-group {
  display: flex;
  justify-content: flex-end;
}

.button {
  padding: 8px 12px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s;
}

.confirm-button {
  background-color: #4CAF50;
  color: white;
  margin-right: 8px;
}

.cancel-button {
  background-color: #f44336;
  color: white;
}

.confirm-button:hover {
  background-color: #45a049;
}

.cancel-button:hover {
  background-color: #e41e20;
}
</style>