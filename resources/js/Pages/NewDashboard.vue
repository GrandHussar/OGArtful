<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Performance Dashboard</h2>
        </template>
        <div class="dashboard-container py-12">
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

                        <v-calendar
                            v-model="selectedDate"
                            :attributes="calendarAttributes"
                            expanded
                            @dayclick="openMoodPicker"
                        ></v-calendar>

                        <!-- Use q-dialog for the mood picker modal -->
                        <q-dialog v-model="showPicker">
                            <q-card>
                                <q-card-section>
                                    <h3>Select Your Mood for {{ selectedDate }}</h3>
                                    <div class="mood-options">
                                        <button
                                            v-for="mood in moods"
                                            :key="mood.value"
                                            @click="selectMood(mood.value)"
                                            :class="['mood-button', `mood-${mood.value}`]"
                                        >
                                            {{ mood.label }}
                                        </button>
                                    </div>
                                </q-card-section>
                                <q-card-actions>
                                    <q-btn label="Close" @click="closePicker" />
                                </q-card-actions>
                            </q-card>
                        </q-dialog>

                        <!-- Display validation errors -->
                        <div v-if="errors" class="error-messages">
                            <ul>
                                <li v-for="(msgs, field) in errors" :key="field">
                                    {{ field }}:
                                    <ul>
                                        <li v-for="msg in msgs" :key="msg">{{ msg }}</li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
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
import { ref, onMounted, watch } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';
import { useToast } from 'vue-toastification';
import moment from 'moment';

import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

// Initialize toast notifications
const toast = useToast();

// Reactive references
const selectedDate = ref(new Date().toISOString().substr(0, 10));
const showPicker = ref(false);
const moods = ref([
  { value: 'happy', label: '😊 Happy' },
  { value: 'sad', label: '😢 Sad' },
  { value: 'neutral', label: '😐 Neutral' },
  { value: 'excited', label: '🤩 Excited' },
  { value: 'angry', label: '😠 Angry' },
]);
const userMoods = ref([]);
const calendarAttributes = ref([]);
const errors = ref(null);
const loading = ref(false);
const moodColors = {
  happy: 'green',
  sad: 'blue',
  neutral: 'orange',
  excited: 'yellow',
  angry: 'red',
};
// Create a form instance
const form = useForm({
  date: '',
  mood: ''
});

// Fetch moods from the backend
const fetchMoods = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/moods'); // Points to /moods
    console.log('Fetched Moods:', response.data);
    if (Array.isArray(response.data)) {
      userMoods.value = response.data;
    } else {
      console.error('Unexpected response format:', response.data);
      toast.error('Failed to load moods.');
    }
  } catch (error) {
    console.error('Error fetching moods:', error);
    toast.error('Failed to load moods.');
  } finally {
    loading.value = false;
  }
};

// Open the mood picker modal
const openMoodPicker = (day) => {
  console.log('Day clicked:', day);
  selectedDate.value = day.date;
  showPicker.value = true;
  console.log('showPicker:', showPicker.value);
};

// Close the mood picker modal
const closePicker = () => {
  showPicker.value = false;
};

// Select and save a mood
const selectMood = async (mood) => {
  loading.value = true;
  form.date = moment(selectedDate.value).format('YYYY-MM-DD');
  form.mood = mood;

  form.post('/moods', {
    onSuccess: (page) => {
      const updatedMood = page.props.mood;

      // Update userMoods array
      const existingIndex = userMoods.value.findIndex(m => m.date === updatedMood.date);
      if (existingIndex !== -1) {
        userMoods.value[existingIndex] = updatedMood;
      } else {
        userMoods.value.push(updatedMood);
      }

      updateCalendarAttributes();
      closePicker();  // Close the mood picker dialog
      toast.success('Mood saved successfully!');
      errors.value = null;

    },
    onError: (errors) => {
      toast.error('Failed to save mood. Please try again.');
      errors.value = errors;
    },
    onFinish: () => {
      loading.value = false;
    }
  });
};


// Update calendar attributes based on user moods
const updateCalendarAttributes = () => {
  if (!Array.isArray(userMoods.value)) {
    console.error('userMoods.value is not an array:', userMoods.value);
    return;
  }

  calendarAttributes.value = userMoods.value.map((mood) => {
    return {
      key: mood.mood,
      dates: new Date(mood.date).toISOString().split('T')[0], // format to YYYY-MM-DD
      highlight: {
        color: moodColors[mood.mood] || 'grey', // Default to grey if mood not found
      },
      popover: {
        label: mood.mood, // popover label with mood text
      },
      customData: {
        mood: mood.mood,
      },
      class: `mood-${mood.mood}`, // additional class for styling
    };
  });
};

// Lifecycle hook: Fetch moods and update calendar on component mount
onMounted(async () => {
  try {
    await axios.get('/sanctum/csrf-cookie'); // Ensure CSRF cookie is set
    await fetchMoods();
    updateCalendarAttributes();
  } catch (error) {
    console.error('Error during initial setup:', error);
    toast.error('Failed to initialize application.');
  }
});

</script>


<style scoped>
.dashboard-container {
    display: flex;
    justify-content: center;
    gap: 50px;
    /* Space between the main content and right column */
    padding: 50px 20px;
    /* Add padding to the container */
}

.main-content {
    flex: 1;
    max-width: 700px;
    /* Adjust based on your desired width for the Card content */
}

.right-column {
    width: 400px;
    flex-shrink: 0;
    /* Prevent the right column from shrinking */
}

/* Media query to stack the right column on top of the main content on small screens */
@media (max-width: 1024px) {
    .dashboard-container {
        flex-direction: column;
        /* Stack the columns vertically */
        padding: 50px 20px;
        /* Ensure padding remains on smaller screens */
        gap: 20px;
        /* Adjust the gap between the stacked elements */
    }

    .main-content {
        max-width: 100%;
        /* Allow the main content to take full width */
        margin: 0 auto;
        /* Center the main content */
    }

    .right-column {
        width: 100%;
        /* Allow the right column to take full width */
        order: -1;
        /* Move the right column above the main content */
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

.modal {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999; /* Ensure it's above other elements */
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
}
.mood-options {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-bottom: 20px;
}
.mood-button {
  padding: 10px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
}
.mood-happy {
  background-color: #ffeb3b;
}
.mood-sad {
  background-color: #90caf9;
}
.mood-neutral {
  background-color: #e0e0e0;
}
.mood-excited {
  background-color: #ff9800;
}
.mood-angry {
  background-color: #f44336;
}
/* Add more mood-specific styles as needed */

/* Loading Spinner */
.loading-spinner {
  /* Style your loading spinner */
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  font-size: 1.5rem;
  color: #555;
}

/* Error Messages */
.error-messages {
  color: red;
  margin-top: 10px;
}
</style>