<template>
  <AuthenticatedLayout>
    <div id="draw">
      <div class="app-wrapper">
        <canvas id="canvas"></canvas>
        <div class="cursor" id="cursor"></div>

        <div class="controls">
          <div class="btn-row">
            <button type="button" @click="removeHistoryItem" :class="{ disabled: !history.length }" title="Undo">
              <i class="ion ion-reply" style="color:black"></i>
            </button>
            <button type="button" @click="removeAllHistory" :class="{ disabled: !history.length }" title="Clear all">
              <i class="ion ion-trash-a" style="color:black"></i>
            </button>
          </div>

          <div class="btn-row">
            <button title="Brush options" @click="togglePopup('showOptions')">
              <i class="ion ion-android-create" style="color:black"></i>
            </button>

            <div class="popup" v-if="activePopup === 'showOptions'">
              <div class="popup-title">Options</div>
              <button title="Restrict movement vertical" @click="options.restrictY = !options.restrictY; options.restrictX = false"
                :class="{ active: options.restrictY }">
                <i class="ion ion-arrow-right-c" style="color:black"></i>
                Restrict vertical
              </button>
              <button title="Restrict movement horizontal"
                @click="options.restrictX = !options.restrictX; options.restrictY = false"
                :class="{ active: options.restrictX }">
                <i class="ion ion-arrow-up-c" style="color:black"></i>
                Restrict horizontal
              </button>
              <button type="button" @click="simplify" :class="{ disabled: !history.length }" title="Simplify paths">
                <i class="ion ion-wand" style="color:black"></i>
                Simplify paths
              </button>
              <button type="button" @click="jumble" :class="{ disabled: !history.length }" title="Go nutz">
                <i class="ion ion-shuffle" style="color:black"></i>
                Go nutz
              </button>
            </div>
          </div>

          <div class="btn-row">
            <button title="Pick a brush size" @click="togglePopup('showSize')" :class="{ active: activePopup === 'showSize' }">
              <i class="ion ion-android-radio-button-on" style="color:black"></i>
              <span class="size-icon">
                {{ size }}
              </span>
            </button>

            <div class="popup" v-if="activePopup === 'showSize'">
              <div class="popup-title">Brush size</div>
              <label v-for="sizeItem in sizes" class="size-item">
                <input type="radio" name="size" v-model="size" :value="sizeItem">
                <span class="size" :style="{width: sizeItem + 'px', height: sizeItem + 'px'}"
                  @click="togglePopup('showSize')"></span>
              </label>
            </div>
          </div>

          <div class="btn-row">
            <button title="Pick a color" @click="togglePopup('showColor')" :class="{ active: activePopup === 'showColor' }">
              <i class="ion ion-android-color-palette" style="color:black"></i>
              <span class="color-icon" :style="{ backgroundColor: color }"></span>
            </button>

            <div class="popup" v-if="activePopup === 'showColor'">
              <div class="popup-title">Brush color</div>
              <label v-for="colorItem in colors" :key="colorItem" class="color-item">
                <input type="radio" name="color" v-model="color" :value="colorItem">
                <span :class="'color color-' + colorItem" :style="{ backgroundColor: colorItem }" @click.stop></span>
              </label>
            </div>
          </div>

          <div class="btn-row">
            <button title="Save" @click="togglePopup('showSave')">
              <i class="ion ion-ios-cloud-upload-outline" style="color:black"></i>
            </button>

            <div class="popup" v-if="activePopup === 'showSave'">
              <div class="popup-title">Save your designs</div>
              <div class="form">
                <input type="text" placeholder="Save name" v-model="save.name">
                <div v-if="save.name.length < 3" class="text-faded">
                  <i>Min 3 characters</i>
                </div>
                <q-btn class="btn" @click="saveDrawing" :disabled="history.length === 0">
                  Save as<span class="text-faded">{{ save.name }}</span>
                </q-btn>
              </div>
            </div>

            <button title="Load" @click="togglePopup('showLoad')">
              <i class="ion ion-ios-cloud-download-outline" style="color:black"></i>
            </button>

            <div class="popup" v-if="activePopup === 'showLoad'">
              <div class="popup-title">Load Drawing</div>
              <div v-if="save.saveItems.length === 0">No saved drawings</div>
              <div v-else>
                <button v-for="item in save.saveItems" :key="item.id" @click="loadDrawing(item.id)">
                  {{ item.name }}
                </button>
              </div>
              <button @click="togglePopup('showLoad')">Close</button>
            </div>
          </div>

          <div class="btn-row ">
            <button title="Download" @click="togglePopup('showDownload')">
              <i class="ion ion-android-download" style="color:black"></i>
            </button>

            <div class="popup" v-if="activePopup === 'showDownload'">
              <div class="popup-title">Download Options</div>
              <button @click="downloadAsPNG">
                <i class="ion ion-android-download" style="color:black"></i> PNG
              </button>
              <button @click="downloadAsJPG">
                <i class="ion ion-android-download" style="color:black"></i> JPG
              </button>
              <button @click="downloadAsPDF">
                <i class="ion ion-android-download" style="color:black"></i> PDF
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';

