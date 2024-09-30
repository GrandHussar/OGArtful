<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    license_id: '',
});

const submit = () => {
    form.post(route('register-therapist'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const handleSubmit = () => { 
    submit(); 
};

</script>

<template>
     <GuestLayout>
        <Head title="Register as Therapist" />
        <form @submit.prevent="handleSubmit">
            <div class="flex flex-col gap-4 rounded-box p-6 max-w-md">
                
                <h1 class="text-3xl font-bold self-center">Register as Therapist</h1>
                
                <span class="self-center">
                    Already have an account?
                    <a :href="route('login')" class="link link-secondary">Log in</a>
                </span>

                <label class="form-control">
                    <q-input outlined v-model="form.name" label="Name" type="text" autocomplete="first-name"/>
                <InputError class="mt-2" :message="form.errors.name" />
                </label>
                <label class="form-control">
                    <q-input outlined v-model="form.username" label="Username" type="text" autocomplete="username"/>
                <InputError class="mt-2" :message="form.errors.username" />
                </label>

                <label class="form-control">
                    <q-input outlined v-model="form.email" label="Email" type="text" autocomplete="username"/>
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

                <label class="form-control">
                    <q-input
                        outlined
                        v-model="form.license_id"
                        label="License ID"
                        type="text"
                        autocomplete="license_id"
                        :rules="[val => /^[0-9]{1,7}$/.test(val) || 'License ID must be between 0000000 and 9999999']"
                        required
                    />
                    <InputError class="mt-2" :message="form.errors.license_id" />
                </label>

                <q-btn push color="primary" rounded text-color="white" size="lg" type="submit">Register</q-btn>
        </div>
    </form>
    </GuestLayout>
    <!-- <GuestLayout>
        <Head title="Register-User" />

        <form @submit.prevent="submit">
            <div>
                <q-input outlined v-model="form.name" label="Name" type="text" autocomplete="name"/>
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <q-input outlined v-model="form.email" label="Email" type="text" autocomplete="username"/>
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <q-input outlined v-model="form.password" label="Password" type="password" autocomplete="current-password"/>
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <q-input outlined v-model="form.password_confirmation" label="Confirm Password" type="password" autocomplete="current-password"/>
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
            </div>

            <div class="mt-4">
                <q-input outlined v-model="form.license_id" label="License ID" type="number" autocomplete="license_id" required min="1000000" max="9999999"/>
                <InputError class="mt-2" :message="form.errors.license_id" />
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    :href="route('login')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Already registered?
                </Link>

                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout> -->
</template>


