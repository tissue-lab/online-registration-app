<?php
// Start session for CSRF verification
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('X-Content-Type-Options: nosniff');

// Helper to determine if the request is an AJAX request
function isAjax() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}

// Function to safely sanitize string input
function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

// Handle non-POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isAjax()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Invalid request method. Only POST is accepted.']);
    } else {
        header('Location: index.php');
    }
    exit;
}

// Verify CSRF Token
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    if (isAjax()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Security token invalid or expired. Please refresh the page and try again.']);
        exit;
    } else {
        die('Security verification failed. Please return to the form and try again.');
    }
}

// -------------------------------------------------------------
// 1. Data Collection & Sanitization
// -------------------------------------------------------------
$fullName     = cleanInput($_POST['fullName'] ?? '');
$email        = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phone        = cleanInput($_POST['phone'] ?? '');
$dob          = cleanInput($_POST['dob'] ?? '');
$gender       = cleanInput($_POST['gender'] ?? '');
$course       = cleanInput($_POST['course'] ?? '');
$education    = cleanInput($_POST['education'] ?? 'Bachelor\'s Degree');
$studyMode    = cleanInput($_POST['studyMode'] ?? 'Full-Time (On-Campus)');
$address      = cleanInput($_POST['address'] ?? 'Not provided');
$city         = cleanInput($_POST['city'] ?? '');
$state        = cleanInput($_POST['state'] ?? 'N/A');
$zipCode      = cleanInput($_POST['zipCode'] ?? 'N/A');
$country      = cleanInput($_POST['country'] ?? '');
$sop          = cleanInput($_POST['statementOfPurpose'] ?? 'No statement provided.');
$terms        = $_POST['terms'] ?? '';

// Sanitize Skills array
$skills = [];
if (!empty($_POST['skills']) && is_array($_POST['skills'])) {
    foreach ($_POST['skills'] as $skill) {
        $cleanSkill = cleanInput($skill);
        if (!empty($cleanSkill)) {
            $skills[] = $cleanSkill;
        }
    }
}

// -------------------------------------------------------------
// 2. Server-side Validation
// -------------------------------------------------------------
$errors = [];

if (empty($fullName) || strlen($fullName) < 2) {
    $errors[] = 'Full name is required (minimum 2 characters).';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}

$digitsCount = preg_match_all('/[0-9]/', $phone);
if (empty($phone) || !preg_match('/^[+]?[0-9\s\-().]{7,25}$/', $phone) || $digitsCount < 7) {
    $errors[] = 'A valid contact phone number is required (min 7 digits).';
}

if (empty($dob)) {
    $errors[] = 'Date of birth is required.';
} else {
    $birthDate = strtotime($dob);
    $minAgeDate = strtotime('-14 years');
    if ($birthDate > $minAgeDate) {
        $errors[] = 'Candidate must be at least 14 years old.';
    }
}

$validGenders = ['Male', 'Female', 'Non-Binary', 'Prefer not to say'];
if (empty($gender) || !in_array($gender, $validGenders)) {
    $errors[] = 'Please select a valid gender option.';
}

if (empty($course)) {
    $errors[] = 'Please select your intended program / course.';
}

if (empty($city)) {
    $errors[] = 'City is required.';
}

if (empty($country)) {
    $errors[] = 'Country is required.';
}

if ($terms !== 'accepted') {
    $errors[] = 'You must accept the declarations and agreement.';
}

// If validation errors exist, abort
if (!empty($errors)) {
    if (isAjax()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode([
            'success' => false,
            'message' => implode(' ', $errors),
            'errors'  => $errors
        ]);
        exit;
    } else {
        die('<h3>Validation Error(s):</h3><ul><li>' . implode('</li><li>', $errors) . '</li></ul><p><a href="javascript:history.back()">Go Back</a></p>');
    }
}

// -------------------------------------------------------------
// 3. File Upload Processing (Applicant Photo)
// -------------------------------------------------------------
$uploadsDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

$photoWebPath = '';
$hasCustomPhoto = false;

if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath   = $_FILES['photo']['tmp_name'];
    $fileSize      = $_FILES['photo']['size'];
    $fileType      = $_FILES['photo']['type'];
    $originalName  = $_FILES['photo']['name'];
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
    $maxFileSize = 3 * 1024 * 1024; // 3 MB

    if ($fileSize <= $maxFileSize && in_array($fileExtension, $allowedExtensions)) {
        // Double-check MIME type using finfo
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fileTmpPath);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (in_array($mime, $allowedMimes)) {
            $newFileName = 'applicant_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
            $destPath = $uploadsDir . DIRECTORY_SEPARATOR . $newFileName;

            if (move_uploaded_file($fileTmpPath, $destPath)) {
                $photoWebPath = 'uploads/' . $newFileName;
                $hasCustomPhoto = true;
            }
        }
    }
}

