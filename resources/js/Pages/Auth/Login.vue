<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import ChatPage from '@/Pages/Chat/ChatPage.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, defineEmits } from 'vue';

const emit = defineEmits(['onAuth']);

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};

// const login = () => {
//     loginRest(form.username, form.password)
//     .then((response) => {
//       // Emit an event on successful login
//       emit('onAuth', { ...response.data, secret: form.password });
//     })
//     .catch((error) => {
//       console.log('Login error', error);
//     });
// };

const handleSubmit = () => {
    submit();
};

</script>

<template>
    <GuestLayout>
        <Head title="Log in" />
        <form @submit.prevent="handleSubmit">
            <div class="flex flex-col gap-4 rounded-box p-6 max-w-md">
                <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
                    {{ status }}
                </div>
                
                <h1 class="text-3xl font-bold self-center">Log in</h1>
                
                <span class="self-center">
                    Don't have an account?
                    <a :href="route('register-user')" class="link link-secondary">Register</a>
                </span>

                <a :href="route('google.login')" class="btn btn-neutral">
                    
                    <q-icon name="fa-brands fa-google text-primary"></q-icon>
                    Log in with Google
                </a>
    

                <div class="divider">OR</div>

                <label class="form-control">
                    <q-input outlined v-model="form.username" label=" Username" autocomplete="username" />
                    <InputError class="mt-2" :message="form.errors.username" />
                </label>

                <label class="form-control">
                    <div class="label">
                        <q-space />
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >Forgot password?</Link>
                    </div>

                    <q-input outlined v-model="form.password" label="Password" type="password" autocomplete="current-password"/>
                    <InputError class="mt-2" :message="form.errors.password" />
                </label>

                <div class="form-control">
                    <label class="cursor-pointer label self-start gap-2">
                        <Checkbox name="remember" v-model:checked="form.remember" />
                        <span class="label-text">Remember me</span>
                    </label>
                 </div>

                 <q-btn push color="primary" rounded text-color="white" size="lg" type="submit">Log in</q-btn>
        </div>
    </form>
    </GuestLayout>
</template>
