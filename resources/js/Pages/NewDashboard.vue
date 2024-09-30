<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Performance Dashboard</h2>
        </template>
        <div class="dashboard-container py-12">
            <!-- Main content -->
            <div class="main-content flex-1">
                <q-card class="welcome-card">
                    <q-card-section>
                        <h3 class="text-2xl font-bold mb-2">Hello,</h3>
                        <p class="text-3xl font-semibold">Juan Dela Cruz</p>
                    </q-card-section>
                </q-card>

                <q-card class="feelings-tracker-card">
                    <q-card-section>
                        <h3 class="text-lg font-semibold mb-3">Feelings Tracker</h3>
                        <p class="text-sm">How are you feeling today?</p>
                    </q-card-section>
                </q-card>

                <q-card class="announcement-card">
                    <q-card-section>
                        <h3 class="text-lg font-semibold mb-3">Announcement</h3>
                        <ul class="announcements-list">
                            <li>*Pre-Survey Link*</li>
                            <li>*ZOOM LINK*</li>
                        </ul>
                    </q-card-section>
                </q-card>
            </div>

            <!-- Right column content -->
            <div class="right-column">
                <q-card class="progress-card">
                    <q-card-section>
                        <div class="progress-header">
                            <h3 class="text-lg font-semibold">Number of Sessions</h3>
                            <p class="text-4xl font-bold text-center">3</p>
                        </div>
                        <div class="progress-info">
                            <p class="text-lg font-semibold">Progress Level</p>
                            <p class="text-4xl font-bold text-green-500">92%</p>
                        </div>
                    </q-card-section>
                </q-card>

                <q-card class="assessment-card">
                    <q-card-section>
                        <h3 class="text-lg font-semibold mb-3">Assessment</h3>
                        <p>09/29/2024 8:00 PM</p>
                        <div class="assessment-content">
                            <p class="assessment-bar">[Assessment Content]</p>
                        </div>
                    </q-card-section>
                </q-card>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref, computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import { useFollowStore } from '@/Components/useFollowStore.js';
const { props } = usePage();
</script>
<style scoped>
.dashboard-container {
    display: flex;
    justify-content: center;
    gap: 50px; /* Space between the main content and right column */
    padding: 50px 20px; /* Add padding to the container */
}

.main-content {
    flex: 1;
    max-width: 700px; /* Adjust based on your desired width for the Card content */
}

.right-column {
    width: 400px;
    flex-shrink: 0; /* Prevent the right column from shrinking */
}

/* Media query to stack the right column on top of the main content on small screens */
@media (max-width: 1024px) {
    .dashboard-container {
        flex-direction: column; /* Stack the columns vertically */
        padding: 50px 20px; /* Ensure padding remains on smaller screens */
        gap: 20px; /* Adjust the gap between the stacked elements */
    }

    .main-content {
        max-width: 100%; /* Allow the main content to take full width */
        margin: 0 auto; /* Center the main content */
    }

    .right-column {
        width: 100%; /* Allow the right column to take full width */
        order: -1; /* Move the right column above the main content */
    }
}

/* Card styles */
.welcome-card,
.feelings-tracker-card,
.announcement-card,
.progress-card,
.assessment-card {
    margin-bottom: 20px;
    border: 1px solid #ddd;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    padding: 15px;
    background-color: #ffffff;
}

.feelings-tracker-icons {
    display: flex;
    justify-content: space-around;
    margin-top: 10px;
}

.feelings-icons {
    width: 40px;
    height: 40px;
}

.announcements-list {
    list-style: none;
    padding: 0;
}

.announcement-card li {
    margin-bottom: 5px;
}

/* Progress section */
.progress-header {
    text-align: center;
}

.progress-info {
    text-align: center;
    margin-top: 10px;
}

.book-session-btn {
    margin-top: 20px;
    width: 100%;
}

.assessment-bar {
    background-color: #ddd;
    height: 20px;
    margin-top: 10px;
    border-radius: 5px;
}
</style>