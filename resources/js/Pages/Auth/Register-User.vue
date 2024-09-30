<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, defineEmits } from 'vue';

const emit = defineEmits(['onAuth']);
// Use useForm to initialize the form object
const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

// Function to handle form submission
const submit = () => {
    form.post(route('register-user'));
};

// Function to handle user registration
// const register = () => {
//     // Replace with your actual signupRest logic, using form values
//     signupRest(form.username, form.password, form.email, form.first_name, form.last_name)
//         .then((response) => {
//             emit("onAuth", { ...response.data, secret: form.password });
//       // Optionally, emit an event or perform other actions on successful registration
//   })
//         .catch((error) => {
//             console.log('Registration error', error);
//         });
// };

// Combined function to handle form submission and registration
const handleSubmit = () => {
    submit();
};

</script>


<template>
    <GuestLayout>
        <Head title="Register as User" />
        <form @submit.prevent="handleSubmit">
            <div class="flex flex-col gap-4 rounded-box p-6 max-w-md">
                
                <h1 class="text-3xl font-bold self-center">Register as User</h1>
                
                <span class="self-center">
                    Already have an account?
                    <a :href="route('login')" class="link link-secondary">Log in</a>
                </span>
                
                <label class="form-control">
                    <q-input outlined v-model="form.name" label="Name" type="text" autocomplete="name"/>
                <InputError class="mt-2" :message="form.errors.name" />
                </label>

                <label class="form-control">
                    <q-input outlined v-model="form.username" label="Username" type="text" autocomplete="username"/>
                <InputError class="mt-2" :message="form.errors.username" />
                </label>


                <label class="form-control">
                    <q-input outlined v-model="form.email" label="Email" type="text" autocomplete="email"/>
                <InputError class="mt-2" :message="form.errors.email" />
                </label>

                <label class="form-control">
                    <q-input outlined v-model="form.password" label="Password" type="password" autocomplete="current-password"/>
                    <InputError class="mt-2" :message="form.errors.password" />
                </label>

                <label class="form-control">
                    <q-input outlined v-model="form.password_confirmation" label="Confirm Password" type="password" autocomplete="current-password"/>
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </label>

                <q-btn push color="primary" rounded text-color="white" size="lg" type="submit">Register</q-btn>
        </div>
        </form>
    </GuestLayout>
</template>
