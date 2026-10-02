document.addEventListener('DOMContentLoaded', function () {

  // ---------------------------------------------------------------
  // Detect GPS location (User dashboard — new request modal)
  // ---------------------------------------------------------------
  const detectBtn = document.getElementById('detectLocationBtn');
  if (detectBtn) {
    detectBtn.addEventListener('click', function () {
      const display = document.getElementById('locationDisplay');
      if (!navigator.geolocation) {
        display.value = 'Geolocation not supported by your browser.';
        return;
      }
      display.value = 'Detecting...';
      navigator.geolocation.getCurrentPosition(
        function (pos) {
          document.getElementById('latitude').value = pos.coords.latitude;
          document.getElementById('longitude').value = pos.coords.longitude;
          display.value = `Lat ${pos.coords.latitude.toFixed(5)}, Lng ${pos.coords.longitude.toFixed(5)} (detected)`;
        },
        function () {
          display.value = 'Unable to detect location — please allow location access.';
        }
      );
    });
  }

  // ---------------------------------------------------------------
  // Submit new request (AJAX — asynchronous dispatch)
  // ---------------------------------------------------------------
  const newRequestForm = document.getElementById('newRequestForm');
  if (newRequestForm) {
    newRequestForm.addEventListener('submit', function (e) {
      e.preventDefault();
      const alertBox = document.getElementById('requestFormAlert');
      const submitBtn = document.getElementById('submitRequestBtn');

      if (!document.getElementById('latitude').value) {
        alertBox.innerHTML = '<div class="alert alert-warning py-2">Please detect your location first.</div>';
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = 'Submitting...';

      fetch('/ajax/submit_request.php', {
        method: 'POST',
        body: new FormData(newRequestForm)
      })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            alertBox.innerHTML = `<div class="alert alert-success py-2">${data.message}</div>`;
            setTimeout(() => window.location.reload(), 900);
          } else {
            alertBox.innerHTML = `<div class="alert alert-danger py-2">${data.message}</div>`;
            submitBtn.disabled = false;
            submitBtn.textContent = 'Submit Request';
          }
        })
        .catch(() => {
          alertBox.innerHTML = '<div class="alert alert-danger py-2">Network error. Please try again.</div>';
          submitBtn.disabled = false;
          submitBtn.textContent = 'Submit Request';
        });
    });
  }

  // ---------------------------------------------------------------
  // Mechanic: accept a job
  // ---------------------------------------------------------------
  document.querySelectorAll('.accept-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const requestId = this.dataset.requestId;
      this.disabled = true;
      this.textContent = 'Accepting...';

      const fd = new FormData();
      fd.append('request_id', requestId);

      fetch('/ajax/accept_request.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          alert(data.message);
          window.location.reload();
        });
    });
  });

  // ---------------------------------------------------------------
  // Mechanic: mark job completed
  // ---------------------------------------------------------------
  document.querySelectorAll('.complete-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const requestId = this.dataset.requestId;
      const fd = new FormData();
      fd.append('request_id', requestId);

      fetch('/ajax/complete_request.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          alert(data.message);
          window.location.reload();
        });
    });
  });

  // ---------------------------------------------------------------
  // Admin: verify / revoke mechanic
  // ---------------------------------------------------------------
  document.querySelectorAll('.verify-btn, .revoke-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      const userId = this.dataset.userId;
      const action = this.classList.contains('verify-btn') ? 'verify' : 'revoke';
      const fd = new FormData();
      fd.append('user_id', userId);
      fd.append('action', action);

      fetch('/ajax/verify_mechanic.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          window.location.reload();
        });
    });
  });

  // ---------------------------------------------------------------
  // Star rating widget
  // ---------------------------------------------------------------
  const starRating = document.getElementById('starRating');
  if (starRating) {
    const stars = starRating.querySelectorAll('i');
    const ratingInput = document.getElementById('ratingValue');

    function highlight(value) {
      stars.forEach(s => s.classList.toggle('active', s.dataset.value <= value));
    }
    highlight(5);

    stars.forEach(star => {
      star.addEventListener('click', function () {
        ratingInput.value = this.dataset.value;
        highlight(this.dataset.value);
      });
      star.addEventListener('mouseenter', function () {
        highlight(this.dataset.value);
      });
    });
    starRating.addEventListener('mouseleave', function () {
      highlight(ratingInput.value);
    });
  }

  // ---------------------------------------------------------------
  // Rate modal: capture which request is being rated
  // ---------------------------------------------------------------
  document.querySelectorAll('.rate-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.getElementById('rateRequestId').value = this.dataset.requestId;
    });
  });

  const rateForm = document.getElementById('rateForm');
  if (rateForm) {
    rateForm.addEventListener('submit', function (e) {
      e.preventDefault();
      fetch('/ajax/submit_rating.php', { method: 'POST', body: new FormData(rateForm) })
        .then(r => r.json())
        .then(data => {
          alert(data.message);
          if (data.success) window.location.reload();
        });
    });
  }

  // ---------------------------------------------------------------
  // Mechanic dashboard: poll for new pending requests every 15s
  // (lightweight AJAX polling — simulates the asynchronous dispatch
  // broadcast described in the project's research insights)
  // ---------------------------------------------------------------
  const pendingCountEl = document.getElementById('pendingCount');
  if (pendingCountEl) {
    setInterval(() => {
      fetch('/ajax/poll_pending.php')
        .then(r => r.json())
        .then(data => {
          if (parseInt(pendingCountEl.textContent, 10) !== data.count) {
            pendingCountEl.textContent = data.count;
            pendingCountEl.classList.add('bg-danger');
          }
        });
    }, 15000);
  }

});
