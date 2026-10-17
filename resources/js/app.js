import { createApp } from 'vue';
import { Quasar } from 'quasar';
import '@quasar/extras/material-icons/material-icons.css';
import 'quasar/dist/quasar.css';
import App from './App.vue';

const element = document.getElementById('app');

createApp(App, { firstDay: element.dataset.firstDay, lastDay: element.dataset.lastDay }).use(Quasar).mount(element);
