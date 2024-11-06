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
              <h3 class="text-lg font-semibold mb-3">Feelings Tracker</h3>
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
                    <input type="month" id="month-picker" v-model="selectedMonth" @change="updateMoodSummaries"
                      class="w-full p-3 border border-gray-300 rounded-lg text-gray-700 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 mt-2 mb-4" />
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
                  <q-btn @click="deleteAnnouncement(announcement.id)"
                    class="text-red-500 hover:underline ml-3">Delete</q-btn>
                </li>
              </ul>
            </template>
            <template v-else>
              <!-- Display All Announcements -->
              <ul class="all-announcements-list">
                <li v-for="announcement in allAnnouncements" :key="announcement.id" class="mb-4">
                  <h4 class="text-md font-semibold">{{ announcement.title }}</h4>
                  <p class="text-sm text-gray-500">By {{ announcement.therapist.name }} on {{
                    formatDate(announcement.created_at) }} {{ date.time }}</p>
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


        <q-card class="assessment-card">
          <q-card-section>
            <h3 class="text-lg font-semibold mb-3">Assessment</h3>
            <!-- For Therapist -->
            <div v-if="isTherapist">
              <q-btn rounded label="Add New Assessment" @click="openModal()" class="mb-3" color="positive" />
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
              <div v-for="(assessment, index) in assessments" :key="index" class="assessment-item">
                <p class="text-lg">{{ assessment.comment }}</p>
                <p class="assessment-date">Therapist: {{ assessment.therapist_name }} | Date: {{ assessment.created_at
                  }}
                </p>
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

        <q-card class="assessment-card">
          <q-card-section>
            <div v-if="isTherapist">
              <h3 class="text-lg font-semibold mb-3">Available Dates for Appointments</h3>
              <q-input v-model="newAvailableDate" label="Select Date" type="date" />
              <q-input v-model="newAvailableTime" label="Select Time" type="time" />
              <div class="mt-3 flex justify-end">
                <q-btn color="positive" rounded label="Save Date" @click="saveAvailableDate" />
              </div>
              <div v-if="availableDates.length" class="mt-4">
                <p class="text-lg font-semibold mb-3">Available Dates:</p>
                <ul>
                  <li v-for="date in availableDates" :key="date.id">
                    {{ formatDate(date) }} at {{ date.time }}

                    <q-btn label="Delete" @click="deleteDate(date.id)" class="text-red-500 hover:underline ml-6" />
                  </li>
                </ul>
              </div>
              <br />
              <h3 class="text-lg font-semibold mb-3">Appointments with clients:</h3>
              <template v-if="appointments.length >= 1">
                <li v-for="appointment in appointments" :key="appointment.id" @click="openAppointmentModal(appointment)"
                  class="selectable-appointment mb-3">
                  <p>Appointment with {{ appointment.user.name }} on {{ appointment.appointment_date }} at
                    {{ convertTo12HourFormat(appointment.appointment_time) }}
                  </p>
                </li>
              </template>
              <template v-else>
                <p>No appointments available.</p>
              </template>
              <!-- Appointment Management Modal -->
              <q-dialog v-model="showAppointmentModal">
                <q-card class="appointment-modal">
                  <q-card-section>
                    <h4 class="mb-4 border-bottom">Manage Appointment</h4>
                    <p>Client: {{ selectedAppointment?.user?.name }}</p>
                    <p>Date: {{ selectedAppointment?.appointment_date }}</p>
                    <p>Time: {{ selectedAppointment?.appointment_time }}</p>

                    <!-- Link Input Field -->
                    <div class="link-input mt-4">
                      <label>Conference Link:</label>
                      <q-input v-model="appointmentLink" placeholder="Enter conference link" type="url"
                        class="w-full mt-2" />
                    </div>
                    <q-btn rounded label="Update Link" @click="updateAppointmentLink" color="primary" class="mt-2"
                      padding="xs md" />

                    <div class="status-selection mt-4">
                      <label>Status:</label>
                      <select v-model="selectedStatus" @change="handleStatusChange"
                        class="w-full p-2 mt-2 border border-gray-300 rounded-lg">
                        <option value="" disabled>Select Status</option>
                        <option value="completed">Complete</option>
                        <option value="cancelled">Canceled</option>
                      </select>
                    </div>

                    <!-- Conditionally Show Button for Feedback Form When Status is "completed" -->
                    <q-btn rounded v-if="selectedStatus === 'completed'" label="Open Feedback Form" color="primary"
                      @click="showFeedbackForm = true" class="mt-5" padding="xs md" />

                    <!-- Pop-Out Feedback Form Dialog -->
                    <!-- Feedback Form Modal -->
                    <q-dialog v-model="showFeedbackForm" persistent>
                      <q-card class="feedback-form-card">
                        <q-card-section>
                          <h2 class="form-title">Art Therapist Feedback Form: Digital Art Therapy Session</h2>

                          <!-- Therapist and Patient Information -->
                          <p>Therapist Name: <q-input v-model="feedbackForm.therapist_name" /></p>
                          <p>Patient Name: <q-input v-model="feedbackForm.patient_name" /></p>
                          <p>Date of Session: <q-input v-model="feedbackForm.session_date" type="date" /></p>

                          <!-- Session Overview -->
                          <h3 class="text-lg font-semibold mb-3">1. Session Overview</h3>
                          <q-input v-model="feedbackForm.duration" label="Duration of Session (minutes)"
                            type="number" />
                          <q-option-group v-model="feedbackForm.activity_type" :options="[
                            { label: 'Drawing', value: 'drawing' },
                            { label: 'Mindful Coloring', value: 'painting' },
                            { label: 'Collage', value: 'collage' },
                            { label: 'Animation', value: 'animation' },
                            { label: 'Other', value: 'other' }
                          ]" type="radio" />

                          <!-- Patient’s Engagement Level -->
                          <h3 class="text-lg font-semibold mb-3">2. Patient’s Engagement Level</h3>
                          <q-option-group v-model="feedbackForm.engagement_level" :options="[
                            { label: 'Highly Engaged', value: 'highly engaged' },
                            { label: 'Moderately Engaged', value: 'moderately engaged' },
                            { label: 'Somewhat Engaged', value: 'somewhat engaged' },
                            { label: 'Not Engaged', value: 'not engaged' }
                          ]" type="radio" />

                          <!-- Observed Emotions During the Session -->
                          <h3 class="text-lg font-semibold mb-3">3. Observed Emotions During the Session</h3>
                          <q-option-group v-model="feedbackForm.observed_emotions" :options="[
                            { label: 'Joy', value: 'joy' },
                            { label: 'Sadness', value: 'sadness' },
                            { label: 'Frustration', value: 'frustration' },
                            { label: 'Anxiety', value: 'anxiety' },
                            { label: 'Calmness', value: 'calmness' },
                            { label: 'Anger', value: 'anger' }
                          ]" type="checkbox" />
                          <q-input v-if="feedbackForm.observed_emotions.includes('other')"
                            v-model="feedbackForm.other_emotion" label="Other Emotion" />

                          <!-- Patient’s Artistic Expression -->
                          <h3 class="text-lg font-semibold mb-3">4. Patient’s Artistic Expression</h3>
                          <q-option-group v-model="feedbackForm.artistic_quality" :options="[
                            { label: 'Excellent', value: 'excellent' },
                            { label: 'Good', value: 'good' },
                            { label: 'Fair', value: 'fair' },
                            { label: 'Poor', value: 'poor' }
                          ]" type="radio" />
                          <h3 class="text-lg font-semibold mb-3">5. Patient’s Artistic Theme</h3>
                          <q-option-group v-model="feedbackForm.artwork_theme" :options="[
                            { label: 'Positive', value: 'positive' },
                            { label: 'Negative', value: 'negative' },
                            { label: 'Neutral', value: 'neutral' },
                            { label: 'Other', value: 'other' }
                          ]" type="radio" />
                          <q-input v-if="feedbackForm.artwork_theme === 'other'" v-model="feedbackForm.other_theme"
                            label="Other Theme" />

                          <!-- Insights Gained from the Session -->
                          <h3 class="text-lg font-semibold mb-3">6. Insights Gained from the Session</h3>
                          <q-option-group v-model="feedbackForm.shared_significant_thoughts" :options="[
                            { label: 'Yes', value: true },
                            { label: 'No', value: false }
                          ]" type="radio" />
                          <q-input v-if="feedbackForm.shared_significant_thoughts"
                            v-model="feedbackForm.thoughts_detail" label="If yes, please elaborate" />

                          <!-- Therapeutic Techniques Used -->
                          <h3 class="text-lg font-semibold mb-3">7. Therapeutic Techniques Used</h3>
                          <q-option-group v-model="feedbackForm.therapeutic_techniques" :options="[
                            { label: 'Guided Imagery', value: 'guided_imagery' },
                            { label: 'Cognitive Behavioral Techniques', value: 'cbt' },
                            { label: 'Emotion Regulation Skills', value: 'emotion_regulation' },
                            { label: 'Other', value: 'other' }
                          ]" type="checkbox" />
                          <q-input v-if="feedbackForm.therapeutic_techniques.includes('other')"
                            v-model="feedbackForm.other_technique" label="Other Technique" />

                          <!-- Overall Assessment of Patient’s Mental State -->
                          <h3 class="text-lg font-semibold mb-3">8. Overall Assessment of Patient’s Mental State</h3>
                          <q-option-group v-model="feedbackForm.mental_state" :options="[
                            { label: 'Improved', value: 'improved' },
                            { label: 'Stable', value: 'stable' },
                            { label: 'Deteriorated', value: 'deteriorated' }
                          ]" type="radio" />

                          <!-- Recommendations for Future Sessions -->
                          <h3 class="text-lg font-semibold mb-3">9. Recommendations for Future Sessions</h3>
                          <q-input v-model="feedbackForm.recommendations" type="textarea" rows="3" />

                          <!-- Additional Notes or Observations -->
                          <h3 class="text-lg font-semibold mb-3">10. Additional Notes or Observations</h3>
                          <q-input v-model="feedbackForm.additional_notes" type="textarea" rows="3" />

                          <!-- Therapist Signature -->
                          <p class="mt-5">Therapist Signature: <q-input v-model="feedbackForm.therapist_signature" />
                          </p>
                        </q-card-section>

                        <!-- Form Actions -->
                        <q-card-actions align="right">
                          <q-btn label="Cancel" color="grey" @click="closeFeedbackForm" />
                          <q-btn label="Submit" color="primary" @click="submitFeedbackForm" />
                        </q-card-actions>
                      </q-card>
                    </q-dialog>


                  </q-card-section>

                  <q-card-actions align="right">
                    <q-btn rounded label="Close" @click="closeAppointmentModal" color="grey" class="mb-3"
                      padding="xs md" />
                    <q-btn rounded label="Update Status" @click="updateAppointmentStatus" color="positive" class="mb-3"
                      padding="xs md" />
                  </q-card-actions>
                </q-card>
              </q-dialog>
            </div>
            <div v-else>
              <h3 class="text-lg font-semibold mb-3">Available Appointment Dates</h3>
              <div v-if="availableDates.length">
                <ul>
                  <li v-for="date in availableDates" :key="date.id">
                    {{ formatDate(date) }} at {{ date.time }}
                    <q-btn label="Pick Date" @click="requestAppointment(date)" />
                  </li>
                </ul>
              </div>
              <div v-else>
                <p>No available dates at the moment.</p>
              </div>
            </div>

            <div v-if="isUser">
              <h2 class="mt-10 text-lg font-semibold mb-3">All Appointments</h2>
              <ul v-if="appointments.length">
                <li v-for="appointment in appointments" :key="appointment.id" class="appointment-item">
                  <!-- Display Time -->
                  <p><strong>Time:</strong> {{ convertTo12HourFormat(appointment.appointment_time) }}</p>

                  <!-- Toggle Details Button with Arrow -->
                  <button @click="toggleDetails(appointment.id)" class="details-toggle">
                    {{ showDetails[appointment.id] ? '▲' : '▼' }} <!-- Arrow for toggle -->
                    {{ showDetails[appointment.id] ? 'Hide Details' : 'Show Details' }}
                  </button>

                  <!-- Conditional Details Section -->
                  <div v-show="showDetails[appointment.id]" class="appointment-details">
                    <p><strong>Link:</strong>
                      <a :href="appointment.link" target="_blank" v-if="appointment.link">{{ appointment.link }}</a>
                      <span v-else>No link available</span>
                    </p>
                    <p><strong>Status:</strong> {{ appointment.status }}</p>
                  </div>
                </li>
              </ul>
              <p v-else>No appointments available.</p>
              <h1 class="mt-10 text-lg font-semibold mb-3">Select Completed Session</h1>

              <div>


                <div v-if="completedSessions.length > 0">
                  <div class="mb-1">
                  <label for="therapistSelect">Select Therapist:</label>
                  <select id="therapistSelect" v-model="selectedTherapistId" @change="onTherapistChange">
                    <option v-for="therapist in therapists" :key="therapist.id" :value="therapist.id">
                      {{ therapist.name }}
                    </option>
                  </select>
                  </div>
                  <br />
                  <label for="completedSession">Select Date:</label>
                  <select v-model="selectedSessionId" @change="fetchSessionReportAndMentalState">
                    <option v-for="session in completedSessions" :key="session.id" :value="session.id">
                      {{ session.appointment_date }} - {{ session.appointment_time }}
                    </option>
                  </select>
                </div>
                <div v-else>
                  <p>No completed sessions available.</p>
                </div>

                <div v-if="sessionReport">
                  <h2 class="mt-10 text-lg font-semibold mb-3">Session Report</h2>
                  <p><strong>Duration:</strong> {{ sessionReport.duration }} minutes</p>
                  <p><strong>Activity Type:</strong> {{ sessionReport.activity_type }}</p>
                  <p><strong>Engagement Level:</strong> {{ sessionReport.engagement_level }}</p>
                  <p><strong>Observed Emotions:</strong> {{ sessionReport.observed_emotions?.join(', ') || 'No emotions recorded' }}</p>
                  <!-- Additional fields as necessary -->
                </div>

                <h2 class="mt-10 text-lg font-semibold mb-3">Mental State Distribution</h2>
                <ApexCharts type="pie" :options="chartOptions1" :series="chartSeries" />

              </div>
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
import { date } from 'quasar';
import { defineProps } from 'vue';
const therapists = ref([]);
const selectedTherapistId = ref(null);
const emit = defineEmits(['therapist-selected']);
const fetchTherapists = async () => {
  try {
    const response = await axios.get('/users2'); // Adjust the API endpoint as necessary
    therapists.value = response.data;
  } catch (error) {
    console.error('Error fetching therapists:', error);
  }
};
const onTherapistChange = () => {
  emit('therapist-selected', selectedTherapistId.value);
};
const CLIENT_ID_KEY = 'selectedClientId';
const appointmentLink = ref(''); // Link for the appointment
const completedSessions = ref([]);
const selectedSessionId = ref(null);
const mentalStateCounts = ref({ improved: 0, stable: 0, deteriorated: 0 });
const chartOptions1 = {
  chart: {
    type: 'pie',
    height: '350'
  },
  labels: ['Improved', 'Stable', 'Deteriorated'], // Ensure labels match counts
  responsive: [{
    breakpoint: 480,
    options: {
      chart: {
        width: 200
      },
      legend: {
        position: 'bottom'
      }
    }
  }]
};
const chartSeries = ref([0, 0, 0]);
const appointments = ref([]);
const availableDates = ref([]);
const availableDatesForClient = ref([]);
const newAvailableDate = ref('');
const newAvailableTime = ref('');
const selectedDateId = ref(null);
const showAppointmentModal = ref(false);
const selectedAppointment = ref(null);
const showFeedbackForm = ref(false);
const selectedStatus = ref('');
const feedbackForm = ref({
  therapist_name: '',
  patient_name: '',
  session_date: '',
  duration: null,
  activity_type: '', // Valid options: 'drawing', 'painting', 'animation', 'collage', 'other'
  other_activity: '',
  engagement_level: '', // Valid options: 'not engaged', 'somewhat engaged', 'moderately engaged', 'highly engaged'
  observed_emotions: [],
  artistic_quality: '', // Valid options: 'excellent', 'good', 'fair', 'poor'
  artwork_theme: '', // Valid options: 'positive', 'negative', 'neutral', 'other'
  other_theme: '',
  shared_significant_thoughts: false, // Set as boolean
  thoughts_detail: '',
  therapeutic_techniques: [], // Stored as JSON array
  mental_state: '', // Valid options: 'improved', 'stable', 'deteriorated'
  recommendations: '',
  additional_notes: '',
  therapist_signature: ''
});
const closeFeedbackForm = () => {
  showFeedbackForm.value = false;
};
const submitFeedbackForm = async () => {
  // Debugging: Log the selected appointment and feedback form data before proceeding
  console.log('Selected Appointment:', selectedAppointment.value);
  console.log('Feedback Form Data before submission:', feedbackForm.value);
  // Check if appointment ID and feedbackForm data are valid
  if (!selectedAppointment.value || !selectedAppointment.value.id) {
    toast.error('No appointment selected or appointment ID is missing.');
    return;
  }
  // Ensure observedEmotions is an array in case it's undefined
  const formData = {
    ...feedbackForm.value,
    observedEmotions: feedbackForm.value.observedEmotions || [],  // Default to empty array if undefined
  };
  // Debugging: Log the final payload data being sent to the API
  console.log('Final Payload for API Submission:', formData);
  try {
    const response = await axios.post(
      `/appointments/${selectedAppointment.value.id}/session-report`,
      formData,
      {
        headers: { 'Content-Type': 'application/json' }
      }
    );
    // Debugging: Log the response from the API if successful
    console.log('API Response:', response.data);

    toast.success('Feedback form submitted successfully!');
    closeFeedbackForm();
  } catch (error) {
    // Debugging: Log error details if the API call fails
    console.error('Error submitting feedback form:', error);
    if (error.response) {
      console.error('Error Response Data:', error.response.data);
      console.error('Error Response Status:', error.response.status);
      console.error('Error Response Headers:', error.response.headers);
    }
    toast.error('Failed to submit feedback form.');
  }
};
const sessionReport = ref({
  duration: null,
  activity_type: '',
  other_activity: '',
  engagement_level: ''
});
const openAppointmentModal = (appointment) => {
  selectedAppointment.value = appointment;
  showAppointmentModal.value = true;
  selectedStatus.value = ''; // Reset status
  appointmentLink.value = appointment.link || ''; // Load existing link if any
};
const fetchPendingAppointments = async () => {
  try {
    const response = await axios.get('/users/all-appointments');
    // Assign all appointments to `appointments` without filtering
    appointments.value = response.data.appointments; // Note: Using "appointments" from the response

  } catch (error) {
    console.error('Failed to fetch appointments:', error);
  }
};

