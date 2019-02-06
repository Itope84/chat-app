<template>
  <div class="card-body chat-page px-0 py-0">
    <div class="d-flex chat-container">
      <div class="col-md-4 border-right px-0">
        <ul class="list-group list-group-flush">
          <li class="list-group-item user-list-item" :class="{'active': activeChat.id === chat.id}" v-for="chat in chats" :key="chat.id">
            <a href="#" @click.prevent="selectChat(chat.id)" class="nav-link">
              <p class="card-text">{{chat.partner.name}}</p>
            </a>
          </li>
        </ul>
      </div>

      <div
        class="col-md-8 d-md-block h-100 position-relative mb-0 px-0"
        :class="{'d-none': !showChatWindow}"
      >
        <div class="chat-window w-100 h-100">
          <div class="chat-title d-flex align-items-center border-bottom py-2 px-2">
            <h5 class="mb-0" v-if="activeChat">
              {{activeChat.partner.name}}
              <span
                class="h3 d-md-none btn bg-transparent"
                @click="showChatWindow = !showChatWindow"
              >
                <i class="fas fa-arrow-left"></i>
              </span>
            </h5>
          </div>

          <div class="chat-messages px-4 py-3" v-if="activeChat" id="messageWindow">
            <div
              class="message"
              v-for="(message, index) in messages"
              :key="'message' + index"
              :class=" {'send': iAmSender(message.sender_id), 'receive': !iAmSender(message.sender_id)}"
            >
              <template v-if="iAmSender(message.sender_id)">
                <div class>
                  <div class="px-4 py-2 message-box mx-2" v-html="message.body"></div>
                  <span class="tiny text-right mx-2 d-block">25 Aug 2018 08:45 PM</span>
                </div>

                <div class="chat-image mr-2 rounded-circle">
                  <img
                    src="https://images.vexels.com/media/users/3/147102/isolated/preview/082213cb0f9eabb7e6715f59ef7d322a-instagram-profile-icon-by-vexels.png"
                    alt
                    class="img-fluid"
                  >
                </div>
              </template>

              <template v-else>
                <div class="chat-image mr-2 rounded-circle">
                  <img
                    src="https://www.freeiconspng.com/uploads/png-file-png-file-png-file-png-file-png-file-27.png"
                    alt
                    class="img-fluid"
                  >
                </div>

                <div class>
                  <div class="px-4 py-2 message-box mx-2" v-html="message.body"></div>
                  <span class="tiny text-left mx-2 d-block">25 Aug 2018 08:45 PM</span>
                </div>
              </template>
            </div>
          </div>

          <div class="chat-footer border-top px-2 py-2">
            <div class="input-group mb-1">
              <textarea
                rows="1"
                v-model="message"
                class="form-control"
                placeholder="New Message"
                aria-label="New Message"
                @keydown="inputHandler"
              ></textarea>
              <div class="input-group-append">
                <button
                  class="btn btn-primary"
                  type="button"
                  :disabled="sending"
                  @click="sendMessage"
                >
                  <i class="fas fa-spinner fa-spin" v-if="sending"></i> Send
                </button>
              </div>
            </div>

            <!-- <span class>Maximum upload size: 5MB.</span> -->
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import fire from "../fire.js";
export default {
  props: ["chatId", "userId"],
  data() {
    return {
      token: "",
      chats: [],
      messages: [],
      activeChatId: this.chatId,
      message: "",
      sending: false,
      showChatWindow: false,
      chatListBackup: [],
      query: ""
    };
  },

  methods: {
    selectChat(chatId) {
      this.showChatWindow = true;
      this.activeChatId = chatId;
    },
    inputHandler(e) {
      if (e.keyCode === 13 && !e.shiftKey) {
        e.preventDefault();
        this.sendMessage();
      }
    },
    sendMessage() {
      // e.preventDefault();
      this.sending = true;
      const message = new FormData();

      message.append("message", this.message);
      // To-Do: Push message to firebase

      axios
        .post(`/chats/${this.activeChatId}/sendMessage`, message, {
          headers: { "Content-Type": "multipart/form-data" }
        })
        .then(response => {
          this.message = "";
          this.sending = false;
        })
        .catch(error => {
          this.sending = false;
        });
    },
    iAmSender(sender_id) {
      return sender_id === this.userId;
    },
    fetchMessages(chatId) {
      let vm = this;
      const itemsRef = fire
        .database()
        .ref()
        .child("messages")
        .orderByChild("chat_id")
        .equalTo(chatId);

      itemsRef.on("value", snapshot => {
        let data = snapshot.val();
        let messages = [];
        if (data) {
          Object.keys(data).forEach(key => {
            data[key].message
              ? (data[key].message = data[key].message.replace(
                  /(\n)/g,
                  `<br />`
                ))
              : "";
            messages.push({ ...data[key] });
          });
        }
        vm.messages = messages;
      });
    },
  },

  computed: {
    activeChat() {
      let id = this.activeChatId;
      this.fetchMessages(this.activeChatId);
      return this.chats.reduce((prev, chat) => {
        return chat.id === id ? chat : prev;
      }, null);
    }
  },

  beforeMount() {
    let url = "/chats/all";
    axios.get(url).then(response => {
      let chats = [];

      Object.keys(response.data.data).forEach(key => {
        chats.push(response.data.data[key]);
      });

      this.chats = chats;
    });
  }
};
</script>
