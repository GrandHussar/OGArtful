import axios from 'axios';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

export function useMoodManager() {
  const toast = useToast();
  const moods = ref([]);

  const fetchMoods = async (clientId) => {
    try {
      const response = await axios.get(`/moods?clientId=${clientId}`);
      moods.value = response.data || [];
    } catch (error) {
      toast.error('Failed to load moods.');
    }
  };

  const selectMood = async (date, mood) => {
    try {
      await axios.post('/moods', { date, mood });
      toast.success('Mood saved successfully!');
      fetchMoods(); // Refresh moods after saving
    } catch (error) {
      toast.error('Failed to save mood.');
    }
  };
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
  

  return { moods, fetchMoods, selectMood,updateCalendarAttributes };
}
