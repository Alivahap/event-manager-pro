document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.emp-rsvp-form');
  if (!form) return;

  const msg = form.querySelector('.emp-rsvp-msg');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    msg.textContent = '';

    const fd = new FormData(form);
    fd.append('action', 'emp_rsvp');

    try {
      const res = await fetch(window.empAjax.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      });

      const data = await res.json();

      if (!data || !data.success) {
        msg.textContent = (data && data.data && data.data.message) ? data.data.message : 'Error';
        return;
      }

      msg.textContent = data.data.message || 'OK';
      form.reset();
    } catch (err) {
      msg.textContent = 'Network error';
    }
  });
});