// Fallback Default SVG Avatar if no photo was uploaded
if (!$hasCustomPhoto) {
    // Generate an inline SVG avatar with candidate initials
    $initials = strtoupper(substr($fullName, 0, 1));
    $words = explode(' ', $fullName);
    if (count($words) > 1) {
        $initials .= strtoupper(substr($words[count($words) - 1], 0, 1));
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120" viewBox="0 0 120 120">' .
           '<rect width="120" height="120" rx="12" fill="#4f46e5"/>' .
           '<text x="50%" y="54%" font-family="Inter, sans-serif" font-size="44" font-weight="700" fill="#ffffff" dominant-baseline="middle" text-anchor="middle">' . $initials . '</text>' .
           '</svg>';
    $photoWebPath = 'data:image/svg+xml;base64,' . base64_encode($svg);
}

// -------------------------------------------------------------
// 4. Generate Application ID and Record
// -------------------------------------------------------------
$applicationId = 'REG-' . date('Y') . '-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
$submissionTimestamp = date('Y-m-d H:i:s');
$formattedDate = date('F j, Y, g:i A T');

$record = [
    'application_id' => $applicationId,
    'full_name'      => $fullName,
    'email'          => $email,
    'phone'          => $phone,
    'dob'            => $dob,
    'gender'         => $gender,
    'course'         => $course,
    'education'      => $education,
    'study_mode'     => $studyMode,
    'address'        => $address,
    'city'           => $city,
    'state'          => $state,
    'zip_code'       => $zipCode,
    'country'        => $country,
    'skills'         => $skills,
    'sop'            => $sop,
    'photo_url'      => $photoWebPath,
    'created_at'     => $submissionTimestamp,
    'status'         => 'PROCESSED &amp; ADMITTED'
];

// -------------------------------------------------------------
// 5. Save Record to Storage (JSON Database)
// -------------------------------------------------------------
$dataDir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}
$dataFile = $dataDir . DIRECTORY_SEPARATOR . 'applications.json';

$existingRecords = [];
if (file_exists($dataFile)) {
    $content = file_get_contents($dataFile);
    if (!empty($content)) {
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $existingRecords = $decoded;
        }
    }
}

