<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Performance Dashboard</h2>
    </template>
    <div class="dashboard-container py-12">
      <div class="main-content flex-1">
        <q-card class="welcome-card">
          <q-card-section>
            <template v-if="isTherapist">
              <h3 class="text-2xl font-bold mb-2">Welcome Back, {{ authUser.name }}</h3>
              <select v-model="clientId" @change="fetchTherapySessionData(clientId)"
                class="w-full p-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 appearance-none">
                <option disabled value="">Pick a client...</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>

            </template>
            <template v-else>
              <h3 class="text-2xl font-bold mb-2">Welcome Back, {{ authUser.name }}</h3>
            </template>
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
            <v-calendar v-model="selectedDate" :attributes="calendarAttributes" expanded @dayclick="openMoodPicker"
              class="mb-5"></v-calendar>

            <!-- Use q-dialog for the mood picker modal -->

            <q-dialog v-model="showPicker">
              <q-card class="mood-selection">
                <q-card-section>
                  <h4>Select your mood for {{ moment(selectedDate.value).format('YYYY-MM-DD') }}</h4>
                  <div class="mood-options mt-10">
                    <button v-for="mood in moods" :key="mood.value" @click="selectMood(mood.value)"
                      :class="[`mood-container`, `mood-${mood.value}`]">
                      <span class="emoji">{{ mood.label.split(' ')[0] }}</span>
                      <span class="label">{{ mood.label.split(' ')[1] }}</span>
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
      <h3 class="text-lg font-semibold mb-3">Monthly Mood Summary</h3>
      <label for="month-picker" class="block text-sm font-medium text-gray-700">Select Month</label>
      <div class="relative">
        <input
          type="month"
          id="month-picker"
          v-model="selectedMonth"
          @change="updateMoodSummaries"
          class="w-full p-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 mt-2 mb-4"
        />
        <span class="month-picker-arrow"></span>
      </div>
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
            <h3 class="text-lg font-semibold mb-3">Announcements</h3>

            <!-- If the user is a therapist, display their own announcements -->
            <template v-if="isTherapist">

              <!-- Announcement Form for Creating or Editing -->
              <form @submit.prevent="submitAnnouncement">
                <div class="mb-3">
                  <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                  <input type="text" id="title" v-model="announcementForm.title" required
                    class="w-full p-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>

                <div class="mb-3">
                  <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                  <textarea id="content" v-model="announcementForm.content" required rows="3"
                    class="w-full p-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <q-btn label="Submit" type="submit" />
              </form>
              <ul class="user-announcements-list">
                <li v-for="announcement in userAnnouncements" :key="announcement.id"
                  class="mb-4 mt-5 update-session-card">
                  <h5 class="text-md font-semibold">{{ announcement.title }}</h5>
                  <p class="text-sm">{{ announcement.content }}</p>

                  <!-- Edit and Delete Buttons -->
                  <button @click="editAnnouncement(announcement)" class="text-blue-500 hover:underline">Edit</button>
                  <button @click="deleteAnnouncement(announcement.id)"
                    class="text-red-500 hover:underline ml-3">Delete</button>
                </li>
              </ul>
            </template>
            <template v-else>
              <!-- Display All Announcements -->
              <ul class="all-announcements-list">
                <li v-for="announcement in allAnnouncements" :key="announcement.id" class="mb-4">
                  <h4 class="text-md font-semibold">{{ announcement.title }}</h4>
                  <p class="text-sm text-gray-500">By {{ announcement.therapist.name }} on {{
                    formatDate(announcement.created_at) }}</p>
                  <p class="text-sm">{{ announcement.content }}</p>
                </li>
              </ul>
            </template>
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
                <label class="text-md font-semibold">Sessions Done: </label>
                <input type="number" v-model="sessionsDone" min="0" />
              </div>
              <div class="mb-3">
                <label class="text-md font-semibold">Total Sessions: </label>
                <input type="number" v-model="totalSessions" min="1" />
              </div>
              <q-btn label="Update Sessions" @click="updateTherapySessions" />
            </q-card-section>
          </q-card>
        </div>
        <q-card class="assessment-card">
          <q-card-section>
            <h3 class="text-lg font-semibold mb-3">Assessment</h3>
            <!-- For Therapist -->
            <div v-if="isTherapist">
              <q-btn label="Add New Assessment" @click="openModal()" class="mb-3" color="primary" />
              <q-dialog v-model="showAssessmentModal">
                <q-card>
                  <q-card-section>
                    <h3>{{ selectedAssessment ? 'Edit' : 'Create' }} Assessment</h3>
                    <textarea v-model="assessmentComment" rows="4"
                      class="w-full p-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                      placeholder="Leave an assessment comment..."></textarea>
                  </q-card-section>
                  <q-card-actions align="right">
                    <q-btn label="Cancel" @click="closeModal" color="grey" />
                    <q-btn v-if="selectedAssessment" label="Update" @click="submitEditedAssessment" color="primary" />
                    <q-btn v-else label="Create" @click="submitAssessment" color="primary" />
                  </q-card-actions>
                </q-card>
              </q-dialog>
            </div>
            <div class="assessment-list" v-if="assessments.length > 0">
              <div  v-for="(assessment, index) in assessments" :key="index" class="assessment-item">
                <p class="text-lg">{{ assessment.comment }}</p>
                <p class="assessment-date">Therapist: {{ assessment.therapist_name }} | Date: {{ assessment.created_at }}</p>
  

                <!-- Therapist Edit/Delete Actions -->
                <div v-if="isTherapist">
                  <q-btn label="Edit" @click="editAssessment(assessment)" class="text-blue-500 hover:underline" />
                  <q-btn label="Delete" @click="deleteAssessment(assessment.id)"
                    class="text-red-500 hover:underline ml-3" />
                </div>
              </div>
            </div>
            <div v-else class="text-gray-500">
              <p>No assessment comments yet.</p>
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
const CLIENT_ID_KEY = 'selectedClientId';


