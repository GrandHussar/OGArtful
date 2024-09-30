<template>
  <AuthenticatedLayout>
  <div class="chat-page flex flex-col h-screen">
    <div class="flex-1 overflow-hidden">
      <div id="messenger" class="h-full">
        <iframe src="/chatify/messenger" class="w-full h-full border-none"></iframe>
      </div>
    </div>
  </div>
  </AuthenticatedLayout>
</template>

<script>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useQuasar } from 'quasar'; // Import useQuasar
export default {
  components: {
      AuthenticatedLayout
    },
    setup() {
    const $q = useQuasar(); // Use Quasar

    return {
      $q
    };
  },
  mounted() {
    const head = document.getElementsByTagName('head')[0];

    const log = (message) => console.log(`[ChatPage] ${message}`);

    // Load jQuery
    const jqueryScript = document.createElement('script');
    jqueryScript.src = 'https://code.jquery.com/jquery-3.7.1.min.js';
    jqueryScript.onload = () => {
      log('jQuery loaded');

      // Load Chatify CSS
      const styleLink = document.createElement('link');
      styleLink.rel = 'stylesheet';
      styleLink.type = 'text/css';
      styleLink.href = '/chatify/css/style.css';
      head.appendChild(styleLink);

      const lightModeLink = document.createElement('link');
      lightModeLink.rel = 'stylesheet';
      lightModeLink.type = 'text/css';
      lightModeLink.href = '/chatify/css/light.mode.css';
      head.appendChild(lightModeLink);

      // Load Chatify JS in correct order
      const script1 = document.createElement('script');
      script1.src = '/chatify/js/font.awesome.min.js';
      script1.onload = () => log('font.awesome.min.js loaded');
      head.appendChild(script1);

      const script2 = document.createElement('script');
      script2.src = '/chatify/js/autosize.js';
      script2.onload = () => log('autosize.js loaded');
      head.appendChild(script2);

      const script3 = document.createElement('script');
      script3.src = '/chatify/js/utils.js';
      script3.onload = () => log('utils.js loaded');
      head.appendChild(script3);

      const script4 = document.createElement('script');
      script4.src = '/chatify/js/code.js';
      script4.onload = () => {
        log('code.js loaded');
        if (typeof window.chatify !== 'undefined') {
          window.chatify.init();
          log('chatify initialized');
        } else {
          log('chatify is not defined');
        }
      };
      head.appendChild(script4);
    };
    jqueryScript.onerror = () => log('Failed to load jQuery');
    head.appendChild(jqueryScript);

    window.Echo.private('chat')
      .listen('MessageSent', (e) => {
        console.log(e.message);
        $q.notify({
                message: 'You have unread messages!',
                color: 'primary',
                avatar: 'https://cdn.quasar.dev/img/boy-avatar.png',
                actions: [
                { icon: 'close', color: 'white', round: true, handler: () => { /* ... */ } }
                ]
            })
      });
  }
};
</script>

<style scoped>
.chat-page {
  height: 100vh;
}
</style>
