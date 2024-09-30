/**
 *-------------------------------------------------------------
 * Global variables
 *-------------------------------------------------------------
 */

 console.log('Chatify code.js script loaded');
 window.chatify = window.chatify || {};
 chatify.init = function() {
   console.log('Chatify initialized');
   // Existing initialization code...
 
   /**
    *-------------------------------------------------------------
    * Pusher initialization
    *-------------------------------------------------------------
    */
   if (typeof Pusher !== 'undefined' && chatify.pusher) {
     Pusher.logToConsole = chatify.pusher.debug;
     const pusher = new Pusher(chatify.pusher.key, {
       encrypted: chatify.pusher.options.encrypted,
       cluster: chatify.pusher.options.cluster,
       wsHost: chatify.pusher.options.host,
       wsPort: chatify.pusher.options.port,
       wssPort: chatify.pusher.options.port,
       forceTLS: chatify.pusher.options.useTLS,
       authEndpoint: chatify.pusherAuthEndpoint,
       auth: {
         headers: {
           "X-CSRF-TOKEN": csrfToken,
         },
       },
     });
 
     /**
      *-------------------------------------------------------------
      * Pusher channels and event listening..
      *-------------------------------------------------------------
      */
     
     // subscribe to the channel
     const channelName = "private-chatify";
     var channel = pusher.subscribe(`${channelName}.${auth_id}`);
     var clientSendChannel;
     var clientListenChannel;
 
     function initClientChannel() {
       if (getMessengerId()) {
         clientSendChannel = pusher.subscribe(`${channelName}.${getMessengerId()}`);
         clientListenChannel = pusher.subscribe(`${channelName}.${auth_id}`);
       }
     }
     initClientChannel();
 
     // Listen to messages, and append if data received
     channel.bind("messaging", function (data) {
       if (data.from_id == getMessengerId() && data.to_id == auth_id) {
         $(".messages").find(".message-hint").remove();
         messagesContainer.find(".messages").append(data.message);
         scrollToBottom(messagesContainer);
         makeSeen(true);
         // remove unseen counter for the user from the contacts list
         $(".messenger-list-item[data-contact=" + getMessengerId() + "]")
           .find("tr>td>b")
           .remove();
       }
 
       playNotificationSound(
         "new_message",
         !(data.from_id == getMessengerId() && data.to_id == auth_id)
       );
     });
 
     // listen to typing indicator
     clientListenChannel.bind("client-typing", function (data) {
       if (data.from_id == getMessengerId() && data.to_id == auth_id) {
         data.typing == true
           ? messagesContainer.find(".typing-indicator").show()
           : messagesContainer.find(".typing-indicator").hide();
       }
       // scroll to bottom
       scrollToBottom(messagesContainer);
     });
 
     // listen to seen event
     clientListenChannel.bind("client-seen", function (data) {
       if (data.from_id == getMessengerId() && data.to_id == auth_id) {
         if (data.seen == true) {
           $(".message-time")
             .find(".fa-check")
             .before('<span class="fas fa-check-double seen"></span> ');
           $(".message-time").find(".fa-check").remove();
         }
       }
     });
 
     // listen to contact item updates event
     clientListenChannel.bind("client-contactItem", function (data) {
       if (data.to == auth_id) {
         if (data.update) {
           updateContactItem(data.from);
         } else {
           console.error("Can not update contact item!");
         }
       }
     });
 
     // listen on message delete event
     clientListenChannel.bind("client-messageDelete", function (data) {
       $("body").find(`.message-card[data-id=${data.id}]`).remove();
     });
     // listen on delete conversation event
     clientListenChannel.bind("client-deleteConversation", function (data) {
       if (data.from == getMessengerId() && data.to == auth_id) {
         $("body").find(`.messages`).html("");
         $(".messages").find(".message-hint").show();
       }
     });
     // -------------------------------------
     // presence channel [User Active Status]
     var activeStatusChannel = pusher.subscribe("presence-activeStatus");
 
     // Joined
     activeStatusChannel.bind("pusher:member_added", function (member) {
       setActiveStatus(1);
       $(".messenger-list-item[data-contact=" + member.id + "]")
         .find(".activeStatus")
         .remove();
       $(".messenger-list-item[data-contact=" + member.id + "]")
         .find(".avatar")
         .before(activeStatusCircle());
     });
 
     // Leaved
     activeStatusChannel.bind("pusher:member_removed", function (member) {
       setActiveStatus(0);
       $(".messenger-list-item[data-contact=" + member.id + "]")
         .find(".activeStatus")
         .remove();
     });
 
     function handleVisibilityChange() {
       if (!document.hidden) {
         makeSeen(true);
       }
     }
 
     document.addEventListener("visibilitychange", handleVisibilityChange, false);
   } else {
     console.error('Pusher is not defined or chatify.pusher configuration is missing.');
   }
 };
 
 var messenger,
   typingTimeout,
   typingNow = 0,
   temporaryMsgId = 0,
   defaultAvatarInSettings = null,
   messengerColor,
   dark_mode,
   messages_page = 1;
 
 const messagesContainer = $(".messenger-messagingView .m-body"),
   messengerTitleDefault = $(".messenger-headTitle").text(),
   messageInputContainer = $(".messenger-sendCard"),
   messageInput = $("#message-form .m-send"),
   auth_id = $("meta[name=url]").attr("data-user"),
   url = $("meta[name=url]").attr("content"),
   messengerTheme = $("meta[name=messenger-theme]").attr("content"),
   defaultMessengerColor = $("meta[name=messenger-color]").attr("content"),
   csrfToken = $('meta[name="csrf-token"]').attr("content");
 
 const getMessengerId = () => $("meta[name=id]").attr("content");
 const setMessengerId = (id) => $("meta[name=id]").attr("content", id);
 
 // The rest of your existing code...
 
 // On DOM ready
 $(document).ready(function () {
   chatify.init();
   // Your other initialization code...
 });
 