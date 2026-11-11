<?php
// Display single application details by Reference ID
$id = $_GET['id'] ?? '';
$id = preg_replace('/[^A-Za-z0-9\-]/', '', $id);

$dataFile = __DIR__ . '/data/applications.json';
$record = null;

if (!empty($id) && file_exists($dataFile)) {
    $records = json_decode(file_get_contents($dataFile), true);
    if (is_array($records)) {
        foreach ($records as $item) {
            if (isset($item['application_id']) && $item['application_id'] === $id) {
                $record = $item;
                break;
            }
        }
    }
}

if (!$record) {
    die("<h3>Application Record Not Found</h3><p>No registration found matching ID: " . htmlspecialchars($id) . "</p><p><a href='index.php'>Back to Registration</a></p>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Application Record - <?php echo htmlspecialchars($record['application_id']); ?></title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <div class="app-container">
    <header class="app-header">
      <h1 class="brand-title">Global Institute of Technology &amp; Innovation</h1>
      <p class="brand-subtitle">Candidate Verification Portal</p>
      <nav class="nav-links">
        <a href="index.php"><i class="fas fa-edit"></i> New Application</a>
        <a href="records.php"><i class="fas fa-list"></i> All Applications</a>
      </nav>
    </header>

    <div class="result-card" style="display: block;">
      <div class="result-toolbar">
        <div class="badge-status">
          <i class="fas fa-check-circle"></i> VERIFIED CANDIDATE RECORD
        </div>
        <div class="toolbar-actions">
          <a href="records.php" class="btn-tool">
            <i class="fas fa-arrow-left"></i> Back to List
          </a>
          <button type="button" id="btnPrint" class="btn-tool btn-print">
            <i class="fas fa-print"></i> Print / Save PDF
          </button>
        </div>
      </div>

      <div class="document-sheet">
        <div class="doc-header">
          <div class="doc-org-info">
            <h1>Global Institute of Technology &amp; Innovation</h1>
            <p><i class="fas fa-map-marker-alt"></i> Office of Admissions &bull; Academic Year 2026-2027</p>
            <p style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">Official Confirmation of Online Candidate Registration</p>
          </div>
          <div class="doc-app-id">
            <div class="ref-title">Application Reference Number</div>
            <div class="ref-number"><?php echo htmlspecialchars($record['application_id']); ?></div>
            <div class="ref-date"><i class="far fa-clock"></i> Registered: <?php echo htmlspecialchars($record['created_at']); ?></div>
          </div>
        </div>

        <div class="doc-profile-row">
          <img src="<?php echo htmlspecialchars($record['photo_url']); ?>" alt="Applicant Photo" class="doc-avatar">
          <div class="doc-applicant-meta">
            <h2><?php echo htmlspecialchars($record['full_name']); ?></h2>
            <div class="applied-course"><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($record['course']); ?></div>
            <span class="badge-mode"><i class="fas fa-desktop"></i> <?php echo htmlspecialchars($record['study_mode']); ?></span>
          </div>
        </div>

        <div class="doc-section-heading">
          <i class="fas fa-id-card"></i> Candidate Profile &amp; Contact Details
        </div>
        <div class="doc-details-grid">
          <div class="detail-item">
            <span class="label">Full Legal Name</span>
            <span class="value"><?php echo htmlspecialchars($record['full_name']); ?></span>
          </div>
          <div class="detail-item">
            <span class="label">Email Address</span>
            <span class="value"><?php echo htmlspecialchars($record['email']); ?></span>
          </div>
          <div class="detail-item">
            <span class="label">Contact Telephone</span>
            <span class="value"><?php echo htmlspecialchars($record['phone']); ?></span>
          </div>
          <div class="detail-item">
            <span class="label">Date of Birth</span>
            <span class="value"><?php echo htmlspecialchars($record['dob']); ?></span>
          </div>
          <div class="detail-item">
            <span class="label">Gender</span>
            <span class="value"><?php echo htmlspecialchars($record['gender']); ?></span>
          </div>
          <div class="detail-item">
            <span class="label">Education Level</span>
            <span class="value"><?php echo htmlspecialchars($record['education']); ?></span>
          </div>
        </div>

        <div class="doc-section-heading">
          <i class="fas fa-map-pin"></i> Residential Address
        </div>
        <div class="doc-details-grid">
          <div class="detail-item">
            <span class="label">Street Address</span>
            <span class="value"><?php echo htmlspecialchars($record['address']); ?></span>
          </div>
          <div class="detail-item">
            <span class="label">City</span>
            <span class="value"><?php echo htmlspecialchars($record['city']); ?></span>
          </div>
          <div class="detail-item">
            <span class="label">State / Province</span>
            <span class="value"><?php echo htmlspecialchars($record['state']); ?></span>
          </div>
          <div class="detail-item">
            <span class="label">Postal / Zip Code</span>
            <span class="value"><?php echo htmlspecialchars($record['zip_code']); ?></span>
          </div>
          <div class="detail-item" style="grid-column: span 2;">
            <span class="label">Country</span>
            <span class="value"><?php echo htmlspecialchars($record['country']); ?></span>
          </div>
        </div>

        <div class="doc-section-heading">
          <i class="fas fa-code"></i> Technical Skills
        </div>
        <div>
          <?php if (!empty($record['skills'])): ?>
            <div class="tag-list">
              <?php foreach ($record['skills'] as $skill): ?>
                <span class="tag-badge"><i class="fas fa-check"></i> <?php echo htmlspecialchars($skill); ?></span>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p style="color: #64748b; font-size: 0.9rem;">None specified.</p>
          <?php endif; ?>
        </div>

        <div class="doc-section-heading">
          <i class="fas fa-quote-left"></i> Statement of Purpose
        </div>
        <div class="statement-box">
          &ldquo;<?php echo nl2br(htmlspecialchars($record['sop'])); ?>&rdquo;
        </div>

        <div class="doc-footer">
          <div>
            <div class="verification-code">
              <strong>Digital Verification Hash:</strong> <?php echo strtoupper(sha1($record['application_id'] . $record['email'])); ?><br>
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
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    $('#btnPrint').on('click', function() {
      window.print();
    });
  </script>
</body>
</html>
