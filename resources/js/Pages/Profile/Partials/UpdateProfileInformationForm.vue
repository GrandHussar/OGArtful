<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Profile Information</h2>

            <p class="mt-1 text-sm text-gray-600">
                Update your account's profile information, email address, and profile picture.
            </p>
        </header>

        <form @submit.prevent="submitForm" class="mt-6 space-y-6">
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="profile_picture" value="Profile Picture" />

                <file-pond 
                        name="image"
                        ref="pond"
                        class-name="my-pond"
                        label-idle="Drop files here or click to upload"
                        allow-multiple="true"
                        accepted-file-types="image/jpeg, image/png"
                        :server="{
                            url:'',
                            process: {
                                url:'/upload-image',
                                method: 'POST',
                                onload: handleFilePondLoad
                            },
                            revert: handleFilePondRevert,
                            headers:{
                                'X-CSRF-TOKEN': $page.props.csrf_token
                            }
            
                        }"
                    ></file-pond>

                <InputError class="mt-2" :message="form.errors.profile_picture" />
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="text-sm mt-2 text-gray-800">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 font-medium text-sm text-green-600"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                </Transition>
            </div>
        </form>
    </section>
</template>

<script setup>
import { onMounted } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage, router } from '@inertiajs/vue3';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';
import 'filepond/dist/filepond.min.css';
import vueFilePond from "vue-filepond";
import { useQuasar } from 'quasar'
const FilePond = vueFilePond(FilePondPluginImagePreview);

const $q = useQuasar()

defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

onMounted(() => {
  const notification = localStorage.getItem('profile_updated');
    if (notification === 'success') {
        $q.notify({
            position: 'top-right',
            message: 'Successful Update of Info!',
            color: 'green',
            icon: 'fa-solid fa-check',
            actions: [
                { icon: 'close', color: 'white', round: true, handler: () => { /* ... */ } }
            ]
        });
        localStorage.removeItem('profile_updated'); // Clear the flag
    }
});
const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    profile_image_url: [], // Add profile picture to the form
});

const handleFileUpload = (event) => {
    form.profile_picture = event.target.files[0];
};

const submitForm = () => {
    form.patch(route('profile.update'), {
        onSuccess: () => {
            window.location.reload();
            localStorage.setItem('profile_updated', 'success');
        }
    });
};
function handleFilePondRevert(uniqueId, load, error){
    form.profile_image_url = form.profile_image_url.filter((image) => image !== uniqueId);
    router.delete('/revert/' + uniqueId);
    load();
}
function handleFilePondLoad(response){
    form.profile_image_url.push(response); 
    return response;
}
</script>