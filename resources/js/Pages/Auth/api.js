// import axios from "axios";

// const loginRest = async (username, secret) => {
//   return await axios.get("https://api.chatengine.io/users/me/", {
//     headers: {
//       "Project-ID": "9dc04e8b-fd9c-4f0c-8a32-57fdb204a3a0",
//       "User-Name": username,
//       "User-Secret": secret,
//     },
//   });
// };

// const signupRest = async (username, secret, email, first_name, last_name) => {
//   return await axios.post(
//     "https://api.chatengine.io/users/",
//     { username, secret, email, first_name, last_name },
//     { headers: { "Private-Key": "c87f816e-669f-4bca-8c89-d85fbc2a5695" } }
//   );
// };

// export { loginRest, signupRest };