// Prepend the new record so newest is first
array_unshift($existingRecords, $record);
file_put_contents($dataFile, json_encode($existingRecords, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

// -------------------------------------------------------------
// 6. Generate Formatted Browser Presentation (Styled Output)
// -------------------------------------------------------------
ob_start();
?>
<!-- Result Toolbar with Print and Reset actions -->
<div class="result-toolbar">
  <div class="badge-status">
    <i class="fas fa-check-circle"></i> APPLICATION SUCCESSFULLY SUBMITTED
  </div>
  <div class="toolbar-actions">
    <button type="button" id="btnNewForm" class="btn-tool">
      <i class="fas fa-plus"></i> Submit Another
    </button>
    <a href="records.php" class="btn-tool">
      <i class="fas fa-list"></i> View All
    </a>
    <button type="button" id="btnPrint" class="btn-tool btn-print">
      <i class="fas fa-print"></i> Print / Save PDF
    </button>
  </div>
</div>

<!-- Formal Styled Document Sheet -->
<div class="document-sheet">
  
  <!-- Document Header -->
  <div class="doc-header">
    <div class="doc-org-info">
      <h1>Global Institute of Technology &amp; Innovation</h1>
      <p><i class="fas fa-map-marker-alt"></i> Office of Admissions &bull; Academic Year 2026-2027</p>
      <p style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">Official Confirmation of Online Candidate Registration</p>
    </div>
    <div class="doc-app-id">
      <div class="ref-title">Application Reference Number</div>
      <div class="ref-number"><?php echo htmlspecialchars($applicationId); ?></div>
      <div class="ref-date"><i class="far fa-clock"></i> Submitted: <?php echo htmlspecialchars($formattedDate); ?></div>
    </div>
  </div>

  <!-- Applicant Primary Profile Banner -->
  <div class="doc-profile-row">
    <img src="<?php echo htmlspecialchars($photoWebPath); ?>" alt="Applicant Photo" class="doc-avatar">
    <div class="doc-applicant-meta">
      <h2><?php echo htmlspecialchars($fullName); ?></h2>
      <div class="applied-course"><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($course); ?></div>
      <span class="badge-mode"><i class="fas fa-desktop"></i> <?php echo htmlspecialchars($studyMode); ?></span>
    </div>
  </div>

  <!-- SECTION: Candidate Personal & Contact Profile -->
  <div class="doc-section-heading">
    <i class="fas fa-id-card"></i> Candidate Profile &amp; Contact Information
  </div>
  <div class="doc-details-grid">
    <div class="detail-item">
      <span class="label">Full Legal Name</span>
      <span class="value"><?php echo htmlspecialchars($fullName); ?></span>
    </div>
    <div class="detail-item">
      <span class="label">Email Address</span>
      <span class="value"><?php echo htmlspecialchars($email); ?></span>
    </div>
    <div class="detail-item">
      <span class="label">Contact Telephone</span>
      <span class="value"><?php echo htmlspecialchars($phone); ?></span>
    </div>
    <div class="detail-item">
      <span class="label">Date of Birth</span>
      <span class="value"><?php echo htmlspecialchars($dob); ?></span>
    </div>
    <div class="detail-item">
      <span class="label">Gender Identification</span>
      <span class="value"><?php echo htmlspecialchars($gender); ?></span>
    </div>
    <div class="detail-item">
      <span class="label">Highest Qualification Level</span>
      <span class="value"><?php echo htmlspecialchars($education); ?></span>
    </div>
  </div>

  <!-- SECTION: Residential & Mailing Address -->
  <div class="doc-section-heading">
    <i class="fas fa-map-pin"></i> Residential &amp; Mailing Address
  </div>
  <div class="doc-details-grid">
    <div class="detail-item">
      <span class="label">Street Address</span>
      <span class="value"><?php echo htmlspecialchars($address); ?></span>
    </div>
    <div class="detail-item">
      <span class="label">City / Town</span>
      <span class="value"><?php echo htmlspecialchars($city); ?></span>
    </div>
    <div class="detail-item">
      <span class="label">State / Province</span>
      <span class="value"><?php echo htmlspecialchars($state); ?></span>
    </div>
    <div class="detail-item">
      <span class="label">Postal / Zip Code</span>
      <span class="value"><?php echo htmlspecialchars($zipCode); ?></span>
    </div>
    <div class="detail-item" style="grid-column: span 2;">
      <span class="label">Country of Residence</span>
      <span class="value"><?php echo htmlspecialchars($country); ?></span>
    </div>
  </div>

  <!-- SECTION: Technical Specialization & Skills -->
  <div class="doc-section-heading">
    <i class="fas fa-code"></i> Declared Technical Skills &amp; Areas of Interest
  </div>
  <div style="margin-bottom: 20px;">
    <?php if (!empty($skills)): ?>
      <div class="tag-list">
        <?php foreach ($skills as $skill): ?>
          <span class="tag-badge"><i class="fas fa-check"></i> <?php echo htmlspecialchars($skill); ?></span>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p style="color: #64748b; font-size: 0.9rem;">No specific technical competencies selected.</p>
    <?php endif; ?>
  </div>

  <!-- SECTION: Statement of Purpose -->
  <div class="doc-section-heading">
    <i class="fas fa-quote-left"></i> Candidate Statement of Purpose
  </div>
  <div class="statement-box">
    &ldquo;<?php echo nl2br(htmlspecialchars($sop)); ?>&rdquo;
  </div>

  <!-- Document Official Verification & Footer -->
  <div class="doc-footer">
    <div>
      <div class="verification-code">
        <strong>Digital Verification Hash:</strong> <?php echo strtoupper(sha1($applicationId . $email)); ?><br>
        <strong>Status:</strong> CONFIRMED &amp; FILED ELECTRONICALLY
      </div>
      <div class="barcode-sim">
        ||||| | |||| |||||| || | |||| |||| ||||| |||||||
      </div>
    </div>
    <div class="signature-box">
      <div class="sign-line"></div>
      <div class="sign-title">Authorized Registrar Signature</div>
      <div style="font-size: 0.72rem; color: #94a3b8;">Global Institute of Technology</div>
    </div>
  </div>

</div>
<?php
$renderedHtml = ob_get_clean();

// -------------------------------------------------------------
// 7. Output Response (JSON for AJAX, Full Page for Direct POST)
// -------------------------------------------------------------
if (isAjax()) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success'        => true,
        'message'        => 'Application submitted successfully!',
        'application_id' => $applicationId,
        'data'           => $record,
        'html'           => $renderedHtml
    ]);
    exit;
} else {
    // Standard POST fallback (if user has JavaScript disabled)
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Application Confirmation - <?php echo htmlspecialchars($applicationId); ?></title>
      <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
      <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
      <div class="app-container">
        <header class="app-header">
          <h1 class="brand-title">Global Institute of Technology &amp; Innovation</h1>
          <p class="brand-subtitle">Official Admission Document</p>
        </header>

        <div class="result-card" style="display: block;">
          <?php echo $renderedHtml; ?>
        </div>
      </div>
      <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
      <script>
        $('#btnPrint').on('click', function() { window.print(); });
        $('#btnNewForm').on('click', function() { window.location.href = 'index.php'; });
      </script>
    </body>
    </html>
    <?php
    exit;
}
