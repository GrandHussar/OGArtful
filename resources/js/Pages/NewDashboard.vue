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
            <select v-model="clientId"   @change="fetchTherapySessionData(selectedUser)"
              class="w-full p-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
              <option disabled value="">Pick a client...</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </q-card-section>
        </q-card>

        <q-card class="feelings-tracker-card">
          <q-card-section>
            <template v-if="isTherapist">
            <h3 class="text-lg font-semibold mb-3">Feelings Tracker for </h3>
            <p class="text-sm">The client's calendar</p>
            </template>  
            <template v-else>
            <h3 class="text-lg font-semibold mb-3">Feelings Tracker</h3>
            <p class="text-sm">How are you feeling today?</p>
            </template>
            <v-calendar v-model="selectedDate" :attributes="calendarAttributes" expanded
              @dayclick="openMoodPicker"></v-calendar>

            <!-- Use q-dialog for the mood picker modal -->
       
            <q-dialog v-model="showPicker">
              <q-card>
                <q-card-section>
                  <h4>Select Your Mood for {{ moment(selectedDate.value).format('YYYY-MM-DD') }}</h4>
                  <div class="mood-options">
                    <button v-for="mood in moods" :key="mood.value" @click="selectMood(mood.value)"
                      :class="['mood-button', `mood-${mood.value}`]">
                      {{ mood.label }}
                    </button>
                  </div>
                </q-card-section>
                <q-card-actions>
                  <q-btn label="Close" @click="closePicker" />
                </q-card-actions>
              </q-card>
            </q-dialog>
            <div v-if="isTherapist" class="mood-summary-section">
              <q-card class="mood-summary-card">
                <q-card-section>
                  <h3 class="text-lg font-semibold mb-3">Weekly Mood Summary</h3>
                  <ApexCharts type="pie" :options="chartOptions" :series="weeklyMoodSummary" />
                </q-card-section>
              </q-card>

              <q-card class="mood-summary-card">
                <q-card-section>
                  <h3 class="text-lg font-semibold mb-3">Monthly Mood Summary</h3>
                  <ApexCharts type="pie" :options="chartOptions" :series="monthlyMoodSummary" />
                </q-card-section>
              </q-card>
            </div>
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
            <p v-if="sessionsDone === 0" class="text-center text-gray-500">No sessions yet</p>
            <p v-else class="text-4xl font-bold text-center">{{ sessionsDone }} / {{ totalSessions }}</p>
        </div>
        <div class="progress-info">
            <p class="text-lg font-semibold">Progress Level</p>
            <!-- Progress bar -->
            <q-linear-progress :value="progressLevel / 100" color="green" size="md" />
        </div>
    </q-card-section>
</q-card>

<div v-if="isTherapist">
    <q-card class="update-session-card">
        <q-card-section>
            <h3 class="text-lg font-semibold mb-3">Update Sessions</h3>
            <div class="mb-3">
                <label class="text-md font-semibold">Sessions Done</label>
                <input type="number" v-model="sessionsDone" min="0" />
            </div>
            <div class="mb-3">
                <label class="text-md font-semibold">Total Sessions</label>
                <input type="number" v-model="totalSessions" min="1" />
            </div>
            <q-btn label="Update Sessions" @click="updateTherapySessions" />
        </q-card-section>
    </q-card>
</div>
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

import { ref, onMounted, watch, computed } from 'vue';
import { useForm } from '@inertiajs/inertia-vue3';
import { useToast } from 'vue-toastification';
import { Link, usePage, router } from "@inertiajs/vue3";
import moment from 'moment';
import ApexCharts from 'vue3-apexcharts';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
const RECIPIENT_ID_KEY = 'selectedRecipientId';

const recipientId = ref(localStorage.getItem(RECIPIENT_ID_KEY) || '');
// Initialize toast notifications
const users = ref([]);
const toast = useToast();
const { auth } = usePage().props;
const authUser = computed(() => auth.user || null);
console.log("auth.user:", auth.user);
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
const sessionsDone = ref(0);
const totalSessions = ref(0);
const progressLevel = ref(0);
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
const fetchTherapySessionData = async (userId = null) => {
    try {
        const response = await axios.get(`/therapy-sessions`, {
            params: { user_id: userId }
        });
        const therapySession = response.data;

        if (therapySession.message === 'No therapy session found') {
            sessionsDone.value = 0;
            totalSessions.value = 0;
            progressLevel.value = 0;
        } else {
            sessionsDone.value = therapySession.sessions_done;
            totalSessions.value = therapySession.total_sessions;
            progressLevel.value = ((sessionsDone.value / totalSessions.value) * 100).toFixed(2);
        }
    } catch (error) {
        console.error('Error fetching therapy session data:', error);
        toast.error('Failed to fetch therapy session data.');
    }
};
// Fetch moods from the backend
const fetchMoods = async () => {
  loading.value = true;
  
  if (!recipientId.value) {
    toast.error('Please select a recipient.');
    loading.value = false;
    return;
  }
  
  try {
    // Pass recipientId as a query parameter
    const response = await axios.get(`/moods?recipientId=${recipientId.value}`);
    console.log('Fetched Moods:', response.data);
    
    if (Array.isArray(response.data)) {
      userMoods.value = response.data;
updateCalendarAttributes();
      updateMoodSummaries();
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
watch(recipientId, async (newValue) => {
  if (newValue) {
    localStorage.setItem(RECIPIENT_ID_KEY, newValue); // Save to localStorage
    await fetchMoods(); // Fetch moods for the selected recipient
  }
});
const fetchUsers = async () => {
  try {
    const response = await axios.get('/users2');
    users.value = response.data;
  } catch (error) {
    console.error('Error fetching users:', error);
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
const roles = auth.user?.roles.map((role) => role.name) || [];
console.log("roles:", roles);
const isTherapist = computed(() => {
  return roles.includes("therapist");
});

// Add data for mood summaries
const weeklyMoodSummary = ref([]);
const monthlyMoodSummary = ref([]);

// Chart options for week and month
const chartOptions = ref({
  chart: {
    type: 'pie',
    height: 350,
  },
  labels: ['Happy', 'Sad', 'Neutral', 'Excited', 'Angry'],
  responsive: [{
    breakpoint: 480,
    options: {
      chart: {
        width: 300
      },
      legend: {
        position: 'bottom'
      }
    }
  }]
});


// Update mood summaries for weekly and monthly data
const updateMoodSummaries = () => {
  const currentWeek = moment().week();
  const currentMonth = moment().month();

  // Group by week and month
  const weeklyData = userMoods.value.filter(mood => moment(mood.date).week() === currentWeek);
  const monthlyData = userMoods.value.filter(mood => moment(mood.date).month() === currentMonth);

  weeklyMoodSummary.value = summarizeMoods(weeklyData);
  monthlyMoodSummary.value = summarizeMoods(monthlyData);
};

// Helper function to summarize moods
const summarizeMoods = (data) => {
  const moodCounts = { happy: 0, sad: 0, neutral: 0, excited: 0, angry: 0 };

  data.forEach(mood => {
    if (moodCounts[mood.mood]) {
      moodCounts[mood.mood] += 1;
    }
  });

  return Object.values(moodCounts);
};
// Lifecycle hook: Fetch moods and update calendar on component mount
onMounted(async () => {
  try {
    await axios.get('/sanctum/csrf-cookie'); // Ensure CSRF cookie is set
    await fetchMoods();
    await fetchUsers();
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
  z-index: 999;
  /* Ensure it's above other elements */
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