// Initialize toast notifications
const users = ref([]);
const toast = useToast();
const assessmentComment = ref('');
const therapistName = ref('');
const announcementForm = ref({ title: '', content: '' });
const allAnnouncements = ref([]);
const userAnnouncements = ref([]);
const { auth } = usePage().props;
const authUser = computed(() => auth.user || null);
console.log("auth.user:", auth.user);
const { props } = usePage();

const showAssessmentModal = ref(false);


// Open the modal for creating or editing an assessment
const openModal = (assessment = null) => {
  selectedAssessment.value = assessment;
  assessmentComment.value = assessment ? assessment.comment : '';  // Populate the comment if editing
  showAssessmentModal.value = true;
};

// Close the modal
const closeModal = () => {
  showAssessmentModal.value = false;
  selectedAssessment.value = null;  // Reset the selected assessment
  assessmentComment.value = '';  // Reset the comment
};
// Initialize toast notifications
const roles = auth.user?.roles.map((role) => role.name) || [];

console.log("role of user:", roles[0]);

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
const selectedMonth = ref(moment().format('YYYY-MM'));
const errors = ref(null);
const loading = ref(false);
const sessionsDone = ref(0);
const totalSessions = ref(0);
const progressLevel = ref(0);
const moodColors = {
  happy: 'blue',
  sad: 'green',
  neutral: 'yellow',
  excited: 'red',
  angry: 'purple',
};
// Create a form instance
const form = useForm({
  date: '',
  mood: ''
});
const isTherapist = computed(() => {
  return roles.includes("therapist");
});

const clientId = ref(isTherapist.value ? localStorage.getItem(CLIENT_ID_KEY) || '' : authUser.value.id);
const assessments = ref([]);

const fetchAnnouncements = async () => {
  try {
    const response = await axios.get('/announcements');
    console.log("Announcements response:", response.data); // Add this to check the structure
    if (response.data) {
      allAnnouncements.value = response.data.allAnnouncements || [];
      userAnnouncements.value = response.data.userAnnouncements || [];
    }
  } catch (error) {
    console.error('Error fetching announcements:', error);
    toast.error('Failed to load announcements.');
  }
};

// Submit a new announcement (therapist only)
const submitAnnouncement = async () => {
  try {
    if (announcementForm.value.id) {
      // Update announcement
      await axios.put(`/announcements/${announcementForm.value.id}`, {
        title: announcementForm.value.title,
        content: announcementForm.value.content,
      });
      toast.success('Announcement updated successfully!');
    } else {
      // Create new announcement
      await axios.post('/announcements', {
        title: announcementForm.value.title,
        content: announcementForm.value.content,
      });
      toast.success('Announcement posted successfully!');
    }

    // Reset form and fetch announcements
    resetForm();
    fetchAnnouncements();
  } catch (error) {
    console.error('Error posting announcement:', error);
    toast.error('Failed to submit announcement.');
  }
};

