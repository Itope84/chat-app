import firebase from "firebase/app";
import "firebase/database";

var config = {
    apiKey: "AIzaSyD9JB8e5Kn0ZfslmvIksM-dQNohb7HCHOU",
    authDomain: "chat-app-8855d.firebaseapp.com",
    databaseURL: "https://chat-app-8855d.firebaseio.com",
    projectId: "chat-app-8855d",
    storageBucket: "chat-app-8855d.appspot.com",
    messagingSenderId: "328678884916"
};

var fire = firebase.initializeApp(config);
export default fire;
