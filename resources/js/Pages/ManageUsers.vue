<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { ref, computed, onMounted } from "vue";
import { usePage, useForm, router } from "@inertiajs/vue3";
import { debounce } from "lodash";

const { auth } = usePage().props;

// Check if the user is an admin
const isAdmin = computed(() => {
    const roles = auth.user.roles.map((role) => role.name);
    return roles.includes("super_admin") || roles.includes("it_admin");
});

// Redirect non-admin users
if (!isAdmin.value) {
    router.visit(route("dashboard"));
}

// State variables
const users = ref([]);
const search = ref("");
const errorMessage = ref("");
const successMessage = ref("");
const form = useForm({
    id: null,
    name: "",
    email: "",
    username: "",
    role: "",
    is_restricted: false,
    restriction_end_at: null,
});

// Fetch users based on search input
const fetchUsers = debounce(() => {
    router.get(
        route("manage.users"),
        { search: search.value },
        {
            preserveState: true,
            onSuccess: (page) => {
                users.value = page.props.users;
            },
        }
    );
}, 300);

// Edit user details
const editUser = (user) => {
    if (
        user.roles.some(
            (role) => role.name === "super_admin" || role.name === "it_admin"
        )
    ) {
        alert("You cannot edit this user.");
        return;
    }

    form.id = user.id;
    form.name = user.name;
    form.email = user.email;
    form.username = user.username;
    form.role = user.roles.length ? user.roles[0].name : "";
    form.is_restricted = user.is_restricted;
    form.restriction_end_at = user.restriction_end_at;
};

// Update user details
const updateUser = () => {
    if (confirm("Are you sure you want to update this user?")) {
        errorMessage.value = "";
        successMessage.value = "";

        form.put(route("manage.users.update", form.id), {
            onSuccess: () => {
                successMessage.value = "User updated successfully!";
                fetchUsers();
                form.id = null; // Close the edit form
            },
            onError: (errors) => {
                errorMessage.value =
                    "An error occurred while updating the user.";
            },
        });
    }
};

// Restrict or unrestrict a user
const restrictUser = (user) => {
    const action = user.is_restricted ? "unrestrict" : "restrict";
    if (confirm(`Are you sure you want to ${action} this user?`)) {
        router.post(
            route("manage.users.restrict", user.id),
            {},
            {
                onSuccess: () => {
                    fetchUsers();
                },
                onError: (errors) => {
                    errorMessage.value =
                        "An error occurred while restricting the user.";
                },
            }
        );
    }
};

// Fetch initial user list
onMounted(() => {
    fetchUsers();
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="container mx-auto p-6">
            <h1 class="text-2xl font-bold mb-6">Manage Users</h1>

            <div class="mb-4">
                <input
                    type="text"
                    v-model="search"
                    @input="fetchUsers"
                    placeholder="Search users..."
                    class="w-full p-2 border border-gray-300 rounded"
                />
            </div>

            <div v-if="errorMessage" class="text-red-500 mb-4">
                {{ errorMessage }}
            </div>
            <div v-if="successMessage" class="text-green-500 mb-4">
                {{ successMessage }}
            </div>

            <div
                v-for="user in users"
                :key="user.id"
                class="p-4 mb-4 border border-gray-300 rounded"
            >
                <p>{{ user.name }} ({{ user.username }})</p>
                <p>{{ user.email }}</p>
                <p>
                    Role:
                    {{
                        user.roles.length
                            ? user.roles[0].name
                            : "No role assigned"
                    }}
                </p>
                <p v-if="user.is_restricted" class="text-red-500">
                    Account is restricted until {{ user.restriction_end_at }}
                </p>
                <button
                    @click="editUser(user)"
                    class="mr-2 p-2 bg-blue-500 text-white rounded"
                >
                    Edit
                </button>
                <button
                    @click="restrictUser(user)"
                    class="p-2 bg-red-500 text-white rounded"
                >
                    {{ user.is_restricted ? "Unrestrict" : "Restrict" }} Account
                </button>
            </div>

            <div
                v-if="form.id"
                class="fixed inset-0 flex items-center justify-center bg-gray-800 bg-opacity-50"
            >
                <div class="bg-white p-6 rounded shadow-lg">
                    <h2 class="text-xl font-bold mb-4">Edit User</h2>
                    <form @submit.prevent="updateUser">
                        <div class="mb-4">
                            <label class="block mb-1">Name</label>
                            <input
                                type="text"
                                v-model="form.name"
                                class="w-full p-2 border border-gray-300 rounded"
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block mb-1">Email</label>
                            <input
                                type="email"
                                v-model="form.email"
                                class="w-full p-2 border border-gray-300 rounded"
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block mb-1">Username</label>
                            <input
                                type="text"
                                v-model="form.username"
                                class="w-full p-2 border border-gray-300 rounded"
                            />
                        </div>
                        <div class="mb-4">
                            <label class="block mb-1">Role</label>
                            <select
                                v-model="form.role"
                                class="w-full p-2 border border-gray-300 rounded"
                            >
                                <option
                                    v-if="
                                        auth.user.roles.includes('super_admin')
                                    "
                                    value="it_admin"
                                >
                                    IT Admin
                                </option>
                                <option value="user">User</option>
                                <option value="therapist">Therapist</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label class="block mb-1">
                                <input
                                    type="checkbox"
                                    v-model="form.is_restricted"
                                />
                                Restrict Account
                            </label>
                        </div>
                        <div class="mb-4" v-if="form.is_restricted">
                            <label class="block mb-1"
                                >Restriction End Date</label
                            >
                            <input
                                type="date"
                                v-model="form.restriction_end_at"
                                class="w-full p-2 border border-gray-300 rounded"
                            />
                        </div>
                        <div class="flex justify-end">
                            <button
                                type="button"
                                @click="form.id = null"
                                class="mr-2 p-2 bg-gray-500 text-white rounded"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="p-2 bg-blue-500 text-white rounded"
                            >
                                Update User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.user-card {
    border: 1px solid #ddd;
    padding: 10px;
    margin-bottom: 10px;
}
.error {
    color: red;
}
.success {
    color: green;
}
</style>