const handleStatusChange = () => {
  if (selectedStatus.value !== 'completed') {
    // Hide the feedback form button if the status is not "completed"
    showFeedbackForm.value = false;
  }
};

const updateAppointmentStatus = async () => {
  if (!selectedAppointment.value || !selectedStatus.value) {
    toast.error('Please select a status to update.');
    return;
  }

  try {
    const payload = {
      status: selectedStatus.value,
      link: appointmentLink.value,
    };

    // Update appointment status and link
    await axios.put(`/appointments/${selectedAppointment.value.id}`, payload);

    // If the status is "completed" and there's a session report, trigger the session report creation
    if (selectedStatus.value === 'completed' && sessionReport.value.duration) {
      await axios.post(`/appointments/${selectedAppointment.value.id}/session-report`, {
        duration: sessionReport.value.duration,
        activity_type: sessionReport.value.activity_type,
        other_activity: sessionReport.value.other_activity,
        engagement_level: sessionReport.value.engagement_level,
      });
      toast.success('Session report submitted successfully.');
    }

    toast.success('Appointment status updated successfully.');
    closeAppointmentModal();
    fetchAppointments(); // Refresh appointments list
  } catch (error) {
    toast.error('Failed to update appointment status.');
  }
};




const updateAppointmentLink = async () => {
  if (!selectedAppointment.value) {
    toast.error('No appointment selected.');
    return;
  }

  try {
    await axios.put(`/appointments/${selectedAppointment.value.id}/link`, {
      link: appointmentLink.value, // Only update the link
    });
    toast.success('Appointment link updated successfully.');
    fetchAppointments(); // Refresh appointments list to show updated link
  } catch (error) {
    toast.error('Failed to update appointment link.');
  }
};

