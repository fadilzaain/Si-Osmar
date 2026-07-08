import AOS from 'aos';

export function initAOS() {
  AOS.init({
    duration: 400,
    easing: 'ease-out-cubic',
    once: true,
    offset: 40,
  });
}