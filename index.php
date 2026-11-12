<?php
// Start session for CSRF security token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Indian States & Union Territories
$indianStates = [
    "Andhra Pradesh", "Arunachal Pradesh", "Assam", "Bihar", "Chhattisgarh",
    "Goa", "Gujarat", "Haryana", "Himachal Pradesh", "Jharkhand",
    "Karnataka", "Kerala", "Madhya Pradesh", "Maharashtra", "Manipur",
    "Meghalaya", "Mizoram", "Nagaland", "Odisha", "Punjab",
    "Rajasthan", "Sikkim", "Tamil Nadu", "Telangana", "Tripura",
    "Uttar Pradesh", "Uttarakhand", "West Bengal",
    "Andaman and Nicobar Islands", "Chandigarh", "Dadra and Nagar Haveli and Daman and Diu",
    "Delhi (NCT)", "Jammu and Kashmir", "Ladakh", "Lakshadweep", "Puducherry"
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All India Higher Education &amp; Admission Portal 2026</title>
  
  <!-- Google Fonts & Font Awesome Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Custom Indian Theme Stylesheet -->
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <div class="app-container">

    <!-- Top Official Indian Header -->
    <header class="app-header">
      <div class="brand-badge">
        <i class="fas fa-university"></i> Ministry of Higher Education &bull; Academic Year 2026–2027
      </div>
      <h1 class="brand-title">National Institute of Technology &amp; Advanced Studies</h1>
      <div class="brand-subtitle-hindi">राष्ट्रीय प्रौद्योगिकी एवं उन्नत अध्ययन संस्थान</div>
      <p class="brand-subtitle">
        Centralized Candidate Registration &amp; Admission Portal for Technical &amp; Professional Degree Programs across India.
      </p>
      
      <nav class="nav-links">
        <a href="index.php" class="active"><i class="fas fa-file-signature"></i> Application Form</a>
        <a href="records.php"><i class="fas fa-address-book"></i> Registered Candidates Directory</a>
      </nav>
    </header>

    <!-- Global Alert Box -->
    <div id="alertBox" class="alert" style="display: none;"></div>

    <!-- 2-Column Split Layout Wrapper -->
    <div id="formContainer" class="form-layout-wrapper">

      <!-- LEFT COLUMN: Candidate Photo Upload & Live Summary Panel -->
      <aside class="sidebar-panel">
        
        <!-- Photo Upload Box -->
        <div class="sidebar-card">
          <div class="sidebar-card-title">
            <i class="fas fa-camera"></i> Passport Photograph
          </div>
          
          <div class="photo-preview-large">
            <i class="fas fa-user" id="photoPlaceholderIcon"></i>
            <span id="photoPlaceholderText">No Photo Selected</span>
            <img id="previewImg" alt="Candidate Photo Preview">
          </div>

          <div class="file-input-wrapper">
            <label for="photoInput" class="btn-upload-photo">
              <i class="fas fa-cloud-upload-alt"></i> Choose Photograph
            </label>
            <input type="file" id="photoInput" name="photo" accept="image/jpeg,image/png,image/webp">
          </div>
          <p class="photo-hint">Indian Passport Format (3.5 × 4.5 cm). JPG, PNG, or WebP. Max 3MB.</p>
          <div id="photoError" class="error-message" style="text-align: center;"></div>
        </div>

        <!-- Live Candidate Preview Card -->
        <div class="live-summary-card">
          <h4><i class="fas fa-id-badge"></i> Candidate Live Preview</h4>
          
          <div class="live-item">
            <span class="label">Candidate Name</span>
            <span class="value" id="liveName">Aarav Sharma</span>
          </div>

          <div class="live-item">
            <span class="label">Email ID</span>
            <span class="value" id="liveEmail">aarav.sharma@example.in</span>
          </div>

          <div class="live-item">
            <span class="label">Mobile Number</span>
            <span class="value" id="livePhone">+91 98765 43210</span>
          </div>

          <div class="live-item">
            <span class="label">Selected Program</span>
            <span class="value" id="liveCourse">B.Tech in Computer Science &amp; Engineering</span>
          </div>

          <div class="live-item">
            <span class="label">Location &amp; PIN</span>
            <span class="value" id="liveLocation">Bengaluru, Karnataka - 560001</span>
          </div>
        </div>

        <!-- Admissions Helpdesk Box -->
        <div class="info-help-box">
          <h5><i class="fas fa-headset" style="color: var(--accent);"></i> Central Admission Helpdesk</h5>
          <p>Toll-Free Helpline: <strong>1800-266-9900</strong></p>
          <p>Direct: <strong>+91 (080) 2345 6789</strong></p>
          <p>Email: <a href="mailto:admissions@nitas.edu.in" style="color: var(--primary); font-weight: 600;">admissions@nitas.edu.in</a></p>
        </div>

      </aside>

      <!-- RIGHT COLUMN: Registration Form -->
      <main class="form-card">
        
        <div class="form-header-bar">
          <h2><i class="fas fa-clipboard-list"></i> Candidate Registration Application</h2>
          <span class="step-indicator"><i class="fas fa-flag"></i> Government Recognized</span>
        </div>

        <div class="form-body">
          <form id="registrationForm" method="POST" action="process.php" enctype="multipart/form-data" novalidate>
            
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <!-- SECTION 1: Personal Details -->
            <div class="form-section">
              <h3 class="section-title"><i class="fas fa-user-tie"></i> 1. Candidate Personal Details</h3>

              <div class="grid-2">
                <!-- Full Name -->
                <div class="form-group span-2">
                  <label for="fullName">Full Name (As per 10th / Matriculation Certificate) <span class="required">*</span></label>
                  <div class="input-wrapper">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" id="fullName" name="fullName" class="form-control" placeholder="e.g. Aarav Sharma" value="Aarav Sharma" required>
                  </div>
                  <div id="fullNameError" class="error-message"></div>
                </div>

                <!-- Date of Birth -->
                <div class="form-group">
                  <label for="dob">Date of Birth <span class="required">*</span></label>
                  <div class="input-wrapper">
                    <i class="fas fa-calendar-alt input-icon"></i>
                    <input type="date" id="dob" name="dob" class="form-control" max="<?php echo date('Y-m-d', strtotime('-14 years')); ?>" value="2002-05-18" required>
                  </div>
                  <div id="dobError" class="error-message"></div>
                </div>

                <!-- Gender -->
                <div class="form-group">
                  <label>Gender <span class="required">*</span></label>
                  <div class="radio-group">
                    <label class="radio-label active">
                      <input type="radio" name="gender" value="Male" checked> Male
                    </label>
                    <label class="radio-label">
                      <input type="radio" name="gender" value="Female"> Female
                    </label>
                    <label class="radio-label">
                      <input type="radio" name="gender" value="Third Gender"> Third Gender
                    </label>
                  </div>
                  <div id="genderError" class="error-message"></div>
                </div>

                <!-- Social Category -->
                <div class="form-group span-2">
                  <label for="category">Social Category / Reservation Class</label>
                  <div class="input-wrapper">
                    <i class="fas fa-users-cog input-icon"></i>
                    <select id="category" name="category" class="form-control">
                      <option value="General (GEN)" selected>General (GEN / Open)</option>
                      <option value="Other Backward Class (OBC-NCL)">Other Backward Class (OBC-NCL)</option>
                      <option value="Scheduled Caste (SC)">Scheduled Caste (SC)</option>
                      <option value="Scheduled Tribe (ST)">Scheduled Tribe (ST)</option>
                      <option value="Economically Weaker Section (EWS)">Economically Weaker Section (EWS)</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <!-- SECTION 2: Contact Details (Indian Mobile & Email) -->
            <div class="form-section">
              <h3 class="section-title"><i class="fas fa-address-book"></i> 2. Contact &amp; Communication Details</h3>

              <div class="grid-2">
                <!-- Email ID -->
                <div class="form-group">
                  <label for="email">Active Email Address <span class="required">*</span></label>
                  <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email" class="form-control" placeholder="aarav.sharma@example.in" value="aarav.sharma@example.in" required>
                  </div>
                  <div id="emailError" class="error-message"></div>
                </div>

                <!-- Indian Mobile Number (+91 prefix) -->
                <div class="form-group">
                  <label for="phone">Indian Mobile Number (10 Digits) <span class="required">*</span></label>
                  <div class="input-wrapper">
                    <i class="fas fa-phone-alt input-icon"></i>
                    <span class="input-prefix">+91</span>
                    <input type="tel" id="phone" name="phone" class="form-control has-prefix" placeholder="98765 43210" maxlength="14" value="98765 43210" required>
                  </div>
                  <div id="phoneError" class="error-message"></div>
                </div>

                <!-- Alternate Contact Number -->
                <div class="form-group span-2">
                  <label for="altPhone">Alternate / Parent's Mobile Number (Optional)</label>
                  <div class="input-wrapper">
                    <i class="fas fa-mobile-alt input-icon"></i>
                    <span class="input-prefix">+91</span>
                    <input type="tel" id="altPhone" name="altPhone" class="form-control has-prefix" placeholder="98123 45678" maxlength="14">
                  </div>
                </div>
              </div>
            </div>

            <!-- SECTION 3: Permanent Address (Indian Location & PIN Code) -->
            <div class="form-section">
              <h3 class="section-title"><i class="fas fa-map-marked-alt"></i> 3. Permanent Residential Address (India)</h3>

              <div class="grid-2">
                <!-- Street Address / House No -->
                <div class="form-group span-2">
                  <label for="address">Flat / House No., Building Name, Street / Locality <span class="required">*</span></label>
                  <div class="input-wrapper">
                    <i class="fas fa-home input-icon"></i>
                    <input type="text" id="address" name="address" class="form-control" placeholder="e.g. Flat 402, Shanti Heights, 12th Main Road, Indiranagar" value="Flat 402, Shanti Heights, 12th Main Road, Indiranagar" required>
                  </div>
                  <div id="addressError" class="error-message"></div>
                </div>

                <!-- City / District -->
                <div class="form-group">
                  <label for="city">City / District <span class="required">*</span></label>
                  <div class="input-wrapper">
                    <i class="fas fa-city input-icon"></i>
                    <input type="text" id="city" name="city" class="form-control" placeholder="e.g. Bengaluru" value="Bengaluru" required>
                  </div>
                  <div id="cityError" class="error-message"></div>
                </div>

                <!-- State / Union Territory Dropdown -->
                <div class="form-group">
                  <label for="state">State / Union Territory <span class="required">*</span></label>
                  <div class="input-wrapper">
                    <i class="fas fa-landmark input-icon"></i>
                    <select id="state" name="state" class="form-control" required>
                      <option value="" disabled>-- Select State / UT --</option>
                      <?php foreach ($indianStates as $st): ?>
                        <option value="<?php echo htmlspecialchars($st); ?>" <?php echo ($st === 'Karnataka') ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($st); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div id="stateError" class="error-message"></div>
                </div>

                <!-- 6-Digit Indian PIN Code -->
                <div class="form-group">
                  <label for="zipCode">Postal PIN Code (6 Digits) <span class="required">*</span></label>
                  <div class="input-wrapper">
                    <i class="fas fa-mail-bulk input-icon"></i>
                    <input type="text" id="zipCode" name="zipCode" class="form-control" placeholder="e.g. 560001" maxlength="6" pattern="^[1-9][0-9]{5}$" value="560001" required>
                  </div>
                  <div id="zipCodeError" class="error-message"></div>
                </div>

                <!-- Country -->
                <div class="form-group">
                  <label for="country">Country</label>
                  <div class="input-wrapper">
                    <i class="fas fa-globe-asia input-icon"></i>
                    <input type="text" id="country" name="country" class="form-control" value="India" readonly style="background: #f1f5f9; font-weight: 600;">
                  </div>
                </div>
              </div>
            </div>

            <!-- SECTION 4: Academic Program Selection -->
            <div class="form-section">
              <h3 class="section-title"><i class="fas fa-graduation-cap"></i> 4. Academic Program &amp; Degree Selection</h3>

              <div class="grid-2">
                <!-- Program Dropdown -->
                <div class="form-group span-2">
                  <label for="course">Intended Program / Course of Study <span class="required">*</span></label>
                  <div class="input-wrapper">
                    <i class="fas fa-laptop-code input-icon"></i>
                    <select id="course" name="course" class="form-control" required>
                      <option value="B.Tech in Computer Science &amp; Engineering" selected>B.Tech in Computer Science &amp; Engineering</option>
                      <option value="B.Tech in Artificial Intelligence &amp; Data Science">B.Tech in Artificial Intelligence &amp; Data Science</option>
                      <option value="B.Tech in Electronics &amp; Communication Engineering">B.Tech in Electronics &amp; Communication Engineering</option>
                      <option value="Master of Computer Applications (MCA)">Master of Computer Applications (MCA)</option>
                      <option value="M.Tech in Cloud Systems &amp; Cybersecurity">M.Tech in Cloud Systems &amp; Cybersecurity</option>
                      <option value="Postgraduate Diploma in Full-Stack Web Engineering">Postgraduate Diploma in Full-Stack Web Engineering</option>
                    </select>
                  </div>
                  <div id="courseError" class="error-message"></div>
                </div>

                <!-- Qualifying Exam -->
                <div class="form-group">
                  <label for="education">Highest Qualification / Exam Passed</label>
                  <div class="input-wrapper">
                    <i class="fas fa-certificate input-icon"></i>
                    <select id="education" name="education" class="form-control">
                      <option value="10+2 / Higher Secondary (CBSE / ISC / State Board)">10+2 / Higher Secondary (CBSE / ISC / State Board)</option>
                      <option value="3-Year Polytechnic / Engineering Diploma">3-Year Polytechnic / Engineering Diploma</option>
                      <option value="Bachelor's Degree (B.Sc / BCA / B.Tech)" selected>Bachelor's Degree (B.Sc / BCA / B.Tech)</option>
                      <option value="Master's Degree (M.Sc / M.Tech / MCA)">Master's Degree (M.Sc / M.Tech / MCA)</option>
                    </select>
                  </div>
                </div>

                <!-- Mode of Study -->
                <div class="form-group">
                  <label>Mode of Study</label>
                  <div class="radio-group">
                    <label class="radio-label active">
                      <input type="radio" name="studyMode" value="Full-Time (On-Campus)" checked> Full-Time (Campus)
                    </label>
                    <label class="radio-label">
                      <input type="radio" name="studyMode" value="Hybrid (Blended)"> Hybrid
                    </label>
                    <label class="radio-label">
                      <input type="radio" name="studyMode" value="100% Online / Remote"> Online
                    </label>
                  </div>
                </div>
              </div>
            </div>

            <!-- SECTION 5: Technical Specializations & Skills -->
            <div class="form-section">
              <h3 class="section-title"><i class="fas fa-tools"></i> 5. Key Technical Competencies &amp; Skills</h3>
              <p style="font-size: 0.82rem; color: var(--text-muted); margin-bottom: 10px;">Select all areas where you possess prior knowledge or wish to specialize:</p>

              <div class="checkbox-grid">
                <label class="checkbox-label active">
                  <input type="checkbox" name="skills[]" value="Web Engineering (HTML5/CSS3/JavaScript/PHP)" checked> Web Engineering (HTML5/CSS3/JavaScript/PHP)
                </label>
                <label class="checkbox-label active">
                  <input type="checkbox" name="skills[]" value="Python &amp; Machine Learning" checked> Python &amp; Machine Learning
                </label>
                <label class="checkbox-label">
                  <input type="checkbox" name="skills[]" value="Cloud Computing &amp; Containerization (Docker/Kubernetes)"> Cloud Computing &amp; Containerization (Docker/Kubernetes)
                </label>
                <label class="checkbox-label">
                  <input type="checkbox" name="skills[]" value="Database Systems &amp; SQL/NoSQL Architecture"> Database Systems &amp; SQL/NoSQL Architecture
                </label>
                <label class="checkbox-label">
                  <input type="checkbox" name="skills[]" value="Cybersecurity &amp; Information Defense"> Cybersecurity &amp; Information Defense
                </label>
                <label class="checkbox-label">
                  <input type="checkbox" name="skills[]" value="Data Structures &amp; Algorithms (C++/Java)"> Data Structures &amp; Algorithms (C++/Java)
                </label>
              </div>
            </div>

            <!-- SECTION 6: Candidate Statement of Purpose -->
            <div class="form-section">
              <h3 class="section-title"><i class="fas fa-pen-nib"></i> 6. Statement of Purpose / Academic Objectives</h3>

              <div class="form-group">
                <label for="statementOfPurpose">Briefly outline your academic motivation, technical ambitions, and career goals:</label>
                <textarea id="statementOfPurpose" name="statementOfPurpose" class="form-control" maxlength="500" placeholder="e.g. I am passionate about software systems, full-stack web engineering, and applied artificial intelligence. I aspire to build high-performance software solutions for India's digital ecosystem.">I am passionate about software systems, full-stack web engineering, and applied artificial intelligence. I aspire to build high-performance software solutions for India's digital ecosystem.</textarea>
                <div class="char-counter"><span id="charCount">0</span> / 500 characters</div>
              </div>
            </div>

            <!-- SECTION 7: Declaration & Submission -->
            <div class="form-section">
              <div class="form-group">
                <label class="checkbox-label" style="background: transparent; border: none; padding: 0;">
                  <input type="checkbox" id="terms" name="terms" value="accepted" checked required>
                  <span>I hereby declare that all information furnished in this application is authentic, accurate, and complete as per Indian regulatory norms. <span class="required">*</span></span>
                </label>
                <div id="termsError" class="error-message"></div>
              </div>
            </div>

            <!-- Actions Bar -->
            <div class="form-actions">
              <button type="reset" class="btn btn-secondary" id="btnReset">
                <i class="fas fa-undo"></i> Reset Form
              </button>
              <button type="submit" id="submitBtn" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Submit Application Form
              </button>
            </div>

          </form>
        </div>
      </main>

    </div>

    <!-- Official Printable Result Card Container -->
    <div id="resultContainer" class="result-card"></div>

  </div>

  <!-- jQuery 3.7.1 Library CDN -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <!-- Application Client Scripts -->
  <script src="js/app.js"></script>

</body>
</html>
