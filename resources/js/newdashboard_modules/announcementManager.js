import axios from 'axios';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

export function useAnnouncementManager() {
  const toast = useToast();
  const announcements = ref([]);

  const fetchAnnouncements = async () => {
    try {
      const response = await axios.get('/announcements');
      announcements.value = response.data || [];
    } catch (error) {
      toast.error('Failed to load announcements.');
    }
  };

  const createAnnouncement = async (title, content) => {
    try {
      await axios.post('/announcements', { title, content });
      toast.success('Announcement created.');
      fetchAnnouncements();
    } catch (error) {
      toast.error('Failed to create announcement.');
    }
  };

  const deleteAnnouncement = async (id) => {
    try {
      await axios.delete(`/announcements/${id}`);
      toast.success('Announcement deleted.');
      fetchAnnouncements();
    } catch (error) {
      toast.error('Failed to delete announcement.');
    }
  };

  return { announcements, fetchAnnouncements, createAnnouncement, deleteAnnouncement };
}
