import '../css/app.css';
import * as bootstrap from 'bootstrap';
import { initTheme } from './modules/theme';
import { initSidebar } from './modules/sidebar';
import { initAOS } from './vendors/aos-init';
import { initDataTables } from './modules/datatable';
import { initProfileMenu } from './modules/profile-menu.js';
import { initMonitoringDokumen } from './modules/monitoring-dokumen';
import { initDashboardCharts } from './modules/dashboard-charts';
import { initAccordion } from './modules/accordion';
import { initSdmBezetting } from './modules/sdm-bezetting';
import { initCountUp, initDistributionBars } from './modules/count-up';import { initMonitoringCuti } from './modules/monitoring-cuti';
import { initMonitoringEvkin } from './modules/monitoring-evkin';


window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initSidebar();
  initAOS();
  initDataTables();
  initProfileMenu();
  initMonitoringDokumen();
  initDashboardCharts();
  initAccordion();
  initSdmBezetting();
  initCountUp();
  initDistributionBars();
  initMonitoringCuti();
  initMonitoringEvkin();
});