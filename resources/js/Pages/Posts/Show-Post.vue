<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  post: Object,
});
</script>

<template>
<AuthenticatedLayout>
    <div class="flex flex-col bg-white shadow rounded-box max-w-7xl mx-auto sm:px-6 lg:px-8 mb-5" >
        <div class="flex gap-6 p-6">
            <q-btn flat rounded align="left">
                <q-avatar size="42px">
                    <img alt="Profile" src="https://api.dicebear.com/6.x/fun-emoji/svg?seed=Luigi"/>
                </q-avatar>
                <span class="ml-3 font-medium text-sm">{{ $page.props.auth.user.username }}</span> 
            </q-btn>
            
        </div>
        <div class="flex flex-col gap-6 p-6 ml-10">
            <h4 class="text-xl font-bold">{{ post.title }}</h4>
        
            <span>
                {{ post.description }}
            </span>
        </div>
        <div v-if="post.image_url" class="flex justify-center h-auto bg-black rounded-lg">
            <img
                :src="(`/storage/${post.image_url}`)"
                :alt="post.title"
                class="max-w-96 h-auto"
            />
        </div>
        <div class="flex flex-col gap-6 p-6">
            <div class="flex gap-2 justify-between items-center">
                <span class="text-sm">Jan 10 2024</span>
            </div>
        </div>
    </div>
</AuthenticatedLayout>
</template>

