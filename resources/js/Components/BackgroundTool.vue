<template>
<div class="frame-background-tool">
    <h3>Frame and Background Tool</h3>
    <div>
    <label for="background-image">Select Background Image:</label>
    <select v-model="selectedImage" id="background-image">
        <option v-for="(image, index) in backgroundImages" :key="index" :value="image.url">
        {{ image.name }}
        </option>
    </select>
    </div>
    <div>
    <button @click="applyToCanvas">Apply to Canvas</button>
    <button @click="applyToObject">Apply to Selected Object</button>
    </div>
    <div>
    <h4>Preview:</h4>
    <div class="preview" :style="previewStyle"></div>
    </div>
</div>
</template>

<script>
export default {
data() {
return {
    selectedImage: '', // Holds the URL of the selected background image
    backgroundImages: [
    { name: 'Image 1', url: 'url_of_image_1.jpg' },
    { name: 'Image 2', url: 'url_of_image_2.jpg' },
    { name: 'Image 3', url: 'url_of_image_3.jpg' },
    // Add more images as needed
    ],
};
},
computed: {
previewStyle() {
    return {
    backgroundImage: this.selectedImage ? `url(${this.selectedImage})` : 'none',
    backgroundSize: 'cover',
    backgroundPosition: 'center',
    width: '100px',
    height: '100px',
    };
},
},
methods: {
applyToCanvas() {
    this.$emit('apply-background', {
    backgroundImage: this.selectedImage,
    target: 'canvas',
    });
},
applyToObject() {
    this.$emit('apply-background', {
    backgroundImage: this.selectedImage,
    target: 'object',
    });
},
},
};
</script>