const closeAppointmentModal = () => {
  showAppointmentModal.value = false;
  selectedAppointment.value = null;
  selectedStatus.value = '';
};

const fetchAppointments = async () => {
  try {
    const response = await axios.get('/therapist/appointments');
    appointments.value = response.data.appointments || [];
  } catch (error) {
    toast.error('Failed to load appointments.');
  }
};
const fetchAssessments = async () => {
  try {
    const response = await axios.get('/assessments');
    assessments.value = response.data.assessments || [];
  } catch (error) {
    toast.error('Failed to load assessments.');
  }
};

const fetchAvailableDates = async () => {
  try {
    const response = await axios.get('/available-dates/all');
    console.log('Response data:', response.data); // Add logging to see what you get
    availableDates.value = response.data.availableDates.map(date => ({
      ...date,
      time: date.time,
    })) || [];
  } catch (error) {
    console.error('Error fetching available dates:', error.response);
    toast.error('Failed to load available dates: ' + (error.response?.data?.message || 'Unknown error'));
  }
};

const fetchAvailableDatesForClient = async () => {
  try {
    const response = await axios.get('/available-dates/all');
    console.log('Available Dates Response:', response.data);

    // Check if the response data is in the expected format
    if (response.data && Array.isArray(response.data.availableDates)) {
      availableDatesForClient.value = response.data.availableDates || [];
    } else {
      console.warn('availableDates is not an array or is undefined');
      availableDatesForClient.value = [];
    }
  } catch (error) {
    console.error('Failed to load available dates.', error.response ? error.response.data : error);
    toast.error('Failed to load available dates: ' + (error.response?.data?.message || 'Unknown error'));
  }
};



