<template>
  <div class="landing-page">
    <h2 class="title">Select Canvas Size and Background Color</h2>
    <div class="choice-container">
      <label>
        <input type="radio" v-model="mode" value="coloring" />
        Coloring
      </label>
      <label>
        <input type="radio" v-model="mode" value="collage" />
        Create Collage
      </label>
    </div>

    <div v-if="mode === 'collage'" class="dropdown-container">
      <select v-model="selectedSize" class="dropdown">
        <option value="" disabled>Select Canvas Size</option>
        <option value="A4Portrait">A4 Portrait</option>
        <option value="A4Landscape">A4 Landscape</option>
        <option value="A3Portrait">A3 Portrait</option>
        <option value="A3Landscape">A3 Landscape</option>
        <option value="Letter">Letter</option>
      </select>
      
    <div class="color-picker-container">
      <label for="backgroundColor" class="color-label mt-3">Background Color:</label>
      <input type="color" v-model="backgroundColor" class="color-picker mt-3" />
    </div>

    </div>

    <div v-if="mode === 'coloring'" class="dropdown-container">
      <select v-model="selectedSize" class="dropdown">
        <option value="" disabled>Select Canvas Size</option>
        <option value="A4Portrait">A4 Portrait</option>
        <option value="A3Portrait">A3 Portrait</option>
        <option value="Letter">Letter</option>
      </select>
    </div>

    <button @click="confirmSelection" class="start-button">Start</button>

    <transition name="template-picker-fade">
      <TemplatePicker v-if="mode === 'coloring' && showTemplatePicker" @select-template="handleTemplateSelection" @close="closeTemplatePicker" />
    </transition>
  </div>
</template>

<script>
import TemplatePicker from '@/Components/TemplatePicker.vue';

export default {
  components: {
    TemplatePicker,
  },
  data() {
    return {
      mode: 'collage', // Default mode
      selectedSize: 'A4Portrait', // Default size
      backgroundColor: '#ffffff',
      showTemplatePicker: false,
    };
  },
  methods: {
    confirmSelection() {
      if (!this.mode) {
        alert('Please select a mode.');
        return;
      }

      if (this.mode === 'collage' && !this.selectedSize) {
        alert('Please select a canvas size.');
        return;
      }

      if (this.mode === 'coloring') {
        this.showTemplatePicker = true;
        return;
      }

      const dimensions = this.getCanvasDimensions(this.selectedSize);
      this.$emit('set-canvas-dimensions', {
        dimensions,
        backgroundColor: this.backgroundColor,
      });
      this.$emit('initialize-canvas');
      this.$emit('close');
    },
    handleTemplateSelection(template) {
      const dimensions = this.getCanvasDimensions('A4Portrait');
      this.$emit('set-canvas-dimensions', {
        dimensions,
        backgroundImage: this.backgroundColor, // Set the selected template as background image
      });
      this.$emit('set-template', template);
      this.$emit('initialize-canvas');
      this.$emit('close');
    },
    getCanvasDimensions(size) {
      const sizes = {
        A4Portrait: { width: 595, height: 842 },
        A4Landscape: { width: 842, height: 595 },
        A3Portrait: { width: 842, height: 1191 },
        A3Landscape: { width: 1191, height: 842 },
        Letter: { width: 612, height: 792 },
      };
      return sizes[size];
    },
    closeTemplatePicker() {
      this.showTemplatePicker = false;
    },
  },
};
</script>

<style scoped>
.landing-page {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0.8);
  color: #ffffff;
  z-index: 1000;
  padding: 20px;
  box-sizing: border-box;
}

.title {
  font-size: 24px;
  margin-bottom: 30px;
  font-weight: bold;
  text-align: center;
}

.choice-container {
  margin-bottom: 20px;
  display: flex;
  justify-content: center;
}

.choice-container label {
  margin-right: 20px;
  font-size: 16px;
}

.dropdown-container,
.color-picker-container {
  margin-bottom: 20px;
  width: 100%;
  max-width: 300px;
}

.dropdown {
  width: 100%;
  padding: 12px;
  font-size: 16px;
  border-radius: 5px;
  border: 1px solid #ccc;
  background-color: #fff;
  color: #333;
  outline: none;
  box-sizing: border-box;
  transition: border-color 0.3s ease;
}

.dropdown:hover,
.dropdown:focus {
  border-color: #555;
}

.color-picker-container {
  display: flex;
  align-items: center;
}

.color-label {
  margin-right: 10px;
  font-size: 16px;
}

.color-picker {
  width: 60px; /* Adjust width as needed */
  height: 34px; /* Adjust height as needed */
  border: 1px solid #ccc;
  border-radius: 5px;
  padding: 0;
  cursor: pointer;
  box-sizing: border-box;
}

.start-button {
  padding: 12px 24px;
  font-size: 18px;
  color: #fff;
  background-color: #28a745;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.3s ease;
  outline: none;
}

.start-button:hover {
  background-color: #218838;
  transform: scale(1.05);
}

.start-button:active {
  background-color: #1e7e34;
  transform: scale(1);
}

.template-picker-fade-enter-active,
.template-picker-fade-leave-active {
  transition: opacity 0.3s ease;
}

.template-picker-fade-enter,
.template-picker-fade-leave-to {
  opacity: 0;
}
</style>