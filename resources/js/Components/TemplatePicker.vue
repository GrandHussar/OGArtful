<template>
  <div class="template-picker">
    <div class="header">
      <span style="color:black;">Choose Template</span>
      <button @click="closePicker" class="close-button" style="color:black;">X</button>
    </div>
    <div class="template-content">
      <template v-for="(category, index) in categorizedTemplates" :key="index">
        <h3 class="category-title">{{ category.title }}</h3>
        <div class="template-row">
          <div v-for="(template, idx) in category.templates" :key="idx" class="template-item" @click="selectTemplate(template)">
            <img :src="template.imageUrl" alt="Template Image" class="template-image">
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      templates: [
        { id: 1, title: 'Flowers', imageUrl: '/storage/images/FLOWERS/1.jpg' },
        { id: 2, title: 'Flowers', imageUrl: '/storage/images/FLOWERS/2.jpg' },
        { id: 3, title: 'Flowers', imageUrl: '/storage/images/FLOWERS/3.jpg' },
        { id: 4, title: 'Flowers', imageUrl: '/storage/images/FLOWERS/4.jpg' },
        { id: 5, title: 'Flowers', imageUrl: '/storage/images/FLOWERS/5.jpg' },
        { id: 6, title: 'Ice Cream', imageUrl: '/storage/images/ICE CREAM/1.jpg' },
        { id: 7, title: 'Ice Cream', imageUrl: '/storage/images/ICE CREAM/2.jpg' },
        { id: 8, title: 'Ice Cream', imageUrl: '/storage/images/ICE CREAM/3.jpg' },
        { id: 9, title: 'Ice Cream', imageUrl: '/storage/images/ICE CREAM/4.jpg' },
        { id: 10, title: 'Ice Cream', imageUrl: '/storage/images/ICE CREAM/5.jpg' },
        { id: 11, title: 'Motivational', imageUrl: '/storage/images/MOTIVATIONAL/1.jpg' },
        { id: 12, title: 'Motivational', imageUrl: '/storage/images/MOTIVATIONAL/2.jpg' },
        { id: 13, title: 'Motivational', imageUrl: '/storage/images/MOTIVATIONAL/3.jpg' },
        { id: 14, title: 'Motivational', imageUrl: '/storage/images/MOTIVATIONAL/4.jpg' },
        { id: 15, title: 'Motivational', imageUrl: '/storage/images/MOTIVATIONAL/5.jpg' },
        { id: 16, title: 'Scenery', imageUrl: '/storage/images/SCENERY/1.jpg' },
        { id: 17, title: 'Scenery', imageUrl: '/storage/images/SCENERY/2.jpg' },
        { id: 18, title: 'Scenery', imageUrl: '/storage/images/SCENERY/3.jpg' },
        { id: 19, title: 'Scenery', imageUrl: '/storage/images/SCENERY/4.jpg' },
        { id: 20, title: 'Scenery', imageUrl: '/storage/images/SCENERY/5.jpg' },
        { id: 21, title: 'Woman Empowerment', imageUrl: '/storage/images/WOMAN EMPOWERMENT/1.jpg' },
        { id: 22, title: 'Woman Empowerment', imageUrl: '/storage/images/WOMAN EMPOWERMENT/2.jpg' },
        { id: 23, title: 'Woman Empowerment', imageUrl: '/storage/images/WOMAN EMPOWERMENT/3.jpg' },
        { id: 24, title: 'Woman Empowerment', imageUrl: '/storage/images/WOMAN EMPOWERMENT/4.jpg' },
        { id: 25, title: 'Woman Empowerment', imageUrl: '/storage/images/WOMAN EMPOWERMENT/5.jpg' }
        // Add more templates as needed
      ]
    };
  },
  computed: {
    categorizedTemplates() {
      // Group templates by title/category
      const categories = {};
      this.templates.forEach(template => {
        if (!categories[template.title]) {
          categories[template.title] = [];
        }
        categories[template.title].push(template);
      });

      return Object.keys(categories).map(title => ({
        title,
        templates: categories[title]
      }));
    }
  },
  methods: {
    selectTemplate(template) {
      this.$emit('select-template', template);
      this.closePicker();
    },
    closePicker() {
      this.$emit('close');
    }
  }
};
</script>

<style scoped>
.template-picker {
  position: fixed;
  top: 10%;
  left: 30%;
  width: 50%;
  max-height: 80vh; /* Ensure popup does not exceed viewport height */
  background: white;
  border: 1px solid #ccc;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
  z-index: 1000;
  overflow-y: auto; /* Allow scrolling */
  transition: opacity 0.3s ease;
}

.header {
  display: flex;
  justify-content: space-between;
  padding: 10px;
  border-bottom: 1px solid #ccc;
}

.close-button {
  background: none;
  border: none;
  cursor: pointer;
  font-size: 16px;
}

.template-content {
  padding: 10px;
}

.category-title {
  font-size: 18px;
  font-weight: bold;
  margin-bottom: 10px;
  padding: 10px 0;
  color:black;
}

.template-row {
  display: grid;
  grid-template-columns: repeat(5, 1fr); /* 5 items per row */
  gap: 10px;
  margin-bottom: 20px; /* Space between rows */
}

.template-item {
  cursor: pointer;
}

.template-image {
  width: 100%;
}

.template-picker-fade-enter-active, .template-picker-fade-leave-active {
  transition: opacity 0.3s ease;
}

.template-picker-fade-enter, .template-picker-fade-leave-to {
  opacity: 0;
}
</style>