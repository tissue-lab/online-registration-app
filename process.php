<?php
// Start session for CSRF verification
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('X-Content-Type-Options: nosniff');

function isAjax() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (!empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false);
}

function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if (isAjax()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => 'Invalid request method. Only POST is allowed.']);
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
        echo json_encode(['success' => false, 'message' => 'Security token expired. Please refresh the page.']);
        exit;
    } else {
        die('Security verification failed. Please try again.');
    }
}

// -------------------------------------------------------------
// 1. Data Collection & Sanitization
// -------------------------------------------------------------
$fullName     = cleanInput($_POST['fullName'] ?? '');
$email        = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$phoneRaw     = cleanInput($_POST['phone'] ?? '');
$altPhone     = cleanInput($_POST['altPhone'] ?? '');
$dob          = cleanInput($_POST['dob'] ?? '');
$gender       = cleanInput($_POST['gender'] ?? 'Male');
$category     = cleanInput($_POST['category'] ?? 'General (GEN)');
$course       = cleanInput($_POST['course'] ?? '');
$education    = cleanInput($_POST['education'] ?? 'Bachelor\'s Degree');
$studyMode    = cleanInput($_POST['studyMode'] ?? 'Full-Time (On-Campus)');
$address      = cleanInput($_POST['address'] ?? '');
$city         = cleanInput($_POST['city'] ?? '');
$state        = cleanInput($_POST['state'] ?? 'Karnataka');
$zipCode      = cleanInput($_POST['zipCode'] ?? '');
$country      = 'India';
$sop          = cleanInput($_POST['statementOfPurpose'] ?? 'No statement provided.');
$terms        = $_POST['terms'] ?? '';

// Sanitize Skills
$skills = [];
if (!empty($_POST['skills']) && is_array($_POST['skills'])) {
    foreach ($_POST['skills'] as $skill) {
        $cSkill = cleanInput($skill);
        if (!empty($cSkill)) {
            $skills[] = $cSkill;
        }
    }
}

// -------------------------------------------------------------
// 2. Server-side Validation with Indian Specifications
// -------------------------------------------------------------
$errors = [];

// Name validation
if (empty($fullName) || strlen($fullName) < 2) {
    $errors[] = 'Candidate Full Name is required (minimum 2 characters).';
}

// Email validation
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}

// Indian Mobile Number validation (10 digits starting with 6, 7, 8, 9)
$phoneDigits = preg_replace('/\D/', '', $phoneRaw);
if (strlen($phoneDigits) === 12 && substr($phoneDigits, 0, 2) === '91') {
    $phoneDigits = substr($phoneDigits, 2);
}
if (strlen($phoneDigits) !== 10 || !in_array($phoneDigits[0], ['6', '7', '8', '9'])) {
    $errors[] = 'A valid 10-digit Indian mobile number starting with 6, 7, 8, or 9 is required.';
}
$formattedPhone = '+91 ' . substr($phoneDigits, 0, 5) . ' ' . substr($phoneDigits, 5);

// Date of Birth & Age check (min 14 years)
if (empty($dob)) {
    $errors[] = 'Date of birth is required.';
} else {
    $birthTimestamp = strtotime($dob);
    $minAgeTimestamp = strtotime('-14 years');
    if ($birthTimestamp > $minAgeTimestamp) {
        $errors[] = 'Candidate must be at least 14 years of age.';
    }
}

// Indian Address checks
if (empty($address) || strlen($address) < 5) {
    $errors[] = 'Permanent street address is required (minimum 5 characters).';
}

if (empty($city) || strlen($city) < 2) {
    $errors[] = 'City / District is required.';
}

if (empty($state)) {
    $errors[] = 'State / Union Territory is required.';
}

// Indian 6-Digit PIN Code validation
if (empty($zipCode) || !preg_match('/^[1-9][0-9]{5}$/', $zipCode)) {
    $errors[] = 'A valid 6-digit Indian Postal PIN Code (e.g. 560001) is required.';
}

if (empty($course)) {
    $errors[] = 'Please select your intended academic program.';
}

if ($terms !== 'accepted') {
    $errors[] = 'You must accept the official candidate declaration.';
}

if (!empty($errors)) {
    if (isAjax()) {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(['success' => false, 'message' => implode(' ', $errors), 'errors' => $errors]);
        exit;
    } else {
        die('<h3>Validation Error:</h3><p>' . implode('</p><p>', $errors) . '</p><p><a href="javascript:history.back()">Go Back</a></p>');
    }
}

// -------------------------------------------------------------
// 3. File Upload Processing (Candidate Photo)
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
    $originalName  = $_FILES['photo']['name'];
    $ext           = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    if ($fileSize <= 3 * 1024 * 1024 && in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $fileTmpPath);
        finfo_close($finfo);

        if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
            $newFileName = 'photo_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest = $uploadsDir . DIRECTORY_SEPARATOR . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest)) {
                $photoWebPath = 'uploads/' . $newFileName;
                $hasCustomPhoto = true;
            }
        }
    }
}