export default {
  components: {
    AuthenticatedLayout,
  },
  data() {
    return {
      history: ref([]),
      save: ref({ name: '', saveItems: [] }),
      color: '#13c5f7',
      activePopup: null,
      popups: {
        showColor: false,
        showSize: false,
        showWelcome: true,
        showSave: false,
        showOptions: false,
      },
      options: {
        restrictY: false,
        restrictX: false,
      },
      size: 12,
      colors: [
        '#d4f713',
        '#13f7ab',
        '#13f3f7',
        '#13c5f7',
        '#138cf7',
        '#1353f7',
        '#2d13f7',
        '#7513f7',
        '#a713f7',
        '#d413f7',
        '#f713e0',
        '#f71397',
        '#f7135b',
        '#f71313',
        '#f76213',
        '#f79413',
        '#f7e013',
      ],
      sizes: [6, 12, 24, 48],
      weights: [2, 4, 6],
    };
  },
  methods: {
    async loadSavedItems() {
      try {
        const response = await axios.get('/load-saved-items');
        this.save.saveItems = response.data;
      } catch (error) {
        console.error('Error loading initial save items:', error);
      }
    },
    async saveDrawing() {
      console.log("Name:", this.save.name);
      console.log("Drawing Data:", this.history);

      if (this.save.name.length < 3) {
        alert('Save name must be at least 3 characters long');
        return;
      }

      try {
        const response = await axios.post('/save-drawing', {
          name: this.save.name,
          drawingData: JSON.stringify(this.history),
        });

        console.log("Response:", response.data);

        alert('Drawing saved successfully!');
        this.save.saveItems.push(response.data);
        this.save.name = '';
        this.activePopup = null;
      } catch (error) {
        console.error('Error saving drawing:', error);
        alert('Error saving drawing, check console for details');
      }
    },
    async onMounted() {
      await this.loadSavedItems();
    },
    async loadDrawing(id) {
      try {
        const response = await axios.get(`/load-drawing/${id}`);
        console.log("Response:", response.data);
        const drawingData = response.data;
        this.activePopup = null;
        console.log("Loading drawing data:", drawingData);
        if (drawingData) {
          this.history = drawingData;
          this.redraw();
        } else {
          console.error('Error loading drawing: drawingData is undefined');
        }
      } catch (error) {
        console.error('Error loading drawing:', error);
      }
    },
    removeHistoryItem() {
      this.history.splice(this.history.length - 2, 1);
      this.redraw();
    },
    removeAllHistory() {
      this.history = [];
      this.redraw();
    },
    simplify() {
      var simpleHistory = [];
      this.history.forEach((item, i) => {
        if (i % 6 !== 1 || item.isDummy) {
          simpleHistory.push(item);
        }
      });
      this.history = simpleHistory;
      this.redraw();
    },
    jumble() {
      var simpleHistory = [];
      this.history.forEach((item, i) => {
        item.r += Math.sin(i * 20) * 5;
      });
      this.history = this.shuffle(this.history);
      this.redraw();
    },
    shuffle(a) {
      var b = [];

      a.forEach((item, i) => {
        if (!item.isDummy) {
          var l = b.length;
          var r = Math.floor(l * Math.random());
          b.splice(r, 0, item);
        }
      });

      for (var i = 0; i < b.length; i++) {
        if (i % 20 === 1) {
          b.push(this.getDummyItem());
        }
      }

      return b;
    },
    togglePopup(popupName) {
      this.activePopup = this.activePopup === popupName ? null : popupName;
    },
    saveItem() {
      if (save.value.name.length > 2) {
        var historyItem = {
          history: this.history.slice(),
          name: this.save.value.name,
        };
        this.save.value.saveItems.push(historyItem);
        this.save.value.name = '';
      }
    },
    setDummyPoint() {
      var item = this.getDummyItem();
      this.history.push(item);
      this.draw(item, this.history.length);
    },
    redraw() {
      this.ctx.clearRect(0, 0, this.c.width, this.c.height);
      this.drawBgDots();

      if (!this.history.length) {
        return true;
      }

      this.history.forEach((item, i) => {
        this.draw(item, i);
      });
    },
    drawBgDots() {
      var gridSize = 50;
      this.ctx.fillStyle = 'rgba(0, 0, 0, .2)';

      for (var i = 0; i * gridSize < this.c.width; i++) {
        for (var j = 0 * gridSize; j * gridSize < this.c.height; j++) {
          if (i > 0 && j > 0) {
            this.ctx.beginPath();
            this.ctx.rect(i * gridSize, j * gridSize, 2, 2);
            this.ctx.fill();
            this.ctx.closePath();
          }
        }
      }
    },
    draw(item, i) {
      this.ctx.lineCap = 'round';
      this.ctx.lineJoin = 'round';

      var prevItem = this.history[i - 2];

      if (i < 2) {
        return false;
      }

      if (!item.isDummy && !this.history[i - 1].isDummy && !prevItem.isDummy) {
        this.ctx.strokeStyle = item.c;
        this.ctx.lineWidth = item.r;

        this.ctx.beginPath();
        this.ctx.moveTo(prevItem.x, prevItem.y);
        this.ctx.lineTo(item.x, item.y);
        this.ctx.stroke();
        this.ctx.closePath();
      } else if (!item.isDummy) {
        this.ctx.strokeStyle = item.c;
        this.ctx.lineWidth = item.r;

        this.ctx.beginPath();
        this.ctx.moveTo(item.x, item.y);
        this.ctx.lineTo(item.x, item.y);
        this.ctx.stroke();
        this.ctx.closePath();
      }
    },
    getDummyItem() {
      if (this.history.length > 0) {
        var lastPoint = this.history[this.history.length - 1];

        return {
          isDummy: true,
          x: lastPoint.x,
          y: lastPoint.y,
          c: null,
          r: null,
        };
      } else {
        return {
          isDummy: true,
          x: 0,
          y: 0,
          c: null,
          r: null,
        };
      }
    },
    setSize() {
      const padding = 20;
      const width = window.innerWidth - padding * 2;
      const height = window.innerHeight - padding * 2;

      this.c.width = width;
      this.c.height = height;

      this.redraw();
    },
    moveMouse(e) {
  // Get the current mouse position relative to the canvas
  let x = e.offsetX;
  let y = e.offsetY;

  // Set the cursor's position based on the mouse coordinates
  let canvasRect = this.c.getBoundingClientRect();
  
  let offsetX = canvasRect.left;
  let offsetY = canvasRect.top;

  var cursor = document.getElementById('cursor');
  cursor.style.transform = `translate(${x - 10 + offsetX}px, ${y - 10 + offsetY}px)`;
  
  // If the mouse is down, proceed with drawing
  if (this.mouseDown) {
    if (!this.options.restrictX) {
      this.mouseX = x;
    }

    if (!this.options.restrictY) {
      this.mouseY = y;
    }

    var item = {
      isDummy: false,
      x: this.mouseX,
      y: this.mouseY,
      c: this.color,
      r: this.size,
    };

    this.history.push(item);
    this.draw(item, this.history.length);
  }
},
    setCanvasBackground(color) {
      const canvas = this.c;
      const ctx = this.ctx;

      ctx.save();
      ctx.globalCompositeOperation = 'destination-over';
      ctx.fillStyle = color;
      ctx.fillRect(0, 0, canvas.width, canvas.height);
      ctx.restore();
    },
    async downloadAsPNG() {
      this.setCanvasBackground('white');
      const dataURL = this.c.toDataURL('image/png');
      const link = document.createElement('a');
      link.href = dataURL;
      link.download = 'canvas.png';
      link.click();
    },
    async downloadAsJPG() {
      this.setCanvasBackground('white');
      const dataURL = this.c.toDataURL('image/jpeg', 0.8);
      const link = document.createElement('a');
      link.href = dataURL;
      link.download = 'canvas.jpg';
      link.click();
    },
    async downloadAsPDF() {
      this.setCanvasBackground('white');
      const dataURL = this.c.toDataURL('image/jpeg', 1.0);
      const pdf = new jsPDF('landscape', 'mm', [
        this.c.width / 4,
        this.c.height / 4,
      ]);
      pdf.addImage(dataURL, 'JPEG', 0, 0, this.c.width / 4, this.c.height / 4);
      pdf.save('canvas.pdf');
    },
  },
  mounted() {
    this.loadSavedItems();
    this.c = document.getElementById('canvas');
    this.ctx = this.c.getContext('2d');

    this.mouseDown = false;
    this.mouseX = 0;
    this.mouseY = 0;

    this.tempHistory = [];

    this.setSize();
    this.redraw();

    this.c.addEventListener('mousedown', (e) => {
      this.mouseDown = true;
      this.mouseX = e.offsetX;
      this.mouseY = e.offsetY;
      this.setDummyPoint();
    });

    this.c.addEventListener('mouseup', () => {
      if (this.mouseDown) {
        this.setDummyPoint();
      }
      this.mouseDown = false;
    });

    this.c.addEventListener('mouseleave', () => {
      if (this.mouseDown) {
        this.setDummyPoint();
      }
      this.mouseDown = false;
    });

    this.c.addEventListener('mousemove', (e) => {
      this.moveMouse(e);
      if (this.mouseDown) {
        this.mouseX = this.mouseX;
        this.mouseY = this.mouseY;

        if (!this.options.restrictX) {
          this.mouseX = e.offsetX;
        }

        if (!this.options.restrictY) {
          this.mouseY = e.offsetY;
        }

        var item = {
          isDummy: false,
          x: this.mouseX,
          y: this.mouseY,
          c: this.color,
          r: this.size,
        };

        this.history.push(item);
        this.draw(item, this.history.length);
      }
    });

    window.addEventListener('resize', () => {
      this.setSize();
      this.redraw();
    });
  },
};
</script>
<style scoped>
@import url('https://fonts.googleapis.com/css?family=Roboto:300,400,500');
@import url('https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');
@import url('../../css/draw.css');

