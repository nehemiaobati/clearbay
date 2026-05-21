/**
 * ClearBay — Real-Time Ambulance Off-Load Management | Nairobi, Kenya
 * Core Client Interaction and Behavior (Decoupled & Encapsulated)
 */
(() => {
  'use strict';

  // State locks
  let isFetchingQueue = false;
  let isSubmittingSignup = false;

  // 1. Navigation Scroll Behavior
  const nav = document.getElementById('nav');
  if (nav) {
    window.addEventListener('scroll', () => {
      nav.classList.toggle('solid', window.scrollY > 60);
    }, { passive: true }); // Optimized scroll performance to avoid main-thread lag
  }

  // 2. Staggered Scroll Animation Reveals (IntersectionObserver)
  const reveals = document.querySelectorAll('.reveal');
  if (reveals.length > 0) {
    const revealObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry, index) => {
        if (entry.isIntersecting) {
          // Trigger smooth fade-in reveal with standard staggered delay
          setTimeout(() => {
            entry.target.classList.add('in');
          }, index * 60);
          revealObserver.unobserve(entry.target);
        }
      });
    }, { 
      threshold: 0.08, 
      rootMargin: '0px 0px -30px 0px' 
    });

    reveals.forEach(element => revealObserver.observe(element));
  }

  // 3. Smooth Scrolling for Internal Navigation Anchors
  document.querySelectorAll('a[href^="#"], a[href^="/#"]').forEach(anchor => {
    anchor.addEventListener('click', event => {
      let targetSelector = anchor.getAttribute('href');
      if (targetSelector.startsWith('/#')) {
        // Only prevent default and scroll smoothly if we are on the homepage
        if (window.location.pathname === '/' || window.location.pathname === '/index.php' || window.location.pathname === '') {
          targetSelector = targetSelector.substring(1);
        } else {
          return;
        }
      }
      
      if (targetSelector === '#') return;

      const targetElement = document.querySelector(targetSelector);
      if (targetElement) {
        event.preventDefault();
        targetElement.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  // 4. CSRF Token Rotation Helper
  const updateCsrfTokens = (newToken) => {
    if (!newToken) return;
    const csrfInputs = document.querySelectorAll('input[name="csrf_test_name"]');
    csrfInputs.forEach(input => {
      input.value = newToken;
    });
  };

  const getCsrfToken = () => {
    const csrfInput = document.querySelector('input[name="csrf_test_name"]');
    return csrfInput ? csrfInput.value : '';
  };

  // 5. Dynamic Queue and Metrics Rendering
  const renderQueue = (data) => {
    const queueTableBody = document.getElementById('queueTableBody');
    const metricAvgWait = document.getElementById('metricAvgWait');
    const metricBaseline = document.getElementById('metricBaseline');
    const metricCompleted = document.getElementById('metricCompleted');
    const metricInQueue = document.getElementById('metricInQueue');

    if (!data) return;

    // A. Update Metrics with smooth transitions if text changed
    if (metricAvgWait && metricAvgWait.textContent !== String(data.metrics.avg_wait_today)) {
      metricAvgWait.textContent = data.metrics.avg_wait_today;
    }
    if (metricBaseline) {
      const baselineVal = data.metrics.baseline_difference;
      const formattedBaseline = baselineVal > 0 ? `+${baselineVal}` : String(baselineVal);
      if (metricBaseline.textContent !== formattedBaseline) {
        metricBaseline.textContent = formattedBaseline;
      }
    }
    if (metricCompleted && metricCompleted.textContent !== String(data.metrics.completed_today)) {
      metricCompleted.textContent = data.metrics.completed_today;
    }
    if (metricInQueue && metricInQueue.textContent !== String(data.metrics.ambulances_in_queue)) {
      metricInQueue.textContent = data.metrics.ambulances_in_queue;
    }

    // B. Build Table Rows
    if (!queueTableBody) return;

    if (!data.queue || data.queue.length === 0) {
      queueTableBody.innerHTML = `
        <tr>
          <td colspan="7" class="text-center text-muted py-4">No active ambulances in queue. All clear.</td>
        </tr>
      `;
      return;
    }

    const rowsHtml = data.queue.map(handover => {
      // 1. Acuity Class mapping
      let acuityClass = 'stab';
      if (handover.acuity === 'Critical') acuityClass = 'crit';
      else if (handover.acuity === 'Serious') acuityClass = 'seri';

      // 2. ETA representation
      const etaText = handover.status === 'En route' ? `${handover.eta_minutes} min` : 'Arrived';

      // 3. Wait Time Representation
      let waitPillClass = 'wait-green';
      let waitTimeLabel = 'En route';
      const waitTime = parseInt(handover.wait_time_minutes, 10);

      if (handover.status !== 'En route' || waitTime > 0) {
        waitTimeLabel = `${waitTime} min`;
        if (waitTime >= 30) waitPillClass = 'wait-red';
        else if (waitTime >= 15) waitPillClass = 'wait-amber';
      }

      // 4. Action Button Mapping based on status
      let actionLabel = 'Acknowledge';
      let actionName = 'acknowledge';

      if (handover.status === 'Acknowledged' || handover.status === 'Arrived') {
        actionLabel = 'Prepare Bay';
        actionName = 'prepare';
      } else if (handover.status === 'Preparing') {
        actionLabel = 'Clear Bay';
        actionName = 'clear';
      }

      return `
        <tr data-handover-id="${handover.id}">
          <td class="mono-label">${handover.unit_id}</td>
          <td>${handover.provider}</td>
          <td>${handover.patient_gender}, ${handover.patient_age}</td>
          <td>
            <div class="acuity-bar">
              <div class="acuity-dot ${acuityClass}"></div>
              <span>${handover.acuity}</span>
            </div>
          </td>
          <td class="mono-val">${etaText}</td>
          <td>
            <span class="wait-pill ${waitPillClass}">${waitTimeLabel}</span>
          </td>
          <td>
            <button class="action-btn btn btn-outline-secondary" data-id="${handover.id}" data-action="${actionName}">
              ${actionLabel}
            </button>
          </td>
        </tr>
      `;
    }).join('');

    queueTableBody.innerHTML = rowsHtml;
  };

  // 6. Fetch Queue Data API Request
  const fetchQueueData = async () => {
    if (isFetchingQueue) return;
    isFetchingQueue = true;

    try {
      const response = await fetch('/api/queue');
      if (!response.ok) throw new Error('HTTP error while fetching queue');
      
      const data = await response.json();
      if (data.status === 'success') {
        renderQueue(data.result);
        updateCsrfTokens(data.csrf_token);
      }
    } catch (error) {
      console.error('[ClearBay Dashboard] Queue polling failed:', error);
    } finally {
      isFetchingQueue = false;
    }
  };

  // 7. Execute Queue Status Action API Request
  const executeQueueAction = async (handoverId, actionName, button) => {
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Processing...';

    const row = button.closest('tr');
    
    // If clearing, kick off the fade-out micro-animation early for visual responsiveness
    if (actionName === 'clear' && row) {
      row.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
      row.style.opacity = '0';
      row.style.transform = 'translateX(24px)';
    }

    try {
      const csrfToken = getCsrfToken();
      const formData = new URLSearchParams();
      formData.append('handoverId', handoverId);
      formData.append('actionName', actionName);
      formData.append('csrf_test_name', csrfToken);

      const response = await fetch('/api/queue/action', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-CSRF-TOKEN': csrfToken
        },
        body: formData.toString()
      });

      if (!response.ok) throw new Error('HTTP error executing queue action');

      const data = await response.json();
      
      if (data.status === 'success') {
        updateCsrfTokens(data.csrf_token);
        
        // Wait for clear transition to finish before complete table redraw
        if (actionName === 'clear') {
          setTimeout(() => {
            renderQueue(data.result);
          }, 400);
        } else {
          renderQueue(data.result);
        }
      } else {
        // Restore row style if API returned failure
        if (actionName === 'clear' && row) {
          row.style.opacity = '1';
          row.style.transform = 'none';
        }
        button.disabled = false;
        button.textContent = originalText;
        alert(data.message || 'Operation failed. Please try again.');
      }
    } catch (error) {
      console.error('[ClearBay Dashboard] Action failed:', error);
      if (actionName === 'clear' && row) {
        row.style.opacity = '1';
        row.style.transform = 'none';
      }
      button.disabled = false;
      button.textContent = originalText;
      alert('A network error occurred. Please check your connection.');
    }
  };

  // 8. Event Delegation for Action Buttons
  const queueTableBody = document.getElementById('queueTableBody');
  if (queueTableBody) {
    queueTableBody.addEventListener('click', event => {
      const button = event.target.closest('.action-btn');
      if (!button) return;

      const handoverId = button.dataset.id;
      const actionName = button.dataset.action;

      if (handoverId && actionName) {
        executeQueueAction(handoverId, actionName, button);
      }
    });
  }

  // 9. Initial Load and Live Dashboard Polling (10s intervals)
  fetchQueueData();
  setInterval(fetchQueueData, 10000);

  // 10. Pilot Onboarding Request Form Submission Handler
  const signupForm = document.getElementById('signupForm');
  const successCard = document.getElementById('successCard');
  const formFeedback = document.getElementById('formFeedback');
  const submitBtn = document.getElementById('submitBtn');
  const submitSpinner = document.getElementById('submitSpinner');
  const submitText = document.getElementById('submitText');

  if (signupForm && successCard) {
    signupForm.addEventListener('submit', async event => {
      event.preventDefault();
      if (isSubmittingSignup) return;

      isSubmittingSignup = true;
      
      // Reset validation states
      formFeedback.classList.add('d-none');
      formFeedback.textContent = '';
      const inputs = signupForm.querySelectorAll('.form-control, .form-select');
      inputs.forEach(input => {
        input.classList.remove('is-invalid');
      });

      // Show spinner & disable button
      submitBtn.disabled = true;
      submitSpinner.classList.remove('d-none');
      submitText.textContent = 'Submitting Request...';

      try {
        const formData = new FormData(signupForm);
        const response = await fetch('/pilot/signup', {
          method: 'POST',
          body: formData
        });

        if (!response.ok) throw new Error('HTTP error during signup submission');

        const data = await response.json();
        updateCsrfTokens(data.csrf_token);

        if (data.status === 'success') {
          // Hide form, show success
          signupForm.style.display = 'none';
          successCard.style.display = 'block';

          // Scroll form container to view for instant feedback
          const formContainer = document.getElementById('formContainer');
          if (formContainer) {
            formContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
          }
        } else {
          // Handle Validation / Backend Errors
          if (data.errors) {
            Object.keys(data.errors).forEach(fieldName => {
              const inputField = document.getElementById(fieldName);
              const errorDiv = document.getElementById(`error_${fieldName}`);
              if (inputField) {
                inputField.classList.add('is-invalid');
              }
              if (errorDiv) {
                errorDiv.textContent = data.errors[fieldName];
              }
            });
          }
          
          formFeedback.textContent = data.message || 'Validation failed. Please correct the fields above.';
          formFeedback.classList.remove('d-none');
        }
      } catch (error) {
        console.error('[ClearBay Onboarding] Signup failed:', error);
        formFeedback.textContent = 'A network error occurred. Please try again.';
        formFeedback.classList.remove('d-none');
      } finally {
        isSubmittingSignup = false;
        submitBtn.disabled = false;
        submitSpinner.classList.add('d-none');
        submitText.textContent = 'Submit Request →';
      }
    });
  }
})();
