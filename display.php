<?php
$id = $_GET['id'] ?? '';
$id = preg_replace('/[^A-Za-z0-9\/\-]/', '', $id);

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
    die("<h3>Registration Record Not Found</h3><p>No admission application found matching ID: " . htmlspecialchars($id) . "</p><p><a href='index.php'>Return to Application Portal</a></p>");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admission Slip - <?php echo htmlspecialchars($record['application_id']); ?></title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <div class="app-container">
    <header class="app-header">
      <div class="brand-badge">
        <i class="fas fa-university"></i> Ministry of Higher Education &bull; Government of India
      </div>
      <h1 class="brand-title">National Institute of Technology &amp; Advanced Studies</h1>
      <div class="brand-subtitle-hindi">राष्ट्रीय प्रौद्योगिकी एवं उन्नत अध्ययन संस्थान</div>
      
      <nav class="nav-links">
        <a href="index.php"><i class="fas fa-edit"></i> New Application</a>
        <a href="records.php"><i class="fas fa-list"></i> Candidate Directory</a>
      </nav>
    </header>

    <div class="result-card" style="display: block;">
      <div class="result-toolbar">
        <div class="badge-status">
          <i class="fas fa-check-circle"></i> VERIFIED ADMISSION RECORD
        </div>
        <div class="toolbar-actions">
          <a href="records.php" class="btn-tool">
            <i class="fas fa-arrow-left"></i> Back to Directory
          </a>
          <button type="button" id="btnPrint" class="btn-tool btn-print">
            <i class="fas fa-print"></i> Print / Save PDF
          </button>
        </div>
      </div>

      <div class="document-sheet">
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

        <div class="doc-meta-bar">
          <div class="ref-box">
            <span class="ref-label">Application Roll / Ref No:</span>
            <span class="ref-value"><?php echo htmlspecialchars($record['application_id']); ?></span>
          </div>
          <div class="ref-date">
            <i class="far fa-clock"></i> <strong>Filing Timestamp:</strong> <?php echo htmlspecialchars($record['created_at']); ?>
          </div>
        </div>

        <div class="doc-profile-row">
          <img src="<?php echo htmlspecialchars($record['photo_url']); ?>" alt="Candidate Photograph" class="doc-avatar">
          <div class="doc-applicant-meta">
            <h2><?php echo htmlspecialchars($record['full_name']); ?></h2>
            <div class="applied-course"><i class="fas fa-graduation-cap"></i> <?php echo htmlspecialchars($record['course']); ?></div>
            <span class="badge-mode"><i class="fas fa-university"></i> <?php echo htmlspecialchars($record['study_mode']); ?></span>
            <div style="font-size: 0.82rem; color: #475569; margin-top: 6px;">
              <strong>Category:</strong> <?php echo htmlspecialchars($record['category'] ?? 'General'); ?>
            </div>
          </div>
        </div>

        <div class="doc-section-heading">
          <i class="fas fa-id-card"></i> Candidate Personal &amp; Communication Details
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
            <span class="label">Primary Indian Mobile</span>
            <span class="value" style="color: var(--primary); font-weight: 700;"><?php echo htmlspecialchars($record['phone']); ?></span>
          </div>
          <div class="detail-item">
            <span class="label">Date of Birth (DD/MM/YYYY)</span>
            <span class="value"><?php echo htmlspecialchars($record['dob']); ?></span>
          </div>
          <div class="detail-item">
            <span class="label">Gender Identification</span>
            <span class="value"><?php echo htmlspecialchars($record['gender']); ?></span>
          </div>
          <div class="detail-item">
            <span class="label">Qualifying Examination</span>
            <span class="value"><?php echo htmlspecialchars($record['education']); ?></span>
          </div>
        </div>

        <div class="doc-section-heading">
          <i class="fas fa-map-marked-alt"></i> Permanent Residential Address in India
        </div>
        <div class="doc-details-grid">
          <div class="detail-item" style="grid-column: span 2;">
            <span class="label">Street / House Address</span>
            <span class="value"><?php echo htmlspecialchars($record['address']); ?></span>
          </div>
          <div class="detail-item">
            <span class="label">City / District</span>
            <span class="value"><?php echo htmlspecialchars($record['city']); ?></span>
          </div>
          <div class="detail-item">
            <span class="label">State / Union Territory</span>
            <span class="value"><?php echo htmlspecialchars($record['state']); ?></span>
          </div>
          <div class="detail-item">
            <span class="label">Postal PIN Code</span>
            <span class="value" style="font-family: monospace; font-size: 1.05rem; font-weight: 800; color: var(--accent);">
              <?php echo htmlspecialchars($record['zip_code']); ?>
            </span>
          </div>
          <div class="detail-item">
            <span class="label">Country</span>
            <span class="value">India (IN)</span>
          </div>
        </div>

        <div class="doc-section-heading">
          <i class="fas fa-laptop-code"></i> Technical Specializations
        </div>
        <div>
          <?php if (!empty($record['skills'])): ?>
            <div class="tag-list">
              <?php foreach ($record['skills'] as $skill): ?>
                <span class="tag-badge"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($skill); ?></span>
              <?php endforeach; ?>
            </div>
          <?php else: ?>
            <p style="color: #64748b; font-size: 0.88rem;">None specified.</p>
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
              <strong>Digital Security Hash:</strong> <?php echo strtoupper(sha1($record['application_id'] . $record['email'])); ?><br>
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
