import Swal from 'sweetalert2';

const toastMixin = Swal.mixin({
  toast: true,
  position: 'top-end',
  showConfirmButton: false,
  timer: 3000,
  timerProgressBar: true,
});

export function showToast(type, message) {
  toastMixin.fire({ icon: type, title: message });
}