if (!$hasCustomPhoto) {
    // Generate initials SVG avatar
    $initials = strtoupper(substr($fullName, 0, 1));
    $words = explode(' ', $fullName);
    if (count($words) > 1) {
        $initials .= strtoupper(substr($words[count($words) - 1], 0, 1));
    }
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="140" viewBox="0 0 120 140">' .
           '<rect width="120" height="140" rx="8" fill="#1e3a8a"/>' .
           '<text x="50%" y="52%" font-family="Inter, sans-serif" font-size="46" font-weight="700" fill="#ffffff" dominant-baseline="middle" text-anchor="middle">' . $initials . '</text>' .
           '</svg>';
    $photoWebPath = 'data:image/svg+xml;base64,' . base64_encode($svg);
}

// -------------------------------------------------------------
// 4. Generate Official Indian Application Reference Number
// -------------------------------------------------------------
$applicationId = 'NITAS/ADM/2026/' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
$submissionTimestamp = date('Y-m-d H:i:s');
$indianDate = date('d-m-Y h:i A') . ' IST';
$dobFormatted = date('d/m/Y', strtotime($dob));

$record = [
    'application_id' => $applicationId,
    'full_name'      => $fullName,
    'email'          => $email,
    'phone'          => $formattedPhone,
    'alt_phone'      => $altPhone ? ('+91 ' . $altPhone) : 'N/A',
    'dob'            => $dobFormatted,
    'gender'         => $gender,
    'category'       => $category,
    'course'         => $course,
    'education'      => $education,
    'study_mode'     => $studyMode,
    'address'        => $address,
    'city'           => $city,
    'state'          => $state,
    'zip_code'       => $zipCode,
    'country'        => 'India',
    'skills'         => $skills,
    'sop'            => $sop,
    'photo_url'      => $photoWebPath,
    'created_at'     => $indianDate,
    'status'         => 'PROVISIONALLY REGISTERED'
];

// Save to JSON Database
$dataDir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}
$dataFile = $dataDir . DIRECTORY_SEPARATOR . 'applications.json';
$existing = [];
if (file_exists($dataFile)) {
    $content = file_get_contents($dataFile);
    if (!empty($content)) {
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $existing = $decoded;
        }
    }
}
array_unshift($existing, $record);
file_put_contents($dataFile, json_encode($existing, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), LOCK_EX);

// -------------------------------------------------------------
// 5. Generate Official Indian Admission Acknowledgement Slip HTML
// -------------------------------------------------------------
ob_start();
?>
<!-- Result Toolbar -->
<div class="result-toolbar">
  <div class="badge-status">
    <i class="fas fa-check-circle"></i> APPLICATION REGISTERED SUCCESSFULLY
  </div>
  <div class="toolbar-actions">
    <button type="button" id="btnNewForm" class="btn-tool">
      <i class="fas fa-plus"></i> New Application
    </button>
    <a href="records.php" class="btn-tool">
      <i class="fas fa-list"></i> Candidate Directory
    </a>
    <button type="button" id="btnPrint" class="btn-tool btn-print">
      <i class="fas fa-print"></i> Print / Save PDF
    </button>
  </div>
</div>

