<?php
// Start session for CSRF security token
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Online Application &amp; Registration Portal</title>
  
  <!-- Google Fonts & Font Awesome Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Custom Modern Stylesheet -->
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <div class="app-container">

    <!-- Top Branding Header -->
    <header class="app-header">
      <div class="brand-badge">
        <i class="fas fa-graduation-cap"></i> Academic &amp; Professional Admissions 2026-2027
      </div>
      <h1 class="brand-title">Global Institute of Technology &amp; Innovation</h1>
      <p class="brand-subtitle">
        Empowering future innovators through cutting-edge education. Complete the form below to initiate your formal registration.
      </p>
      
      <nav class="nav-links">
        <a href="index.php"><i class="fas fa-edit"></i> Application Form</a>
        <a href="records.php"><i class="fas fa-database"></i> Registered Candidates</a>
      </nav>
    </header>

    <!-- Global Alert Box -->
    <div id="alertBox" class="alert" style="display: none;"></div>

    <!-- Main Registration Form Container -->
    <div id="formContainer" class="form-card">
      <div class="form-header-bar">
        <h2><i class="fas fa-file-signature"></i> Candidate Registration Form</h2>
        <span class="step-indicator"><i class="fas fa-shield-alt"></i> Official Admission Portal</span>
      </div>

      <div class="form-body">
        <form id="registrationForm" method="POST" action="process.php" enctype="multipart/form-data" novalidate>
          
          <!-- Hidden CSRF Token -->
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

          <!-- SECTION 1: Personal Details -->
          <div class="form-section">
            <h3 class="section-title"><i class="fas fa-user-circle"></i> 1. Personal Information</h3>

            <!-- Photo Upload Area -->
            <div class="form-group span-2">
              <label>Applicant Photograph</label>
              <div class="photo-upload-container">
                <div class="photo-preview-box">
                  <i class="fas fa-user" id="photoPlaceholderIcon"></i>
                  <img id="previewImg" alt="Preview Photo">
                </div>
                <div class="photo-upload-meta">
                  <div class="file-input-wrapper">
                    <label for="photoInput" class="file-upload-btn">
                      <i class="fas fa-cloud-upload-alt"></i> Choose Image
                    </label>
                    <input type="file" id="photoInput" name="photo" accept="image/jpeg,image/png,image/webp">
                  </div>
                  <p>JPG, PNG, or WebP format. Maximum size 3MB. Clear headshot recommended.</p>
                  <div id="photoError" class="error-message"></div>
                </div>
              </div>
            </div>

            <div class="grid-2">
              <!-- Full Name -->
              <div class="form-group">
                <label for="fullName">Full Name <span class="required">*</span></label>
                <div class="input-wrapper">
                  <i class="fas fa-user input-icon"></i>
                  <input type="text" id="fullName" name="fullName" class="form-control" placeholder="e.g. Alexander Vance" required>
                </div>
                <div id="fullNameError" class="error-message"></div>
              </div>

              <!-- Email Address -->
              <div class="form-group">
                <label for="email">Email Address <span class="required">*</span></label>
                <div class="input-wrapper">
                  <i class="fas fa-envelope input-icon"></i>
                  <input type="email" id="email" name="email" class="form-control" placeholder="alexander@example.com" required>
                </div>
                <div id="emailError" class="error-message"></div>
              </div>

              <!-- Phone Number -->
              <div class="form-group">
                <label for="phone">Phone / Mobile Number <span class="required">*</span></label>
                <div class="input-wrapper">
                  <i class="fas fa-phone input-icon"></i>
                  <input type="tel" id="phone" name="phone" class="form-control" placeholder="+1 (555) 019-2834" required>
                </div>
                <div id="phoneError" class="error-message"></div>
              </div>

              <!-- Date of Birth -->
              <div class="form-group">
                <label for="dob">Date of Birth <span class="required">*</span></label>
                <div class="input-wrapper">
                  <i class="fas fa-calendar-alt input-icon"></i>
                  <input type="date" id="dob" name="dob" class="form-control" max="<?php echo date('Y-m-d', strtotime('-14 years')); ?>" required>
                </div>
                <div id="dobError" class="error-message"></div>
              </div>

              <!-- Gender -->
              <div class="form-group span-2">
                <label>Gender <span class="required">*</span></label>
                <div class="radio-group">
                  <label class="radio-label">
                    <input type="radio" name="gender" value="Male"> Male
                  </label>
                  <label class="radio-label">
                    <input type="radio" name="gender" value="Female"> Female
                  </label>
                  <label class="radio-label">
                    <input type="radio" name="gender" value="Non-Binary"> Non-Binary
                  </label>
                  <label class="radio-label">
                    <input type="radio" name="gender" value="Prefer not to say"> Prefer not to say
                  </label>
                </div>
                <div id="genderError" class="error-message"></div>
              </div>
            </div>
          </div>

          <!-- SECTION 2: Academic & Program Details -->
          <div class="form-section">
            <h3 class="section-title"><i class="fas fa-book-reader"></i> 2. Academic Program Selection</h3>
            
            <div class="grid-3">
              <!-- Course / Program -->
              <div class="form-group span-2">
                <label for="course">Program / Specialization <span class="required">*</span></label>
                <div class="input-wrapper">
                  <i class="fas fa-laptop-code input-icon"></i>
                  <select id="course" name="course" class="form-control" required>
                    <option value="" disabled selected>-- Select Your Intended Program --</option>
                    <option value="B.S. in Computer Science &amp; Software Engineering">B.S. in Computer Science &amp; Software Engineering</option>
                    <option value="M.S. in Artificial Intelligence &amp; Machine Learning">M.S. in Artificial Intelligence &amp; Machine Learning</option>
                    <option value="Advanced Full-Stack Web Architecture Certification">Advanced Full-Stack Web Architecture Certification</option>
                    <option value="Cloud Systems Engineering &amp; DevOps Specialist">Cloud Systems Engineering &amp; DevOps Specialist</option>
                    <option value="Cybersecurity &amp; Network Defense Program">Cybersecurity &amp; Network Defense Program</option>
                    <option value="Data Science &amp; Predictive Analytics Diploma">Data Science &amp; Predictive Analytics Diploma</option>
                  </select>
                </div>
                <div id="courseError" class="error-message"></div>
              </div>

              <!-- Education Level -->
              <div class="form-group">
                <label for="education">Highest Qualification</label>
                <div class="input-wrapper">
                  <i class="fas fa-graduation-cap input-icon"></i>
                  <select id="education" name="education" class="form-control">
                    <option value="High School Diploma / Secondary">High School Diploma / Secondary</option>
                    <option value="Associate Degree / Polytechnic">Associate Degree / Polytechnic</option>
                    <option value="Bachelor's Degree" selected>Bachelor's Degree</option>
                    <option value="Master's Degree">Master's Degree</option>
                    <option value="Doctorate / PhD">Doctorate / PhD</option>
                  </select>
                </div>
              </div>

              <!-- Study Mode -->
              <div class="form-group span-3">
                <label>Preferred Mode of Study</label>
                <div class="radio-group">
                  <label class="radio-label active">
                    <input type="radio" name="studyMode" value="Full-Time (On-Campus)" checked> Full-Time (On-Campus)
                  </label>
                  <label class="radio-label">
                    <input type="radio" name="studyMode" value="Hybrid (Blended)"> Hybrid (Blended)
                  </label>
                  <label class="radio-label">
                    <input type="radio" name="studyMode" value="100% Online / Remote"> 100% Online / Remote
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- SECTION 3: Residential & Contact Address -->
          <div class="form-section">
            <h3 class="section-title"><i class="fas fa-map-marker-alt"></i> 3. Address &amp; Location</h3>

            <div class="grid-2">
              <div class="form-group span-2">
                <label for="address">Street Address</label>
                <div class="input-wrapper">
                  <i class="fas fa-home input-icon"></i>
                  <input type="text" id="address" name="address" class="form-control" placeholder="124 Innovation Way, Suite 400">
                </div>
              </div>

              <div class="form-group">
                <label for="city">City <span class="required">*</span></label>
                <div class="input-wrapper">
                  <i class="fas fa-city input-icon"></i>
                  <input type="text" id="city" name="city" class="form-control" placeholder="San Francisco" required>
                </div>
                <div id="cityError" class="error-message"></div>
              </div>

              <div class="form-group">
                <label for="state">State / Province</label>
                <div class="input-wrapper">
                  <i class="fas fa-map-marked-alt input-icon"></i>
                  <input type="text" id="state" name="state" class="form-control" placeholder="California">
                </div>
              </div>

              <div class="form-group">
                <label for="zipCode">Postal / Zip Code</label>
                <div class="input-wrapper">
                  <i class="fas fa-mail-bulk input-icon"></i>
                  <input type="text" id="zipCode" name="zipCode" class="form-control" placeholder="94105">
                </div>
              </div>

              <div class="form-group">
                <label for="country">Country <span class="required">*</span></label>
                <div class="input-wrapper">
                  <i class="fas fa-globe input-icon"></i>
                  <select id="country" name="country" class="form-control" required>
                    <option value="" disabled selected>-- Select Country --</option>
                    <option value="United States">United States</option>
                    <option value="India">India</option>
                    <option value="United Kingdom">United Kingdom</option>
                    <option value="Canada">Canada</option>
                    <option value="Australia">Australia</option>
                    <option value="Germany">Germany</option>
                    <option value="Singapore">Singapore</option>
                    <option value="Other">Other Country</option>
                  </select>
                </div>
                <div id="countryError" class="error-message"></div>
              </div>
            </div>
          </div>

          <!-- SECTION 4: Skills & Area of Interest -->
          <div class="form-section">
            <h3 class="section-title"><i class="fas fa-code-branch"></i> 4. Technical Skills &amp; Key Interests</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 12px;">Select all competencies that match your current background or learning goals:</p>

            <div class="checkbox-grid">
              <label class="checkbox-label">
                <input type="checkbox" name="skills[]" value="Web Development (HTML/CSS/JS/PHP)"> Web Development (HTML/CSS/JS/PHP)
              </label>
              <label class="checkbox-label">
                <input type="checkbox" name="skills[]" value="Python &amp; Machine Learning"> Python &amp; Machine Learning
              </label>
              <label class="checkbox-label">
                <input type="checkbox" name="skills[]" value="Cloud Computing &amp; Docker"> Cloud Computing &amp; Docker
              </label>
              <label class="checkbox-label">
                <input type="checkbox" name="skills[]" value="Database Architecture (SQL/NoSQL)"> Database Architecture (SQL/NoSQL)
              </label>
              <label class="checkbox-label">
                <input type="checkbox" name="skills[]" value="Cybersecurity &amp; Ethical Hacking"> Cybersecurity &amp; Ethical Hacking
              </label>
              <label class="checkbox-label">
                <input type="checkbox" name="skills[]" value="UI/UX Product Design"> UI/UX Product Design
              </label>
            </div>
          </div>

          <!-- SECTION 5: Statement of Purpose -->
          <div class="form-section">
            <h3 class="section-title"><i class="fas fa-pen-fancy"></i> 5. Statement of Purpose / Bio</h3>
            
            <div class="form-group">
              <label for="statementOfPurpose">Briefly describe your career objectives and motivation for applying:</label>
              <textarea id="statementOfPurpose" name="statementOfPurpose" class="form-control" maxlength="500" placeholder="Share your academic interests, career goals, or why you are excited to join this program..."></textarea>
              <div class="char-counter"><span id="charCount">0</span> / 500 characters</div>
            </div>
          </div>

          <!-- SECTION 6: Declarations & Agreement -->
          <div class="form-section">
            <div class="form-group">
              <label class="checkbox-label" style="background: transparent; border: none; padding: 0;">
                <input type="checkbox" id="terms" name="terms" value="accepted" required>
                <span>I hereby declare that all information submitted in this application is truthful and accurate to the best of my knowledge. <span class="required">*</span></span>
              </label>
              <div id="termsError" class="error-message"></div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="form-actions">
            <button type="reset" class="btn btn-secondary">
              <i class="fas fa-undo"></i> Reset Form
            </button>
            <button type="submit" id="submitBtn" class="btn btn-primary">
              <i class="fas fa-paper-plane"></i> Submit Application
            </button>
          </div>

        </form>
      </div>
    </div>

    <!-- Confirmation / Printable Result View Container -->
    <div id="resultContainer" class="result-card"></div>

  </div>

  <!-- jQuery 3.7.1 Library CDN -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <!-- Application Client Scripts -->
  <script src="js/app.js"></script>

</body>
</html>
