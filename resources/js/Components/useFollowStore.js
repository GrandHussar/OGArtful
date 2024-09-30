// import { defineStore } from 'pinia';
// import axios from 'axios';

// export const useFollowStore = defineStore('follow', {
//   state: () => ({
//     followingUsers: new Set(JSON.parse(localStorage.getItem('followingUsers')) || []),
//   }),
//   actions: {
//     async fetchFollowingUsers(userId) {
//       try {
//         const response = await axios.get(`/users/${userId}/following`);
//         if (response.status === 200) {
//           const userIds = response.data.userIds || [];
//           this.followingUsers = new Set(userIds);
//           localStorage.setItem('followingUsers', JSON.stringify([...this.followingUsers]));
//         }
//       } catch (error) {
//         console.error('Error fetching following users:', error);
//       }
//     },
//     async toggleFollow(userId) {
//       try {
//         const response = await axios.post(`/users/${userId}/toggle`);
        
//         if (response.status === 200) {
//           if (this.followingUsers.has(userId)) {
//             this.followingUsers.delete(userId);
//           } else {
//             this.followingUsers.add(userId);
//           }
//           localStorage.setItem('followingUsers', JSON.stringify([...this.followingUsers]));
//         }
//       } catch (error) {
//         console.error('Error toggling follow status:', error);
//       }
//     }
//   }
// });
import { defineStore } from 'pinia';
import axios from 'axios';

export const useFollowStore = defineStore('follow', {
  state: () => ({
    followingUsers: new Set(JSON.parse(localStorage.getItem('followingUsers')) || []),
    currentUserId: localStorage.getItem('currentUserId') || null,  // Initialize from localStorage
  }),
  actions: {
    async fetchFollowingUsers(userId) {
      try {
        const response = await axios.get(`/users/${userId}/following`);
        if (response.status === 200) {
          const userIds = response.data.userIds || [];
          this.followingUsers = new Set(userIds);
          localStorage.setItem('followingUsers', JSON.stringify([...this.followingUsers]));
          this.currentUserId = userId;  // Update the current user ID
          localStorage.setItem('currentUserId', userId);  // Store the current user ID
        }
      } catch (error) {
        console.error('Error fetching following users:', error);
      }
    },
    async toggleFollow(userId) {
      try {
        const response = await axios.post(`/users/${userId}/toggle`);
        
        if (response.status === 200) {
          if (this.followingUsers.has(userId)) {
            this.followingUsers.delete(userId);
          } else {
            this.followingUsers.add(userId);
          }
          localStorage.setItem('followingUsers', JSON.stringify([...this.followingUsers]));
        }
      } catch (error) {
        console.error('Error toggling follow status:', error);
      }
    },
    handleAccountChange(newUserId) {
      if (newUserId !== this.currentUserId) {
        this.followingUsers.clear();  // Clear the current following users
        localStorage.removeItem('followingUsers');  // Clear the stored data
        localStorage.setItem('currentUserId', newUserId);  // Update the current user ID
        this.fetchFollowingUsers(newUserId);  // Fetch the following users for the new account
      }
    },
  }
});