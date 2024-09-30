<template>
  <AuthenticatedLayout>
    <div class="profile-page">
      <header class="profile-header">
        <q-avatar size="100px" class="profile-avatar">
          <img :src="user.profile_image_url ? `/storage/images/${user.profile_image_url}` : '/storage/images/TEMPPROFILE.png'" alt="Profile Image" />
        </q-avatar>
        <div class="profile-info">
          <div class="profile-header-content">
            <div class="profile-name-handle">
              <h6 class="profile-username">{{ user.name }}</h6>
              <span class="profile-twitter-handle">@{{ user.username }}</span>
            </div><!-- Username under the name -->
            <!-- Show FollowButton only if the profile is not the current user's profile -->
            <FollowButton
                v-if="user.id !== authUserId"
                @click.stop
                :is-following="isFollowing(user.id)"
                @toggle-follow="toggleFollow(user.id)"
                class="ml-4"
            />
          </div>
          <p class="profile-bio">{{ user.bio }}</p>
          <div class="profile-stats">
            <span>{{ posts.data.length }} Posts</span>
            <span>{{ followersCount }} Followers</span>
            <span>{{ followingCount }} Following</span>
          </div>
        </div>
      </header>

      <div class="profile-posts">
        <div class="grid grid-cols-3 gap-2">
          <template v-for="post in posts.data" :key="post.id">
            <CardSmall :user="user" :post="post" :comments="comments[post.id] || []"/>
          </template>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';
import CardSmall from '@/Components/CardSmall.vue';
import { useFollowStore } from '@/Components/useFollowStore.js';
import FollowButton from "@/Components/FollowButton.vue";

const { props } = usePage();

const posts = ref(props.posts);
const comments = ref(props.comments);
const user = ref(props.user);
const followersCount = ref(props.followersCount);
const followingCount = ref(props.followingCount);

// Current authenticated user's ID
const authUserId = ref(props.authUserId);

const followStore = useFollowStore();
const isFollowing = (userId) => followStore.followingUsers.has(userId);
const toggleFollow = async (userId) => {
  await followStore.toggleFollow(userId);
};
</script>
<style scoped>
.profile-name-handle {
  display: flex;
  flex-direction: column; /* Stack name and handle vertically */
}

.profile-username {
  font-size: 24px;
  font-weight: bold;
  padding:0;
}

.profile-twitter-handle {
  font-size: 14px;
  color: #657786; /* A soft gray color similar to Twitter */
}
.profile-page {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

.profile-header {
  display: flex;
  align-items: center;
  border-bottom: 1px solid #eaeaea;
  padding-bottom: 20px;
}

.profile-avatar {
  margin-right: 20px;
  background: white;
}

.profile-info {
  flex-grow: 1;
}

.profile-header-content {
  display: flex;
  align-items: center;
}



.profile-bio {
  margin: 5px 0;
}

.profile-stats {
  display: flex;
  gap: 20px;
  margin-top: 10px;
  font-size: 14px;
}

.profile-posts {
  margin-top: 20px;
}

.post-item {
  position: relative;
  width: 100%;
  padding-bottom: 100%; /* 1:1 Aspect Ratio */
  background: white;
}

.post-image {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}
</style>