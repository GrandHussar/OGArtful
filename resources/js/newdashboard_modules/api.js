import axios from 'axios';
import { useToast } from 'vue-toastification';

export function useApi() {
  const toast = useToast();

  const fetchAppointments = async () => {
    try {
      const response = await axios.get('/therapist/appointments');
      return response.data.appointments || [];
    } catch (error) {
      toast.error('Failed to load appointments.');
      return [];
    }
  };

  const fetchAvailableDates = async () => {
    try {
      const response = await axios.get('/available-dates/all');
      return response.data.availableDates || [];
    } catch (error) {
      toast.error('Failed to load available dates.');
      return [];
    }
  };

  const fetchUsers = async () => {
    try {
      const response = await axios.get('/users2');
      return response.data;
    } catch (error) {
      toast.error('Failed to load users.');
      return [];
    }
  };

  return { fetchAppointments, fetchAvailableDates, fetchUsers };
}