// Time conversion function
const convertTo12HourFormat = (time) => {
  console.log('Converting time:', time); // Log the time being converted
  if (!time) return ''; // Return empty if time is not provided

  // Extract hours and minutes, ignoring seconds if present
  const [hours, minutes] = time.split(':').slice(0, 2);
  const hour = parseInt(hours, 10);

  // Correctly assign AM/PM
  const period = hour >= 12 ? 'PM' : 'AM';

  // Convert to 12-hour format
  const formattedHour = hour % 12 || 12; // Adjust 0 to 12 for 12-hour clock
  console.log(`Formatted time: ${formattedHour}:${minutes} ${period}`); // Log final output for verification

  return `${formattedHour}:${minutes} ${period}`;
};


const saveAvailableDate = async () => {
  try {
    // Access the actual values from the reactive references
    const dateValue = newAvailableDate.value; // Assuming newAvailableDate is a ref
    const timeValue = newAvailableTime.value; // Assuming newAvailableTime is a ref

    console.log('Sending:', {
      available_date: dateValue,
      available_time: timeValue,
    });

    await axios.post('/available-dates', {
      available_date: dateValue,
      available_time: timeValue,
    });

    fetchAvailableDates(); // Reload the dates after saving
  } catch (error) {
    console.error('Failed to save available date', error.response?.data || error);
    toast.error('Failed to save available date.');
  }
};


