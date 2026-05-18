/**
 * ClearBay — Real-Time Ambulance Off-Load Management | Nairobi, Kenya
 * Core Client Interaction and Behavior (Decoupled & Encapsulated)
 */
(() => {
  'use strict';

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
  document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', event => {
      const targetSelector = anchor.getAttribute('href');
      if (targetSelector === '#') return;

      const targetElement = document.querySelector(targetSelector);
      if (targetElement) {
        event.preventDefault();
        targetElement.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });

  // 4. Pilot Onboarding Request Form Submission Handler
  const signupForm = document.getElementById('signupForm');
  const successCard = document.getElementById('successCard');
  if (signupForm && successCard) {
    signupForm.addEventListener('submit', event => {
      event.preventDefault();
      
      // Structural transitions
      signupForm.style.display = 'none';
      successCard.style.display = 'block';

      // Scroll form container to view for instant feedback
      const formContainer = document.getElementById('formContainer');
      if (formContainer) {
        formContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
      }
    });
  }
})();
