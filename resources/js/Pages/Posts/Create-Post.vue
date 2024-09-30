<!-- <script setup>
    import { ref } from 'vue';
    import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
    import { Head, Link, useForm } from "@inertiajs/vue3";

    
    const form = useForm({
        title: '',
        description: '',
        image_url: null,
    });

    const submit = () => {
        errorMessage.value = '';
        form.post(route('post.uploadPost'))
    }

    const imageUrl = ref(null);
    const errorMessage = ref('');

    const handleFileUpload = (event) => {
        const file = event.target.files[0];
        if (file) {
            form.image_url = file;
            const reader = new FileReader();
            reader.onload = (e) => {
                imageUrl.value = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            form.image_url = null;
            imageUrl.value = null;
        }
};

</script> -->

<!-- <template>
    <AuthenticatedLayout>
    <div class="flex flex-col bg-white shadow mt-10 rounded-box max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-start p-12"><h4>Create Post</h4></div>
        
        <div class="flex flex-col gap-6 p-6">
        <form @submit.prevent="submit">
         
            <input type="file" class="ml-5 bg-base-200 file-input file-input-bordered w-full mb-5 max-w-xs" @input="form.image_url = $event.target.files[0]"  @change="handleFileUpload" accept="image/*" />
            <div v-if="imageUrl" class="q-pa-md">
                <img :src="imageUrl" class="rounded-lg max-w-xs mx-auto" alt="Uploaded Image"/>
            </div>
      
            <div class="q-pa-md">
                <q-input v-model="form.title" rounded outlined type="text" label="Title"/>
            </div>
            
         
            <div class="q-pa-md">
                <q-input v-model="form.description" rounded outlined type="textarea" label="Description"/>
            </div>
            
      
            <div class="q-pa-md right">
                <q-btn color="primary" label="Submit" type="submit" />
            </div>
        </form>
    </div>
  </div>
    </AuthenticatedLayout>
</template> -->

<script setup>
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from "@inertiajs/vue3";

const form = useForm({
    title: '',
    description: '',
    image_url: null,
});

const imageUrl = ref(null);
const errorMessage = ref('');

const handleFileUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.image_url = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imageUrl.value = e.target.result;
        };
        reader.readAsDataURL(file);
    } else {
        // Clear image if no file is selected
        form.image_url = null;
        imageUrl.value = null;
    }
};

const submit = () => {
    errorMessage.value = ''; // Clear any previous error messages

    // Print the form data for debugging purposes
    console.log('Form Data:', {
        title: form.title,
        description: form.description,
        image_url: form.image_url,
    });

    // Check if all required fields are filled
    if (!form.title || !form.description) {
        errorMessage.value = 'Title and Description are required.';
        return;
    }

    form.post(route('post.uploadPost'), {
        onSuccess: () => {
            // Handle successful form submission
            console.log('Form submitted successfully!');
        },
        onError: (errors) => {
            // Handle errors from the server
            console.error('Form submission error:', errors);
            errorMessage.value = 'An error occurred while submitting the form.';
        }
    });
};
</script>

<template>
<AuthenticatedLayout>
    <div class="flex flex-col bg-white shadow mt-10 rounded-box max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-start p-12">
            <h4>Create Post</h4>
        </div>
        
        <div class="flex flex-col gap-6 p-6">
            <form @submit.prevent="submit">
                <!-- File input for image upload -->
                <input 
                    type="file" 
                    class="ml-5 bg-base-200 file-input file-input-bordered w-full mb-5 max-w-xs" 
                    @change="handleFileUpload" 
                    accept="image/*" 
                />
                
                <div v-if="imageUrl" class="q-pa-md">
                    <img :src="imageUrl" class="rounded-lg max-w-xs mx-auto" alt="Uploaded Image"/>
                </div>
                
                <!-- Title input -->
                <div class="q-pa-md">
                    <q-input v-model="form.title" rounded outlined type="text" label="Title" />
                </div>
                
                <!-- Description input -->
                <div class="q-pa-md">
                    <q-input v-model="form.description" rounded outlined type="textarea" label="Description" />
                </div>

                <!-- Error message display -->
                <div v-if="errorMessage" class="q-pa-md text-red-500">
                    {{ errorMessage }}
                </div>
                
                <!-- Submit button -->
                <div class="q-pa-md right">
                    <q-btn color="primary" label="Submit" type="submit" />
                </div>
            </form>
        </div>
    </div>
</AuthenticatedLayout>
</template>