const deleteDate = async (id) => {
  try {
    await axios.delete(`/available-dates/${id}`);
    fetchAvailableDates();
  } catch (error) {
    console.error('Error deleting date:', error);
    toast.error('Failed to delete available date.');
  }
};

const requestAppointment = async (date) => {
  try {
    await axios.post('/appointments', {
      appointment_date: date.date,
      appointment_time: date.time,
      therapist_id: date.therapist_id,
    });
    toast.success('Appointment booked successfully!');
    fetchAvailableDatesForClient();
    fetchAppointments();
  } catch (error) {
    toast.error('Failed to book appointment.');
  }
};
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
const showDetails = ref({});
const showAssessmentModal = ref(false);


// Open the modal for creating or editing an assessment
const openModal = (assessment = null) => {
  selectedAssessment.value = assessment;
  assessmentComment.value = assessment ? assessment.comment : '';  // Populate the comment if editing
  showAssessmentModal.value = true;
};
const toggleDetails = (id) => {
  showDetails.value[id] = !showDetails.value[id];
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
const isUser = computed(() => {
  return roles.includes("user");
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
async function fetchMentalStateCounts() {
  try {
    const response = await axios.get(`/mental-state-counts/${selectedSessionId.value}/${authUser.value.id}`);
    const data = response.data.mentalStateCounts;

    // Update mentalStateCounts based on the API response
    mentalStateCounts.value = [
      data.improved || 0,
      data.stable || 0,
      data.deteriorated || 0,
    ];
  } catch (error) {
    console.error('Error fetching mental state counts:', error);
  }
}

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

console.log('Chart Series Data:', chartSeries.value);
watch(clientId, async (newValue) => {
  if (newValue) {
    localStorage.setItem(CLIENT_ID_KEY, newValue); // Save to localStorage
    await fetchMoods(); // Fetch moods for the selected recipient
    await fetchAssessmentComment();
  }
});
watch(mentalStateCounts, (newCounts) => {
  chartSeries.value[0].data = [
    newCounts.improved || 0,
    newCounts.stable || 0,
    newCounts.deteriorated || 0
  ];
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
    const response = await axios.get('/users3'); // Calls the updated API
    users.value = response.data; // Store the users
  } catch (error) {
    console.error('Error fetching user list:', error);
    toast.error('Failed to fetch user list.');
  }
};

// Update session progress for the selected user (therapist only)

const formatDate = (dateObj) => {
  // const [year, month, day] = dateObj.date.split('-').map(part => parseInt(part, 10));
  // console.log(`Extracted Date Parts - Year: ${year}, Month: ${month}, Day: ${day}`);

  // const timeString = dateObj.time ? dateObj.time : '00:00:00';
  // console.log(`Raw Time String: ${timeString}`);

  // const dateString = `${month}/${day}/${year}`;
  // return `${dateString}, ${timeString}`;
  if (!dateObj || !dateObj.date) return 'Invalid date';

  const [year, month, day] = dateObj.date.split('-').map(part => parseInt(part, 10));
  const dateString = `${month}/${day}/${year}`;
  return dateString; // Only return the formatted date
};
async function fetchSessionReportAndMentalState() {
  if (!selectedSessionId.value) return;

  try {
    // Fetch session report
    const sessionReportResponse = await axios.get(`/session-report/${selectedSessionId.value}`);
    console.log('Session Report Response:', sessionReportResponse.data); // Log response

    // Fetch mental state counts
    const mentalStateCountsResponse = await axios.get(`/mental-state-counts/${selectedTherapistId.value}/${authUser.value.id}`);
    console.log('Mental State Counts Response:', mentalStateCountsResponse.data); // Log response

    // Check if the session report response is valid
    if (sessionReportResponse.data.sessionReport) {
      sessionReport.value = sessionReportResponse.data.sessionReport;
    } else {
      sessionReport.value = null; // Handle no report case
    }

    // Update mental state counts and chart series
    if (mentalStateCountsResponse.data && mentalStateCountsResponse.data.mentalStateCounts) {
      const counts = mentalStateCountsResponse.data.mentalStateCounts;

      // Update chart series directly for pie chart
      chartSeries.value = [
        counts.improved || 0,
        counts.stable || 0,
        counts.deteriorated || 0,
      ]; // Set chart data directly as an array of numbers
    } else {
      chartSeries.value = [0, 0, 0]; // Reset chart data for pie chart
    }

    // Log chart data to see what is being passed
    console.log('Updated Chart Series:', chartSeries.value);

  } catch (error) {
    console.error('Error fetching session report or mental state counts:', error);
    sessionReport.value = null; // Reset on error
    mentalStateCounts.value = { improved: 0, stable: 0, deteriorated: 0 }; // Default mental state counts
    chartSeries.value[0].data = [0, 0, 0]; // Reset chart values
  }
}

onMounted(async () => {
  try {
    await axios.get('/sanctum/csrf-cookie'); // Ensure CSRF cookie is set

    if (isTherapist.value) {
      // Fetch list of users if the logged-in user is a therapist
      await fetchAppointments();
      await fetchUserList();
      await fetchAnnouncements();
      await fetchAssessmentComment();
      await fetchTherapySessionData(clientId.value || authUser.value.id); // Ensure the session data is also fetched


    } else {
      // Fetch therapy session data for the current logged-in user

      fetchMoods();
      updateCalendarAttributes();
      await fetchAppointments();
      await fetchTherapySessionData(authUser.value.id);
      await fetchAssessmentComment(authUser.value.id);
      await fetchAnnouncements();
      await fetchTherapySessionData(clientId.value || authUser.value.id); // Ensure the session data is also fetched
      await fetchPendingAppointments();
    }
    fetchTherapists();
    const response = await axios.get(`/completed-sessions/${authUser.value.id}`);
    completedSessions.value = response.data.appointments;
    fetchAvailableDates();
    fetchAvailableDatesForClient();

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
.ticketing-system {
  max-width: 800px;
  margin: 0 auto;
}

.mood-selection {
  max-width: 1000px;
  margin: 0 auto;
  border: 1px solid #ddd;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  background-color: #ffffff;
}
.feedback-form-card {
  max-width: 600px;
  margin: 0 auto;
}
.form-title {
  font-size: 1.5em;
  font-weight: bold;
  margin-bottom: 1em;
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
  margin-bottom: 15px;
  /* Add margin at the bottom */
}

.assessment-list .assessment-item {
  padding: 10px;
  margin-bottom: 15px;
  background-color: #f9f9f9;
  /* Lighter background for better contrast */
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.assessment-item p {
  white-space: pre-line;
  /* This will ensure that line breaks and spaces are respected */
  margin-bottom: 10px;
  line-height: 1.6;
}

.assessment-item .assessment-date {
  font-size: 12px;
  color: #6b7280;
  /* Light gray for the date text */
}

/* Change input color to white */
input[type="number"] {
  background-color: white;
  /* Change the background to white */
  color: black;
  /* Ensure the text is still visible */
  border: 1px solid #ddd;
  /* Light gray border */
  border-radius: 6px;
  padding: 10px;
  margin-top: 5px;
  /* Add some space between the input and label */
}

input[type="number"]:focus {
  outline: none;
  border-color: #3182ce;
  /* Blue border on focus */
  box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5);
  /* Focus shadow effect */
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
  border-top: 5px solid #a0aec0;
  /* Gray color */
  pointer-events: none;
  transform: translateY(-50%);
}

.selectable-appointment {
  cursor: pointer;
  padding: 10px;
  border-radius: 10px;
  transition: background-color 0.3s;
  border: 1px solid black;
}

.selectable-appointment:hover {
  background-color: #f1f5f9;
  /* Light gray to indicate hover */
}

.appointments-list {
  padding: 1rem;
  background-color: #f9f9f9;
  /* Light background for the list */
  border-radius: 0.5rem;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.appointment-item {
  margin-bottom: 1rem;
  padding: 1rem;
  background-color: #ffffff;
  /* White background for individual appointments */
  border: 1px solid #e0e0e0;
  /* Light border */
  border-radius: 0.5rem;
  transition: background-color 0.3s;
  /* Smooth background transition */
}

.appointment-item:hover {
  background-color: #f1f1f1;
  /* Darker background on hover */
}

.details-toggle {
  background: none;
  /* Remove default button background */
  border: none;
  /* Remove border */
  color: #007bff;
  /* Blue color for the link */
  cursor: pointer;
  /* Pointer cursor */
  font-size: 1rem;
  /* Font size */
  padding: 0;
  /* Remove padding */
  text-align: left;
  /* Align text to the left */
}

.details-toggle:hover {
  text-decoration: underline;
  /* Underline on hover */
}

.appointment-details {
  margin-top: 0.5rem;
  /* Spacing above details */
  padding: 0.5rem;
  background-color: #f8f8f8;
  /* Light background for details */
  border-radius: 0.25rem;
  /* Slightly rounded corners */
}

.feedback-form-card {
  max-width: 600px;
  margin: 0 auto;
}

.form-title {
  font-size: 1.5em;
  font-weight: bold;
  margin-bottom: 1em;
}

.border-bottom {
  border-bottom: 2px solid black;
  /* Change color and thickness as needed */
  padding-bottom: 0.5rem;
  /* Add some space below the text */
  margin-bottom: 1rem;
  /* Space between the header and following content */
}
</style>
