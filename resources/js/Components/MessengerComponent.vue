<template>
  <div id="chatify-messenger" class="fixed bottom-0 right-0 w-96 h-128 z-50 bg-white shadow-lg rounded-t-lg overflow-hidden">
    <div class="header bg-blue-500 text-white p-4 flex items-center justify-between">
      <h2 class="text-lg font-semibold">Chat</h2>
      <button @click="closeMessenger" class="text-white hover:text-gray-200 focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <div class="messenger h-full">
      <iframe src="/chat" class="w-full h-full border-none"></iframe>
    </div>
  </div>
</template>

<script>
export default {
  mounted() {
    window.Echo.private('chat')
      .listen('MessageSent', (e) => {
        console.log(e.message);
      });
  },
  methods: {
    closeMessenger() {
      const messenger = document.getElementById('chatify-messenger');
      messenger.style.display = 'none';
    }
  }
};
</script>

<style scoped>
#chatify-messenger {
  max-height: 80vh;
  border-top-left-radius: 1rem;
  border-top-right-radius: 1rem;
  box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}
</style>