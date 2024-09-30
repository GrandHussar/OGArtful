<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

</script>

<template>
    <GuestLayout>
        <Head title="Register" />
            <div>
                <h1 class="text-3xl font-bold self-center mb-3 ml-3">Register as:</h1>
                <div class="flex flex-col gap-6 rounded-box p-4 max-w-md">

                <Link :href="route('register-user')" align="left">
                    <div class="flex flex-col rounded-box max-w-mdr">
                        <q-btn push rounded color="primary" align="left" size="lg"> 
                            <q-icon name="fa-solid fa-user"></q-icon>
                            <div class="ml-4">Register as a User</div>
                        </q-btn>
                    </div>
                </Link>
                <Link :href="route('register-therapist')" align="left">
                    <div class="flex flex-col rounded-box max-w-mdr">
                        <q-btn push rounded color="primary" align="left" size="lg"> 
                            <q-icon name="fa-solid fa-user-doctor"></q-icon>
                            <div class="ml-4"> Register as a Therapist</div>
                        </q-btn>
                    </div>
                </Link>
                </div>
            </div>
    </GuestLayout>
</template>
