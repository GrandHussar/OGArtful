<script setup>
import axios from "axios";
import { ref, computed, defineProps, defineEmits, watch } from "vue";
import { Head, Link, usePage, useForm, router } from "@inertiajs/vue3";
import { Inertia } from "@inertiajs/inertia";
import HeartButton from "@/Components/HeartButton.vue";
import moment from "moment"; // Import moment.js for date formatting
const props = defineProps({
    post: Object,
    comments: Object,
    user: Object,
});

const postImages = computed(() => props.post.postImages);
console.log('Post Images:', postImages.value);
const formattedDate = computed(() => {
    return moment(props.post.created_at).fromNow();
});

const firstImage = computed(() => postImages.value[0]?.post_image_path);
const form = useForm({
    post_id: props.post.id,
    comment: "",
});

const post = ref(props.post);
const maxComments = 5;
const showAll = ref(false);

const displayedComments = computed(() => {
    return showAll.value
        ? props.comments
        : props.comments.slice(0, maxComments);
});

const toggleComments = () => {
    showAll.value = !showAll.value;
};
const showDialog = ref(false);

const handleLikeToggle = async (event) => {
    event.preventDefault();

    // Optimistically update the UI
    post.value.liked = !post.value.liked;
    post.value.likes_count += post.value.liked ? 1 : -1;

    try {
        // Perform the server request
        await axios.post(`/post/${post.value.id}/like`, {
            post_id: post.value.id,
        });

        // On success, no additional action needed
    } catch (error) {
        // On error, revert the UI changes
        post.value.liked = !post.value.liked;
        post.value.likes_count += post.value.liked ? -1 : 1;
        console.error("Error toggling like:", error);
    }
};

const submit = () => {
    errorMessage.value = "";

    form.post(route("createComment"), {
        preserveScroll: true,
        onSuccess: () => {
            // Handle successful form submission
            console.log("Form submitted successfully!");
            successMessage.value = "Comment submitted successfully!";
            form.comment = "";
        },
        onError: (errors) => {
            // Handle errors from the server
            console.error("Form submission error:", errors);
            errorMessage.value = "An error occurred while submitting the form.";
        },
    });
};
const currentIndex = ref(0); 
const nextImage = () => {
    if (postImages.value.length > 1) {
        currentIndex.value = (currentIndex.value + 1) % postImages.value.length;
    }
};
const prevImage = () => {
    if (postImages.value.length > 1) {
        currentIndex.value = (currentIndex.value - 1 + postImages.value.length) % postImages.value.length;
    }
};
const staticImage = computed(() => postImages.value[currentIndex.value]?.post_image_path);
const currentImage = computed(() => postImages.value[currentIndex.value]?.post_image_path);
const showArrows = computed(() => postImages.value.length > 1);
const beforeEnter = (el) => {
    el.style.opacity = 0;
    el.style.transform = 'scale(0.95)';
};

const enter = (el, done) => {
    el.offsetHeight; // Trigger reflow
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    el.style.opacity = 1;
    el.style.transform = 'scale(1)';
    done();
};

const leave = (el, done) => {
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    el.style.opacity = 0;
    el.style.transform = 'scale(0.95)';
    done();
};
console.log('Current Image:', currentImage.value);

// Log the index and image URL for each image
postImages.value.forEach((img, index) => {
    console.log(`Image ${index}:`, img.post_image_path);
});

</script>

<template>
    <q-card @click="showDialog = true" class="my-card1">
        <q-img
            :key="currentImage" 
            :src="`/storage/images/${currentImage}`"
            alt="Post Image"
            class="aspect-ratio-image1"
            style="object-fit: cover"
        >
            <div class="absolute-bottom text-subtitle2 text-center">
                {{ post.title }}
            </div>
        </q-img>
    </q-card>

    <q-dialog v-model="showDialog" backdrop-filter="blur(4px) saturate(150%)">
        <q-card class="instagram-dialog my-card row no-wrap">
        
            <div class="carousel-container" style="background:black"@click.stop>
          
            <button 
                v-if="showArrows" 
                class="carousel-control prev" 
                @click="prevImage"
            >
                ❮
            </button>
            <div class="carousel-images">
                <transition name="fade" @before-enter="beforeEnter" @enter="enter" @leave="leave">
                    <img 
                        :key="currentImage" 
                        :src="`/storage/images/${currentImage}`" 
                        alt="Post Image" 
                        class="carousel-image" 
                    />
                </transition>
            </div>
     
            <button 
                v-if="showArrows" 
                class="carousel-control next" 
                @click="nextImage"
            >
                ❯
            </button>
        </div>

        
            <div class="col-3 flex flex-col post-details">
     
                <div class="flex items-center p-4 border-b">
                    <q-avatar size="42px">
                        <img
                            style="background: white; border-radius: 50%; width: 42px; height: 42px;"
                            :src="
                                post.user.profile_image_url
                                    ? `/storage/images/${post.user.profile_image_url}`
                                    : '/storage/images/TEMPPROFILE.png'
                            "
                            alt="Profile Image"
                        />
                    </q-avatar>
                    <div class="ml-3">
                        <span class="font-medium text-sm">{{
                            post.user.name
                        }}</span
                        ><br />
                        <span class="text-xs text-gray-500">{{
                            formattedDate
                        }}</span>
                    </div>
                </div>

         
                <div class="flex-grow p-4 overflow-auto comments-section">
                    <h2 class="text-xl font-bold text-black mb-2">
                        {{ post.title }}
                    </h2>
                    <div class="text-caption text-black mb-2">
                        {{ post.description }}
                    </div>
                    <q-separator class="mb-4" />
                    <div class="instagram-comments">
                        <template v-if="comments && comments.length">
                            <div
                                v-for="(comment, index) in displayedComments"
                                :key="comment.id"
                                class="comment-item mt-2 mb-1"
                            >
                                <div class="comment-content">
                                    <p class="font-semibold text-black mr-2">
                                        {{ comment.user.username }}:
                                    </p>
                                    <p class="text-gray-700">
                                        {{ comment.comments }}
                                    </p>
                                </div>
                            </div>
                            <template v-if="comments.length > maxComments">
                                <button
                                    @click="toggleComments"
                                    class="text-blue-500 mt-2"
                                >
                                    {{
                                        showAll ? "See Less..." : "See More..."
                                    }}
                                </button>
                            </template>
                        </template>
                        <template v-else>
                            <p class="text-gray-500">No comments yet.</p>
                        </template>
                    </div>
                </div>

              
                <div class="flex items-center p-4 border-t actions-section">
                    <Link
                        @click.stop
                        :href="`/post/${post.id}/like`"
                        method="post"
                        as="button"
                        type="button"
                        preserve-scroll
                        class="flex items-center text-blue-500 hover:text-blue-700"
                    >
                        <HeartButton
                            class="w-4 h-4"
                            :name="post.liked ? 'heart' : 'heart-outline'"
                            @click="handleLikeToggle"
                        />
                        <span
                            class="ml-2 text-black"
                            style="font-weight: bold"
                            >{{ post.likes_count }}</span
                        >
                    </Link>
                </div>

            
                <form @submit.prevent="submit">
            <div class="flex items-center p-2 border-t" @click.stop>
                <q-input
                    filled
                    placeholder="Add a comment..."
                    class="flex-grow"
                    v-model="form.comment"
                />
                <q-btn
                    rounded
                    flat
                    label="Post"
                    icon="fa-solid fa-paper-plane"
                    type="submit"
                />
            </div>
            <div v-if="errorMessage" class="q-pa-md text-red-500">
                {{ errorMessage }}
            </div>
            <div v-if="successMessage" class="q-pa-md text-green-500">
                {{ successMessage }}
            </div>
        </form>
            </div>
      
        </q-card>
    </q-dialog>
