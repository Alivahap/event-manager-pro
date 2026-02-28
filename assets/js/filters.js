document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('.emp-filters');
  if (!form) return;

  const type = form.querySelector('select[name="event_type"]');
  const from = form.querySelector('input[name="from"]');
  const to   = form.querySelector('input[name="to"]');
  const s    = form.querySelector('input[name="s"]');


  if (type) {
    type.addEventListener('change', () => {
      if (from) from.value = '';
      if (to)   to.value = '';
      if (s)    s.value = '';
    });
  }


  form.addEventListener('submit', () => {
    [type, from, to, s].forEach((el) => {
      if (!el) return;
      if ((el.value || '').trim() === '') el.disabled = true;
    });
  });
});