// Edit an announcement
const editAnnouncement = (announcement) => {
  announcementForm.value.id = announcement.id;
  announcementForm.value.title = announcement.title;
  announcementForm.value.content = announcement.content;
};

// Delete an announcement
const deleteAnnouncement = async (id) => {
  try {
    await axios.delete(`/announcements/${id}`);
    toast.success('Announcement deleted successfully!');
    fetchAnnouncements();
  } catch (error) {
    console.error('Error deleting announcement:', error);
    toast.error('Failed to delete announcement.');
  }
};

// Reset the form
const resetForm = () => {
  announcementForm.value.id = null;
  announcementForm.value.title = '';
  announcementForm.value.content = '';
};

const fetchAssessmentComment = async () => {
  try {
    const response = await axios.get(`/assessment`, {
      params: { user_id: clientId.value || authUser.value.id },
    });

    // Check if assessments exist
    if (response.data.assessments && response.data.assessments.length > 0) {
      assessments.value = response.data.assessments;
    } else {
      assessments.value = []; // Clear assessments or handle empty state
      toast.info(response.data.message || 'No assessments available.');
    }
  } catch (error) {
    console.error('Error fetching assessments:', error);
    toast.error('Failed to load assessments.');
  }
};

const submitAssessment = async () => {
  try {
    await axios.post('/assessment', {
      client_id: clientId.value,
      comment: assessmentComment.value,
    });
    toast.success('Assessment created successfully');
    fetchAssessmentComment();  // Refresh the assessment list
    closeModal();  // Close the modal
  } catch (error) {
    toast.error('Failed to create assessment');
    console.error('Error creating assessment:', error);
  }
};
const fetchTherapySessionData = async (userId = null) => {
  try {
    const response = await axios.get(`/therapy-sessions`, {
      params: { user_id: userId || authUser.value.id }  // Default to logged-in user's ID
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

  if (!clientId.value) {
    toast.error('Please select a client.');
    loading.value = false;
    return;
  }

  try {
    // Pass recipientId as a query parameter
    const response = await axios.get(`/moods?clientId=${clientId.value}`);
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
watch(clientId, async (newValue) => {
  if (newValue) {
    localStorage.setItem(CLIENT_ID_KEY, newValue); // Save to localStorage
    await fetchMoods(); // Fetch moods for the selected recipient
    await fetchAssessmentComment();
  }
});

watch(userMoods, () => {
  updateMoodSummaries();
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

  // Check if the mood for the selected date already exists
  const existingMood = userMoods.value.find(m => m.date === form.date);

  if (existingMood) {
    // Update existing mood using PUT request
    try {
      await axios.post(`/moods/update`, {
        date: form.date,
        mood: form.mood,
      });

      // Update the mood in the userMoods array
      const existingIndex = userMoods.value.findIndex(m => m.date === existingMood.date);
      if (existingIndex !== -1) {
        userMoods.value[existingIndex] = { ...existingMood, mood: form.mood };
      }

      updateCalendarAttributes();
      closePicker();  // Close the mood picker dialog
      toast.success('Mood updated successfully!');
      errors.value = null;
    } catch (error) {
      toast.error('Failed to update mood. Please try again.');
    } finally {
      loading.value = false;
    }

  } else {
    // Create new mood using POST request
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
      },
      onFinish: () => {
        loading.value = false;
      }
    });
  }
};


// Update calendar attributes based on user moods
const updateCalendarAttributes = () => {
  if (!Array.isArray(userMoods.value)) {
    console.error('userMoods.value is not an array:', userMoods.value);
    return;
  }

  // Create a new array for calendar attributes based on userMoods
  const newAttributes = userMoods.value.map((mood) => {
    return {
      key: mood.mood,
      dates: moment(mood.date).format('YYYY-MM-DD'), // format to YYYY-MM-DD
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

  // Assign the new array to calendarAttributes (this ensures Vue's reactivity)
  calendarAttributes.value = newAttributes;
};

console.log("roles:", roles);

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
  const selectedMoment = moment(selectedMonth.value, 'YYYY-MM');
  const monthlyMoods = userMoods.value.filter((mood) =>
    moment(mood.date).isSame(selectedMoment, 'month')
  );

  // Count moods for the selected month
  const moodCounts = { happy: 0, sad: 0, neutral: 0, excited: 0, angry: 0 };
  monthlyMoods.forEach((mood) => {
    if (moodCounts[mood.mood] !== undefined) {
      moodCounts[mood.mood]++;
    }
  });

  // Update the pie chart data
  monthlyMoodSummary.value = Object.values(moodCounts);
};


// Helper function to summarize moods
const summarizeMoods = (data) => {
  const moodCounts = { happy: 0, sad: 0, neutral: 0, excited: 0, angry: 0 };

  data.forEach(mood => {
    if (moodCounts[mood.mood] !== undefined) {
      moodCounts[mood.mood] += 1;
    }
  });

  return Object.values(moodCounts); // Returns an array like [happyCount, sadCount, neutralCount, excitedCount, angryCount]
};

// Lifecycle hook: Fetch moods and update calendar on component mount

const selectedAssessment = ref(null);

const editAssessment = (assessment) => {
  openModal(assessment);  // Use the openModal function to open the modal and populate it with assessment data
};

const submitEditedAssessment = async () => {
  if (selectedAssessment.value) {
    try {
      await axios.put(`/assessment/${selectedAssessment.value.id}`, {
        comment: assessmentComment.value,
      });
      toast.success('Assessment updated successfully');
      fetchAssessmentComment();  // Refresh the assessment list
      closeModal();  // Close the modal
    } catch (error) {
      toast.error('Failed to update assessment');
      console.error('Error updating assessment:', error);
    }
  }
};

const deleteAssessment = async (id) => {
  try {
    await axios.delete(`/assessment/${id}`);
    toast.success('Assessment deleted successfully');
    fetchAssessmentComment();  // Refresh the assessment list
  } catch (error) {
    toast.error('Failed to delete assessment');
    console.error('Error deleting assessment:', error);
  }
};


// Fetch all users for the therapist to select
const fetchUserList = async () => {
  try {
    const response = await axios.get('/users2'); // Calls the updated API
    users.value = response.data; // Store the users
  } catch (error) {
    console.error('Error fetching user list:', error);
    toast.error('Failed to fetch user list.');
  }
};

// Update session progress for the selected user (therapist only)
const updateTherapySessions = async () => {
  const sessionsDoneInt = parseInt(sessionsDone.value, 10);
  const totalSessionsInt = parseInt(totalSessions.value, 10);

  // Ensure total sessions and sessions done are valid integers
  if (isNaN(sessionsDoneInt) || isNaN(totalSessionsInt)) {
    toast.error('Both "Sessions Done" and "Total Sessions" must be valid numbers.');
    return;
  }

  // Handle the special case of 0/0
  if (sessionsDoneInt === 0 && totalSessionsInt === 0) {
    toast.error('Both "Sessions Done" and "Total Sessions" cannot be 0.');
    return;
  }

  // Validate that sessions_done does not exceed total_sessions
  if (sessionsDoneInt > totalSessionsInt) {
    toast.error('"Sessions Done" cannot exceed "Total Sessions."');
    return;
  }

  try {
    const response = await axios.post(`/therapy-sessions/${clientId.value}`, {
      sessions_done: sessionsDoneInt,
      total_sessions: totalSessionsInt,
    });

    if (response.status === 200) {
      toast.success('Therapy session updated successfully.');
    } else {
      toast.error('Failed to update therapy session. Please try again.');
    }
  } catch (error) {
    console.error('Error updating therapy session:', error);

    // Handle 403 unauthorized errors explicitly
    if (error.response && error.response.status === 403) {
      toast.error('You are not authorized to update this session.');
    } else {
      toast.error('Failed to update therapy session. Please try again later.');
    }
  }
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString();
};

onMounted(async () => {
  try {
    await axios.get('/sanctum/csrf-cookie'); // Ensure CSRF cookie is set

    if (isTherapist.value) {
      // Fetch list of users if the logged-in user is a therapist
      await fetchUserList();
      await fetchAnnouncements();
      await fetchAssessmentComment();
      await fetchTherapySessionData(clientId.value || authUser.value.id); // Ensure the session data is also fetched

    } else {
      // Fetch therapy session data for the current logged-in user
      fetchMoods();
      updateCalendarAttributes();
      await fetchTherapySessionData(authUser.value.id);
      await fetchAssessmentComment(authUser.value.id);
      await fetchAnnouncements();
      await fetchTherapySessionData(clientId.value || authUser.value.id); // Ensure the session data is also fetched
    }

    fetchMoods(); // Fetch moods
    updateCalendarAttributes(); // Update calendar attributes for moods
  } catch (error) {
    console.error('Error during initial setup:', error);
    toast.error('Failed to initialize application.');
  }
});

console.log('Weekly Mood Summary:', weeklyMoodSummary.value);
console.log('Monthly Mood Summary:', monthlyMoodSummary.value);
</script>
<style scoped>
.mood-selection {
  max-width: 1000px;
  margin: 0 auto;
  border: 1px solid #ddd;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  background-color: #ffffff;
}

.update-session-card {
  margin-bottom: 20px;
  border: 1px solid #ddd;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 15px;
  background-color: #ffffff;

}

.dashboard-container {
  display: flex;
  justify-content: center;
  gap: 50px;
  /* Space between the main content and right column */
  padding: 50px 20px;
  /* Add padding to the container */
}

/* Improve assessment spacing */
.assessment-list {
  max-height: 200px;
  overflow-y: auto;
  padding-right: 10px;
  margin-bottom: 15px; /* Add margin at the bottom */
}

.assessment-list .assessment-item {
  padding: 10px;
  margin-bottom: 15px;
  background-color: #f9f9f9; /* Lighter background for better contrast */
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.assessment-item p {
  white-space: pre-line; /* This will ensure that line breaks and spaces are respected */
  margin-bottom: 10px;
  line-height: 1.6;
}

.assessment-item .assessment-date {
  font-size: 12px;
  color: #6b7280; /* Light gray for the date text */
}

/* Change input color to white */
input[type="number"] {
  background-color: white; /* Change the background to white */
  color: black; /* Ensure the text is still visible */
  border: 1px solid #ddd; /* Light gray border */
  border-radius: 6px;
  padding: 10px;
  margin-top: 5px; /* Add some space between the input and label */
}

input[type="number"]:focus {
  outline: none;
  border-color: #3182ce; /* Blue border on focus */
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5); /* Focus shadow effect */
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

.announcement-card {
  margin-bottom: 20px;
  border: 1px solid #ddd;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 15px;
  background-color: #ffffff;
  max-height: 400px;
  overflow-y: auto;
}

/* Card styles */
.q-btn {
  margin-right: 10px;
}

.q-btn.text-blue-500 {
  color: #3490dc;
}

.q-btn.text-red-500 {
  color: #e3342f;
}

.welcome-card,
.feelings-tracker-card,
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
  margin-top: 10px;
}

.mood-happy {
  background-color: #d6f6ff;
  /* Light blue */
}

.mood-sad {
  background-color: #b3f5bc;
  /* Light green */
}

.mood-neutral {
  background-color: #f9ffb5;
}

.mood-excited {
  background-color: #fa9189;
}

.mood-angry {
  background-color: #e2cbf7;

}

.mood-happy .label,
.mood-neutral .label,
.mood-excited .label,
.mood-sad .label,
.mood-angry .label {
  color: black;
  text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.5);
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

@keyframes wave {
  0% {
    transform: rotate(0deg);
  }

  25% {
    transform: rotate(25deg);
  }

  0% {
    transform: rotate(0deg);
  }

  75% {
    transform: rotate(-25deg);
  }

  100% {
    transform: rotate(0deg);
  }
}

.wave .emoji {
  font-size: 28px;
  display: inline-block;
  animation: wave 0.3s ease-in-out 4;
  animation-direction: alternate;
}

.mood-options {
  display: flex;
  justify-content: space-around;
  flex-wrap: wrap;
}

.mood-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  border: none;
  cursor: pointer;
  transition: transform 0.3s ease;
  margin: 0 auto;
  border: 1px solid #ddd;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  padding: 10px 20px;
  font-size: 1rem;

}

.mood-container:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
}


.emoji {
  font-size: 50px;
  /* Enlarge the emoji */
  margin-bottom: 5px;

}

.emoji:hover {
  font-size: 50px;
  /* Enlarge the emoji */
  margin-bottom: 5px;
  animation: wave 0.5s
}

.label {
  font-size: 14px;
  /* Smaller label */
  color: white;
}


.month-picker-arrow {
  position: absolute;
  top: 50%;
  right: 13px;
  width: 0;
  height: 0;
  border-left: 5px solid transparent;
  border-right: 5px solid transparent;
  border-top: 5px solid #a0aec0; /* Gray color */
  pointer-events: none;
  transform: translateY(-50%);
}

</style>