</template>

<style scoped>
.aspect-ratio-image1 {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.my-card1 {
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.instagram-card {
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 12px 17px 51px rgba(0, 0, 0, 0.22);
    /* margin: 0 auto; */
    /* margin-left: auto;
    margin-right: 20px; */
    margin-bottom: 20px;
    backdrop-filter: blur(6px);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.5s;
    user-select: none;
    font-weight: bolder;
    color: black;
    background: white;
    font-family: "Poppins", sans-serif;
    overflow: hidden;
}

.aspect-ratio-container {
    position: relative;
    width: 100%;
    padding-top: 56.25%; /* 16:9 aspect ratio */
    background-color: black; /* Black filler */
}

.aspect-ratio-img {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.instagram-card:hover {
    border: 1px solid black;
}
.instagram-card-header,
.instagram-card-footer {
    display: flex;
    align-items: center;
    padding: 10px;
}

.instagram-card-user-image {
    border-radius: 50%;
    width: 42px;
    height: 42px;
    margin-right: 10px;
}

.instagram-card-user-name {
    color: #333;
    text-decoration: none;
    font-weight: bold;
}

.instagram-card-image img {
    width: 100%;
    height: auto;
    display: block;
}

.instagram-card-content {
    padding: 10px;
}

.instagram-card-icon {
    color: #333;
    text-decoration: none;
    margin-right: 10px;
    cursor: pointer;
}

.instagram-card-icon.instagram-bookmark {
    margin-left: auto;
}

.instagram-card-content-user,
.hashtag {
    color: #003569;
    text-decoration: none;
    font-weight: bold;
}

.instagram-card-input {
    border: none;
    flex: 1;
    padding: 10px;
    margin-right: 10px;
    border-radius: 10px;
}

.instagram-card-footer .fa-ellipsis-h {
    cursor: pointer;
}

.instagram-dialog {
    max-width: 90vw;
    max-height: 90vh;
}

.image-container {
    overflow: hidden;
}

.instagram-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.post-details {
    display: flex;
    flex-direction: column;
    max-height: 100%;
}

.comments-section {
    overflow-y: auto;
}

.instagram-comments {
    max-height: 35vh;
    overflow-y: auto;
}

.my-card {
    display: flex;
    flex-direction: row;
    padding: 0;
    width: 85vw;
    height: 80vh;
}

.q-card-section {
    padding: 0;
}

.q-separator {
    margin: 0;
}

.instagram-actions svg {
    width: 24px;
    height: 24px;
}

.comment-content {
  display: flex;
  flex-direction: row; /* Ensures items are aligned horizontally */
}

.carousel-container {
    position: relative;
    width: 100%;
    height: 100%;
}

.carousel-images {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    transition: opacity 0.5s ease; /* Smooth transition for opacity */
}

.carousel-image {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain; /* Maintains aspect ratio and fits within the container */
}
.carousel-control {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background-color: rgba(0, 0, 0, 0.5);
    color: white;
    border: none;
    padding: 10px;
    cursor: pointer;
    z-index: 1000;
    font-size: 24px; /* Adjust size of the arrows */
}

.prev {
    left: 10px;
}

.next {
    right: 10px;
}

/* Transition effects for carousel */
.carousel-enter-active, .carousel-leave-active {
    transition: opacity 0.5s ease, transform 0.5s ease;
}

.carousel-enter, .carousel-leave-to {
    opacity: 0;
    transform: scale(0.95); /* Optional: Scale down during the transition */
}

/* Optional: Add additional scale effect for entering */
.carousel-enter {
    opacity: 0;
    transform: scale(1.05); /* Optional: Scale up during the transition */
}
</style>
