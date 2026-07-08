import '../css/app.css';
import * as bootstrap from 'bootstrap';
import { initTheme } from './modules/theme';
import { initSidebar } from './modules/sidebar';
import { initAOS } from './vendors/aos-init';
import { initDataTables } from './modules/datatable';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initSidebar();
  initAOS();
  initDataTables();
});