<!-- Official Indian Admission Slip -->
<div class="document-sheet">
  
  <!-- Slip Header with Official National Emblem Styling -->
  <div class="doc-header-official">
    <div class="doc-emblem-badge">
      <i class="fas fa-landmark"></i> Ministry of Higher Education &bull; Government of India
    </div>
    <div class="doc-institute-hindi">राष्ट्रीय प्रौद्योगिकी एवं उन्नत अध्ययन संस्थान</div>
    <h1 class="doc-institute-name">National Institute of Technology &amp; Advanced Studies</h1>
    <div class="doc-slip-title">
      Provisional Admission &amp; Registration Acknowledgement Slip &bull; Session 2026–2027
    </div>
  </div>

  <!-- Reference ID & Submission Timestamp Bar -->
  <div class="doc-meta-bar">
    <div class="ref-box">
      <span class="ref-label">Application Roll / Ref No:</span>
      <span class="ref-value"><?php echo htmlspecialchars($applicationId); ?></span>
    </div>
    <div class="ref-date">
      <i class="far fa-clock"></i> <strong>Filing Timestamp:</strong> <?php echo htmlspecialchars($indianDate); ?>
    </div>
  </div>

  <!-- Candidate Identity Row -->
  <div class="doc-profile-row">
    <img src="<?php echo htmlspecialchars($photoWebPath); ?>" alt="Candidate Photograph" class="doc-avatar">
    <div class="doc-applicant-meta">
      <h2><?php echo htmlspecialchars($fullName); ?></h2>
      <div class="applied-course"><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($course); ?></div>
      <span class="badge-mode"><i class="fas fa-university"></i> <?php echo htmlspecialchars($studyMode); ?></span>
      <div style="font-size: 0.82rem; color: #475569; margin-top: 6px;">
        <strong>Social Category:</strong> <?php echo htmlspecialchars($category); ?>
      </div>
    </div>
  </div>

  <!-- SECTION 1: Personal & Communication Details -->
  <div class="doc-section-heading">
    <i class="fas fa-id-card"></i> Candidate Personal &amp; Communication Details
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
      <span class="label">Primary Indian Mobile Number</span>
      <span class="value" style="color: var(--primary); font-weight: 700;"><?php echo htmlspecialchars($formattedPhone); ?></span>
    </div>
    <div class="detail-item">
      <span class="label">Date of Birth (DD/MM/YYYY)</span>
      <span class="value"><?php echo htmlspecialchars($dobFormatted); ?></span>
    </div>
    <div class="detail-item">
      <span class="label">Gender Identification</span>
      <span class="value"><?php echo htmlspecialchars($gender); ?></span>
    </div>
    <div class="detail-item">
      <span class="label">Qualifying Examination</span>
      <span class="value"><?php echo htmlspecialchars($education); ?></span>
    </div>
  </div>

  <!-- SECTION 2: Permanent Residential Address (Indian) -->
  <div class="doc-section-heading">
    <i class="fas fa-map-marked-alt"></i> Permanent Residential Address in India
  </div>
  <div class="doc-details-grid">
    <div class="detail-item" style="grid-column: span 2;">
      <span class="label">House / Flat No., Street, Locality</span>
      <span class="value"><?php echo htmlspecialchars($address); ?></span>
    </div>
    <div class="detail-item">
      <span class="label">City / District</span>
      <span class="value"><?php echo htmlspecialchars($city); ?></span>
    </div>
    <div class="detail-item">
      <span class="label">State / Union Territory</span>
      <span class="value"><?php echo htmlspecialchars($state); ?></span>
    </div>
    <div class="detail-item">
      <span class="label">Indian Postal PIN Code</span>
      <span class="value" style="font-family: monospace; font-size: 1.05rem; font-weight: 800; color: var(--accent);">
        <?php echo htmlspecialchars($zipCode); ?>
      </span>
    </div>
    <div class="detail-item">
      <span class="label">Country</span>
      <span class="value">India (IN)</span>
    </div>
  </div>

  <!-- SECTION 3: Technical Skills -->
  <div class="doc-section-heading">
    <i class="fas fa-laptop-code"></i> Technical Competencies &amp; Specializations
  </div>
  <div style="margin-bottom: 14px;">
    <?php if (!empty($skills)): ?>
      <div class="tag-list">
        <?php foreach ($skills as $skill): ?>
          <span class="tag-badge"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($skill); ?></span>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p style="color: #64748b; font-size: 0.88rem;">No prior technical specializations specified.</p>
    <?php endif; ?>
  </div>

  <!-- SECTION 4: Statement of Purpose -->
  <div class="doc-section-heading">
    <i class="fas fa-quote-left"></i> Candidate Statement of Purpose
  </div>
  <div class="statement-box">
    &ldquo;<?php echo nl2br(htmlspecialchars($sop)); ?>&rdquo;
  </div>

  <!-- Official Verification Footer with Barcode & Signatures -->
  <div class="doc-footer">
    <div>
      <div class="verification-code">
        <strong>Digital Security Hash:</strong> <?php echo strtoupper(sha1($applicationId . $email . $zipCode)); ?><br>
        <strong>Portal Verification:</strong> ALL INDIA ADMISSIONS SYSTEM &bull; CONFIRMED
      </div>
      <div class="barcode-sim">
        ||||| | |||| |||||| || | |||| |||| ||||| |||||||
      </div>
    </div>
    <div class="signature-box">
      <div class="sign-line"></div>
      <div class="sign-title">Controller of Admissions &amp; Examinations</div>
      <div style="font-size: 0.72rem; color: #64748b;">NITAS Central Examination Board, New Delhi</div>
    </div>
  </div>

</div>
<?php
$renderedHtml = ob_get_clean();

// -------------------------------------------------------------
// 6. Return Response
// -------------------------------------------------------------
if (isAjax()) {
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode([
        'success'        => true,
        'message'        => 'Application registered successfully!',
        'application_id' => $applicationId,
        'data'           => $record,
        'html'           => $renderedHtml
    ]);
    exit;
} else {
    // Non-AJAX fallback
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Admission Acknowledgement Slip - <?php echo htmlspecialchars($applicationId); ?></title>
      <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
      <div class="app-container">
        <div class="result-card" style="display: block;">
          <?php echo $renderedHtml; ?>
        </div>
      </div>
    </body>
    </html>
    <?php
    exit;
}
