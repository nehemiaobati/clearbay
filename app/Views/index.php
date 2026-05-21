<?php
/**
 * @var string $pageTitle
 * @var string $metaDescription
 * @var string $canonicalUrl
 * @var string $robotsTag
 * @var string $metaImage
 */
?>
<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

  <!-- ━━━━ HERO ━━━━ -->
  <section class="hero">
    <div class="hero-bg-text" aria-hidden="true">CLEAR</div>

    <div class="container my-5">
      <div class="hero-eyebrow">
        <div class="eyebrow-line"></div>
        <span class="eyebrow-text">Nairobi, Kenya &nbsp;·&nbsp; Emergency Health Technology &nbsp;·&nbsp; Est. 2026</span>
      </div>

      <h1 class="hero-headline">
        Clear the<br>
        <span class="italic">Bay.</span>
        <span class="stroke"> Free</span><br>
        the Crew.
      </h1>

      <div class="hero-bottom">
        <p class="hero-sub">
          Kenya's first <strong>real-time ambulance off-load management platform</strong> — giving hospital emergency departments and ambulance services the live visibility they need to hand over patients faster, reduce dangerous delays, and return crews to the community where they are needed most.
        </p>
        <div class="hero-cta-group">
          <a href="#signup" class="btn-main btn btn-primary">
            Request Pilot Access <span class="arrow">→</span>
          </a>
          <a href="#how" class="btn-ghost btn btn-outline-secondary">
            See How It Works ↓
          </a>
        </div>
      </div>
    </div>

    <div class="scroll-hint" aria-hidden="true">
      <span>Scroll</span>
      <div class="scroll-line"></div>
    </div>
  </section>

  <!-- ━━━━ TICKER ━━━━ -->
  <div class="ticker-wrap" aria-hidden="true">
    <div class="ticker-track" id="ticker">
      <div class="ticker-item"><div class="ticker-dot"></div>Ambulances detained 30–90 min per handover</div>
      <div class="ticker-item"><div class="ticker-dot"></div>Mbagathi Hospital — 200% daily bed occupancy</div>
      <div class="ticker-item"><div class="ticker-dot"></div>KNH receives 3,000 patients against 1,800-bed capacity</div>
      <div class="ticker-item"><div class="ticker-dot"></div>Mama Lucy serves 2.25 million people alone</div>
      <div class="ticker-item"><div class="ticker-dot"></div>No real-time ED capacity visibility for ambulance crews</div>
      <div class="ticker-item"><div class="ticker-dot"></div>4.4 million Nairobians without adequate emergency cover</div>
      <div class="ticker-item"><div class="ticker-dot"></div>Cardiac arrest outcomes worsen every minute of delay</div>
      <!-- duplicate for seamless loop -->
      <div class="ticker-item"><div class="ticker-dot"></div>Ambulances detained 30–90 min per handover</div>
      <div class="ticker-item"><div class="ticker-dot"></div>Mbagathi Hospital — 200% daily bed occupancy</div>
      <div class="ticker-item"><div class="ticker-dot"></div>KNH receives 3,000 patients against 1,800-bed capacity</div>
      <div class="ticker-item"><div class="ticker-dot"></div>Mama Lucy serves 2.25 million people alone</div>
      <div class="ticker-item"><div class="ticker-dot"></div>No real-time ED capacity visibility for ambulance crews</div>
      <div class="ticker-item"><div class="ticker-dot"></div>4.4 million Nairobians without adequate emergency cover</div>
      <div class="ticker-item"><div class="ticker-dot"></div>Cardiac arrest outcomes worsen every minute of delay</div>
    </div>
  </div>

  <!-- ━━━━ PROBLEM ━━━━ -->
  <section class="section problem" id="problem">
    <div class="container my-5">
      <div class="blueprint-header reveal">
        <div class="s-label">
          <div class="s-label-line"></div>
          <span class="s-label-text">01 — The Problem</span>
        </div>
      </div>

      <div class="problem-grid">
        <div class="stat-monument card blueprint-card reveal">
          <div class="monument-num">90<span class="monument-unit">min</span></div>
          <div class="monument-label">The average time an ambulance can spend waiting to hand over a patient at a Nairobi emergency department — time the crew cannot spend responding to the next emergency in the community.</div>
          <br>
          <div class="monument-num" style="font-size: clamp(2.5rem, 5vw, 4.5rem);">200<span class="monument-unit">%</span></div>
          <div class="monument-label">Mbagathi County Hospital's reported daily bed occupancy — running at double capacity every single day. Two patients for every bed. No space for incoming ambulances.</div>
        </div>

        <div class="problem-list reveal">
          <div class="problem-item">
            <span class="problem-index">01</span>
            <div>
              <h4>Ambulances Stuck at Hospitals</h4>
              <p>When EDs are overwhelmed, ambulance crews have nowhere to hand over their patient. They wait — sometimes for an hour or more — while the community behind them has one fewer emergency vehicle available.</p>
            </div>
          </div>
          <div class="problem-item">
            <span class="problem-index">02</span>
            <div>
              <h4>No Advance Warning for EDs</h4>
              <p>Hospital emergency departments receive ambulances with zero notice. There is no system to prepare a bay, alert the right staff, or route incoming patients intelligently. Every arrival is a surprise.</p>
            </div>
          </div>
          <div class="problem-item">
            <span class="problem-index">03</span>
            <div>
              <h4>Patients Deteriorate in the Bay</h4>
              <p>Cardiac arrest, stroke, sepsis, and major trauma are ruthlessly time-sensitive. Every minute a patient waits in the ambulance environment — with limited equipment and no definitive care — their survival odds fall.</p>
            </div>
          </div>
          <div class="problem-item">
            <span class="problem-index">04</span>
            <div>
              <h4>Coverage Gaps Across the City</h4>
              <p>One ambulance delayed at a hospital for 60 minutes creates a coverage void across its entire catchment area. The next emergency call goes unanswered — or takes dangerous extra minutes to reach.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ━━━━ HOW IT WORKS ━━━━ -->
  <section class="section how section--light" id="how">
    <div class="container my-5">
      <div class="blueprint-header reveal">
        <div class="s-label">
          <div class="s-label-line"></div>
          <span class="s-label-text">02 — The Solution</span>
        </div>
        <h2 class="s-title">Three Steps.<br><span class="ital">One Cleared Bay.</span></h2>
        <p class="s-body" style="margin-top: 1rem; max-width: 540px;">ClearBay connects ambulances and hospital EDs before the vehicle even arrives — so every handover is prepared, every bay is ready, and every crew is back on the road in minutes.</p>
      </div>

      <div class="steps-flow">
        <div class="step-block card blueprint-card reveal">
          <div class="step-counter"><span class="step-counter-num">01</span></div>
          <h3>Hospital Updates<br>Live Status</h3>
          <p>Emergency department staff update their real-time capacity — green, amber, or red — with a single tap. Every ambulance crew in Nairobi sees it instantly on their mobile app.</p>
          <span class="step-tag">Hospital Dashboard</span>
        </div>

        <div class="step-block card blueprint-card reveal">
          <div class="step-counter"><span class="step-counter-num">02</span></div>
          <h3>Paramedic Sends<br>Pre-Notification</h3>
          <p>Before leaving the scene, the crew selects the best available hospital and sends a pre-alert — patient condition, acuity, and GPS-calculated ETA — giving the ED time to prepare.</p>
          <span class="step-tag">Paramedic App</span>
        </div>

        <div class="step-block card blueprint-card reveal">
          <div class="step-counter"><span class="step-counter-num">03</span></div>
          <h3>Bay Cleared.<br>Crew Free.</h3>
          <p>Handover happens in minutes, not hours. One tap marks the bay clear. The ambulance is instantly visible as available on the dispatcher map — ready for the next call.</p>
          <span class="step-tag">Dispatch Command Centre</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ━━━━ WHO WE SERVE ━━━━ -->
  <section class="section serve" id="serve">
    <div class="container my-5">
      <div class="blueprint-header reveal">
        <div class="s-label">
          <div class="s-label-line"></div>
          <span class="s-label-text">03 — Who We Serve</span>
        </div>
        <h2 class="s-title">Built for Every<br><span class="ital">Link in the Chain.</span></h2>
      </div>

      <div class="serve-grid reveal">
        <div class="serve-cell card blueprint-card">
          <span class="serve-num">01 / 03</span>
          <h3>Hospital Emergency Departments</h3>
          <p>Real-time visibility of every incoming ambulance. Prepare bays, alert the right clinical staff, and close the communication gap before the doors open.</p>
          <ul class="serve-features">
            <li>Live ambulance queue dashboard</li>
            <li>Pre-arrival patient condition alerts</li>
            <li>Automatic delay flags (&gt;30 minutes)</li>
            <li>Weekly off-load performance reports</li>
            <li>County health reporting compliance</li>
          </ul>
        </div>

        <div class="serve-cell card blueprint-card">
          <span class="serve-num">02 / 03</span>
          <h3>Paramedics &amp; Ambulance Crews</h3>
          <p>Know which hospitals have capacity before you arrive. Send one-tap pre-notifications. Spend minutes at the bay, not hours — and get back to the community.</p>
          <ul class="serve-features">
            <li>Live hospital capacity map</li>
            <li>GPS-powered ETA calculation</li>
            <li>One-tap pre-notification send</li>
            <li>Works on any Android phone</li>
            <li>Offline-capable for low-signal areas</li>
          </ul>
        </div>

        <div class="serve-cell card blueprint-card">
          <span class="serve-num">03 / 03</span>
          <h3>EMS Dispatchers &amp; Operations</h3>
          <p>Command-centre visibility across every active ambulance in Nairobi. Spot stuck crews instantly. Make smarter deployment decisions with real data.</p>
          <ul class="serve-features">
            <li>Fleet-wide live map view</li>
            <li>Ambulance status tracking</li>
            <li>Automatic stuck-crew alerts</li>
            <li>Daily and weekly analytics</li>
            <li>CAD system integration ready</li>
          </ul>
        </div>
      </div>
    </div>
  </section>

  <!-- ━━━━ EVIDENCE ━━━━ -->
  <section class="section evidence" id="evidence">
    <div class="container my-5">
      <div class="evidence-grid">
        <div>
          <div class="blueprint-header reveal">
            <div class="s-label">
              <div class="s-label-line"></div>
              <span class="s-label-text">04 — Research Foundation</span>
            </div>
            <h2 class="s-title">Not an Idea.<br><span class="ital">A Proven Problem.</span></h2>
          </div>
          <p class="s-body reveal" style="margin-top: 1.2rem; max-width: 460px;">ClearBay was not built on assumption. It was built on three chapters of peer-reviewed academic research on ambulance wait times in Kenya — with evidence drawn from 245 participants and international literature spanning four continents.</p>
          <p class="s-body reveal" style="margin-top: 1rem; max-width: 460px;">No other health technology company in Kenya is launching with this depth of documented, structured evidence. When we walk into a hospital, we carry proof — not a pitch deck.</p>
        </div>

        <div class="doc-block card blueprint-card reveal">
          <p class="doc-title">"How Do Long Ambulance Wait Times at the Hospital Affect Patient Care and Community Emergency Response?"</p>

          <div class="chapter-rows">
            <div class="chapter-row">
              <span class="ch-num">CH.1</span>
              <span class="ch-text"><strong>Introduction</strong> — Background, problem statement, 5 research questions, objectives, and 3 testable hypotheses grounded in Nairobi County.</span>
            </div>
            <div class="chapter-row">
              <span class="ch-num">CH.2</span>
              <span class="ch-text"><strong>Literature Review</strong> — Systems Theory, Queuing Theory, Chain of Survival. Global evidence from Australia, UK, Canada, USA, and Africa.</span>
            </div>
            <div class="chapter-row">
              <span class="ch-num">CH.3</span>
              <span class="ch-text"><strong>Methodology</strong> — Convergent parallel mixed-methods design. 245-participant study across 6 stakeholder groups in Nairobi County.</span>
            </div>
          </div>

          <div class="doc-stats">
            <div class="doc-stat">
              <span class="val">245</span>
              <span class="key">Participants</span>
            </div>
            <div class="doc-stat">
              <span class="val">6</span>
              <span class="key">Stakeholder groups</span>
            </div>
            <div class="doc-stat">
              <span class="val">3</span>
              <span class="key">Research chapters</span>
            </div>
            <div class="doc-stat">
              <span class="val">5</span>
              <span class="key">Research questions</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ━━━━ HOSPITALS ━━━━ -->
  <section class="section hospitals section--light bg-cream-2" id="hospitals">
    <div class="container my-5">
      <div class="blueprint-header reveal">
        <div class="s-label">
          <div class="s-label-line"></div>
          <span class="s-label-text">05 — Pilot Programme</span>
        </div>
        <h2 class="s-title">Launching Where<br><span class="ital">It Matters Most.</span></h2>
        <p class="s-body" style="margin-top: 1rem; max-width: 540px;">We are actively recruiting pilot hospital and EMS partners across Nairobi County — the facilities serving millions of Kenyans who depend on public and private emergency care every day.</p>
      </div>

      <div class="hospital-grid reveal">
        <div class="hospital-tile card blueprint-card">
          <span class="h-code">KNH · Level 6</span>
          <span class="h-name">Kenyatta National Hospital</span>
          <span class="h-cat">National Referral · Public</span>
          <span class="h-status">Recruiting</span>
        </div>
        <div class="hospital-tile card blueprint-card">
          <span class="h-code">MLK · Level 5</span>
          <span class="h-name">Mama Lucy Kibaki Hospital</span>
          <span class="h-cat">County Referral · Public</span>
          <span class="h-status">Recruiting</span>
        </div>
        <div class="hospital-tile card blueprint-card">
          <span class="h-code">MBG · Level 5</span>
          <span class="h-name">Mbagathi County Hospital</span>
          <span class="h-cat">County Referral · Public</span>
          <span class="h-status">Recruiting</span>
        </div>
        <div class="hospital-tile card blueprint-card">
          <span class="h-code">AKU · Private</span>
          <span class="h-name">Aga Khan University Hospital</span>
          <span class="h-cat">Teaching Hospital · Private</span>
          <span class="h-status">Recruiting</span>
        </div>
        <div class="hospital-tile card blueprint-card">
          <span class="h-code">NBO · Private</span>
          <span class="h-name">Nairobi Hospital</span>
          <span class="h-cat">Referral Hospital · Private</span>
          <span class="h-status">Recruiting</span>
        </div>
      </div>

      <div class="ems-strip reveal">
        <span class="ems-label">EMS Partners:</span>
        <span class="ems-tag">Kenya Red Cross EMS</span>
        <span class="ems-tag">AAR Healthcare</span>
        <span class="ems-tag">County Ambulance Services</span>
        <span class="ems-tag">+ Open to all providers</span>
      </div>
    </div>
  </section>

  <!-- ━━━━ LIVE PREVIEW ━━━━ -->
  <section class="section preview" id="preview">
    <div class="container my-5">
      <div class="blueprint-header reveal">
        <div class="s-label">
          <div class="s-label-line"></div>
          <span class="s-label-text">06 — Platform Preview</span>
        </div>
        <h2 class="s-title">What the Hospital<br><span class="ital">Dashboard Looks Like.</span></h2>
      </div>

      <div class="preview-frame card blueprint-card reveal">
        <div class="dash-header">
          <span class="dash-title">Ambulance Queue — Emergency Department</span>
          <div class="dash-live"><div class="live-dot"></div> Live · 14:32 EAT</div>
        </div>

        <div class="table-responsive">
          <table class="queue-table">
            <thead>
              <tr>
                <th>Unit ID</th>
                <th>Provider</th>
                <th>Patient</th>
                <th>Acuity</th>
                <th>ETA</th>
                <th>Wait Time</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="queueTableBody">
              <!-- Dynamically populated via AJAX on page load -->
              <tr>
                <td colspan="7" class="text-center text-muted py-4">Loading live ambulance queue...</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="dash-metrics">
          <div class="metric-box">
            <div class="metric-val" id="metricAvgWait">38</div>
            <div class="metric-key">Avg wait today (min)</div>
          </div>
          <div class="metric-box">
            <div class="metric-val" id="metricBaseline">-22</div>
            <div class="metric-key">vs. pre-ClearBay baseline</div>
          </div>
          <div class="metric-box">
            <div class="metric-val" id="metricCompleted">14</div>
            <div class="metric-key">Handovers completed today</div>
          </div>
          <div class="metric-box">
            <div class="metric-val" id="metricInQueue">4</div>
            <div class="metric-key">Ambulances in queue</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ━━━━ SIGNUP ━━━━ -->
  <section class="section signup section--light" id="signup">
    <div class="container my-5">
      <div class="blueprint-header reveal">
        <div class="s-label">
          <div class="s-label-line"></div>
          <span class="s-label-text">07 — Join the Pilot</span>
        </div>
        <h2 class="s-title">Be Part of the<br><span class="ital dim">First Pilot.</span></h2>
      </div>

      <div class="form-wrap">
        <div id="formContainer" class="card blueprint-card p-4 p-md-5 reveal">
          <form class="form-block" id="signupForm" novalidate>
            <?= csrf_field() ?>
            <div id="formFeedback" class="alert alert-danger d-none mb-3" role="alert"></div>
            
            <div class="form-row">
              <div class="field-wrap form-floating mb-3">
                <input type="text" id="fullName" name="fullName" class="form-control field-input" placeholder="Dr. Wanjiru Kamau" required>
                <label class="field-label" for="fullName">Full Name *</label>
                <div class="invalid-feedback" id="error_fullName">Please enter a valid full name (minimum 3 characters).</div>
              </div>
              <div class="field-wrap form-floating mb-3">
                <input type="email" id="emailAddress" name="emailAddress" class="form-control field-input" placeholder="you@hospital.ke" required>
                <label class="field-label" for="emailAddress">Email Address *</label>
                <div class="invalid-feedback" id="error_emailAddress">Please enter a valid email address.</div>
              </div>
            </div>

            <div class="field-wrap form-floating mb-3">
              <input type="text" id="organisation" name="organisation" class="form-control field-input" placeholder="e.g. Kenyatta National Hospital" required>
              <label class="field-label" for="organisation">Organisation / Hospital / EMS Service *</label>
              <div class="invalid-feedback" id="error_organisation">Please specify your organisation (minimum 3 characters).</div>
            </div>

            <div class="form-row">
              <div class="field-wrap form-floating mb-3">
                <select id="userRole" name="userRole" class="form-select field-select" required style="padding-top: 1.625rem; padding-bottom: 0.625rem;">
                  <option value="" disabled selected>Select your role</option>
                  <option value="Hospital Administrator">Hospital Administrator</option>
                  <option value="ED Manager / Charge Nurse">ED Manager / Charge Nurse</option>
                  <option value="Emergency Physician">Emergency Physician</option>
                  <option value="Paramedic / EMT">Paramedic / EMT</option>
                  <option value="EMS Dispatcher / Operations Manager">EMS Dispatcher / Operations Manager</option>
                  <option value="Investor / Funder">Investor / Funder</option>
                  <option value="Researcher / Academic">Researcher / Academic</option>
                  <option value="Other">Other</option>
                </select>
                <label class="field-label" for="userRole">Your Role *</label>
                <div class="invalid-feedback" id="error_userRole">Please select a valid role.</div>
              </div>
              <div class="field-wrap form-floating mb-3">
                <input type="tel" id="phoneNumber" name="phoneNumber" class="form-control field-input" placeholder="+254 7XX XXX XXX">
                <label class="field-label" for="phoneNumber">Phone (optional)</label>
                <div class="invalid-feedback" id="error_phoneNumber">Please enter a valid phone number.</div>
              </div>
            </div>

            <div class="field-wrap form-floating mb-3">
              <input type="text" id="message" name="message" class="form-control field-input" placeholder="Tell us a bit about your interest in ClearBay">
              <label class="field-label" for="message">Message (optional)</label>
              <div class="invalid-feedback" id="error_message">Message content cannot exceed 2000 characters.</div>
            </div>

            <button type="submit" id="submitBtn" class="submit-btn btn btn-primary">
              <span id="submitSpinner" class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
              <span id="submitText">Submit Request →</span>
            </button>
            <p class="form-note">The pilot is free. We will respond within 48 hours. Your information is held in strict confidence and is never shared with third parties.</p>
          </form>

          <div class="success-card" id="successCard">
            <h3>You're In.</h3>
            <p>Thank you for your interest in the ClearBay pilot. Our team will contact you within 48 hours to discuss next steps. You are part of something that matters.</p>
          </div>
        </div>

        <div class="signup-aside">
          <div class="aside-block reveal">
            <h4>Pilot is completely free</h4>
            <p>The 12-week pilot programme costs you nothing. We provide the platform, the training, the weekly reports, and the data analysis. You give us your time and your honest feedback.</p>
          </div>
          <div class="aside-block reveal">
            <h4>Results in 12 weeks</h4>
            <p>At the end of the pilot, we produce a case study showing the before-and-after impact of ClearBay on your off-load times, ambulance throughput, and ED staff experience.</p>
          </div>
          <div class="aside-block reveal">
            <h4>Evidence you can use</h4>
            <p>Your pilot data belongs to you. The case study can support your annual reports, county government submissions, and quality improvement documentation.</p>
          </div>
          <div class="aside-block reveal">
            <h4>Built on research</h4>
            <p>ClearBay is grounded in three chapters of peer-reviewed academic research on ambulance wait times in Kenya. We know the problem. We built the solution.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

<?= $this->endSection() ?>