.btn {
  @apply mt-2 px-3 py-1 text-xs font-normal rounded bg-blue-500 text-black cursor-pointer;
  animation-delay: 1s;
  transition: all 0.15s;
}

.btn:hover {
  @apply bg-blue-600;
}
.btn-row {
  @apply mb-4; /* Adds a margin-bottom to each button row for spacing */
}

.popup {
  background-color: white;
  border: 1px solid #ccc;
  border-radius: 6px;
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.2);
  padding: 0.75rem;
}

.popup-title {
  font-size: 0.875rem;
  font-weight: bold;
  margin-bottom: 0.75rem;
  color: black;
}

.form {
  display: flex;
  flex-direction: column;
  margin-bottom: 0.75rem;
  align-items: center;
  justify-content: center;
}

.size-item,
.color-item {
  display: inline-block;
  margin: 3px;
}

.cursor {
  width: 20px;
  height: 20px;
  border: 2px solid black;
  border-radius: 50%;
  position: absolute;
  pointer-events: none;
  background-color: rgba(255, 255, 255, 0.7);
  box-shadow: 0 0 5px rgba(0, 0, 0, 0.3); 
  transform: translate(-10px, -10px); 
  z-index: 1000; 
}

#canvas {
  width: 100%;
  height: 100%;
  display: block;
}

.controls {
  background-color: #f5f5f5;
  padding: 0.5rem;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
     margin-bottom: 0.15rem;
}

/* Media query for devices smaller than 768px */
@media (max-width: 768px) {
  .btn {
    @apply px-2 py-1 text-xs;
  }

  .popup-title {
    font-size: 0.75rem;
  }

  .form {
    padding: 0.5rem;
  }
}

/* Media query for devices smaller than 480px */
@media (max-width: 480px) {
  .controls {
    flex-direction: column;
    align-items: stretch;
    padding: 0.15rem;
    margin-bottom: 0.15rem;
  }

  .btn {
    @apply w-full px-1 py-0.5 text-xs;
  }

  .popup-title {
    font-size: 0.625rem;
  }

  .form {
    padding: 0.15rem;
  }

  .size-item,
  .color-item {
    margin: 2px;
  }
}
</style>
