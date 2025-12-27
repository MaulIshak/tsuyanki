import './assets/main.css'
import '../node_modules/vue-sonner/lib/index.css'

import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

const app = createApp(App)

app.use(router)
app.mount('#app')
