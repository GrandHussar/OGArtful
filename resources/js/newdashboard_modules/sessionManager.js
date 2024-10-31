import axios from 'axios';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';

export function useSessionManager() {
  const toast = useToast();
  const sessionsDone = ref(0);
  const totalSessions = ref(0);
  const progressLevel = ref(0);

  const fetchTherapySessionData = async (userId) => {
    try {
      const response = await axios.get(`/therapy-sessions`, { params: { user_id: userId } });
      sessionsDone.value = response.data.sessions_done || 0;
      totalSessions.value = response.data.total_sessions || 0;
      progressLevel.value = ((sessionsDone.value / totalSessions.value) * 100).toFixed(2);
    } catch (error) {
      toast.error('Failed to load session data.');
    }
  };

  const updateTherapySessions = async (clientId, done, total) => {
    try {
      await axios.post(`/therapy-sessions/${clientId}`, { sessions_done: done, total_sessions: total });
      toast.success('Therapy session updated.');
    } catch (error) {
      toast.error('Failed to update therapy session.');
    }
  };

  return { sessionsDone, totalSessions, progressLevel, fetchTherapySessionData, updateTherapySessions };
}
