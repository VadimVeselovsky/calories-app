import axios from "axios"

const instance = axios.create({
    baseURL: 'http://calories-api/api/app',
    headers: { 'Authorization': 'Bearer 6|Zm4KlzOHY22lX6J2asrqdYp1QMzsPptp2neaBCAC' }
});

window.axios = instance;

export default instance;