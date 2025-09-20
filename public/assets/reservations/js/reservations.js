async function fetchSlots(date, guests){
  const res = await fetch(`/api/availability?date=${encodeURIComponent(date)}&guests=${encodeURIComponent(guests)}`);
  if(!res.ok) return {slots:[]};
  return await res.json();
}

document.addEventListener('DOMContentLoaded', () => {
  const guestsEl = document.querySelector('#guests');
  const dateEl = document.querySelector('#date');
  const slotsEl = document.querySelector('#slots');
  const timeInput = document.querySelector('#time');
  const LS_KEY_DATE = 'resv_date';
  const LS_KEY_GUESTS = 'resv_guests';
  const LS_KEY_TIME = 'resv_time';

  function weekendMinFor(dateStr){
    if (!dateStr) return 1;
    try {
      const d = new Date(dateStr + 'T00:00:00');
      const dow = d.getDay(); // 0=Sun .. 6=Sat
      return (dow === 0 || dow === 5 || dow === 6) ? 10 : 1;
    } catch(_) { return 1; }
  }

  function applyWeekendMin(){
    if (!guestsEl || !dateEl) return;
    const min = weekendMinFor(dateEl.value);
    guestsEl.min = String(min);
    if (guestsEl.value && Number(guestsEl.value) < min) {
      guestsEl.value = String(min);
      try { localStorage.setItem(LS_KEY_GUESTS, guestsEl.value); } catch(_){}
    }
    // Toggle weekend note
    try {
      const note = document.getElementById('wkndNote');
      if (note) note.style.display = (min > 1) ? 'block' : 'none';
    } catch(_) {}
  }

  async function refreshSlots(){
    const date = dateEl.value;
    applyWeekendMin();
    const guests = guestsEl.value || '1';
    slotsEl.innerHTML = '';
    // Reset selected time in the UI; stored value may re-apply if still available
    timeInput.value = '';
    if(!date) return;
    const data = await fetchSlots(date, guests);
    const storedTime = localStorage.getItem(LS_KEY_TIME);
    let selectedApplied = false;
    (data.slots || []).forEach(s => {
      const b = document.createElement('button');
      b.type='button'; b.className='slot'+(s.available?'':' disabled');
      b.textContent = s.label;
      b.dataset.value = s.time;
      if(!s.available) b.disabled = true;
      b.addEventListener('click', ()=>{
        [...slotsEl.querySelectorAll('.slot')].forEach(x=>x.classList.remove('selected'));
        b.classList.add('selected');
        timeInput.value = s.time;
        localStorage.setItem(LS_KEY_TIME, s.time);
      });
      if (storedTime && storedTime === s.time && s.available && !selectedApplied){
        b.classList.add('selected');
        timeInput.value = s.time;
        selectedApplied = true;
      }
      slotsEl.appendChild(b);
    });
  }

  if(guestsEl && dateEl && slotsEl) {
    // Apply weekend min immediately (on load) and when date changes
    applyWeekendMin();
    // Restore saved values if present
    try {
      const savedDate = localStorage.getItem(LS_KEY_DATE);
      if (!dateEl.value && savedDate) {
        dateEl.value = savedDate;
      }
      const savedGuests = localStorage.getItem(LS_KEY_GUESTS);
      if (!guestsEl.value && savedGuests) {
        guestsEl.value = savedGuests;
      }
    } catch(_){}

    guestsEl.addEventListener('change', () => {
      localStorage.setItem(LS_KEY_GUESTS, guestsEl.value || '');
      refreshSlots();
    });
    dateEl.addEventListener('change', () => {
      localStorage.setItem(LS_KEY_DATE, dateEl.value || '');
      // when date changes, clear previously selected time
      localStorage.removeItem(LS_KEY_TIME);
      applyWeekendMin();
      refreshSlots();
    });
    if(dateEl.value && guestsEl.value) refreshSlots();
  }
});

document.addEventListener('DOMContentLoaded', () => {
  const form = document.querySelector('#step1');
  const timeInput = document.querySelector('#time');
  if (form && timeInput) {
    form.addEventListener('submit', (e) => {
      if (!timeInput.value) {
        alert('Please select a time.');
        e.preventDefault();
      }
    });
  }
});
