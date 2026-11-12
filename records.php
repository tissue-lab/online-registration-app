<?php
$dataFile = __DIR__ . '/data/applications.json';
$records = [];

if (file_exists($dataFile)) {
    $content = file_get_contents($dataFile);
    if (!empty($content)) {
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $records = $decoded;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registered Candidates - NITAS Central Admissions Directory</title>
  
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <div class="app-container" style="max-width: 1160px;">
    
    <header class="app-header">
      <div class="brand-badge">
        <i class="fas fa-university"></i> Ministry of Higher Education &bull; Government of India
      </div>
      <h1 class="brand-title">Candidate Registration Directory</h1>
      <div class="brand-subtitle-hindi">राष्ट्रीय प्रौद्योगिकी एवं उन्नत अध्ययन संस्थान - प्रवेश पंजीयन विवरण</div>
      <p class="brand-subtitle">
        Centralized portal to review submitted candidate applications, filter by state, program, or name, and view official admission slips.
      </p>
      
      <nav class="nav-links">
        <a href="index.php"><i class="fas fa-plus-circle"></i> New Application Form</a>
        <a href="records.php" class="active"><i class="fas fa-sync"></i> Refresh Directory</a>
      </nav>
    </header>

    <div class="form-card">
      <div class="form-header-bar">
        <h2><i class="fas fa-users"></i> Registered Applicants (<?php echo count($records); ?>)</h2>
        <div>
          <input type="text" id="searchBox" class="form-control" placeholder="Search candidate, state, PIN, course..." style="font-size: 0.85rem; padding: 6px 12px; width: 280px; background: #ffffff;">
        </div>
      </div>

      <div class="form-body" style="padding: 20px;">
        <?php if (empty($records)): ?>
          <div style="text-align: center; padding: 50px 20px; color: var(--text-muted);">
            <i class="fas fa-folder-open" style="font-size: 3rem; margin-bottom: 12px; opacity: 0.4;"></i>
            <h3>No Candidate Applications Found</h3>
            <p>Submit the admission form to populate candidate entries in this directory.</p>
            <a href="index.php" class="btn btn-primary" style="margin-top: 15px;">
              <i class="fas fa-arrow-right"></i> Open Application Form
            </a>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="records-table" id="recordsTable">
              <thead>
                <tr>
                  <th>Photo</th>
                  <th>Roll / Ref No.</th>
                  <th>Candidate Name</th>
                  <th>Program Applied</th>
                  <th>Location &amp; PIN</th>
                  <th>Submission Date</th>
                  <th style="text-align: right;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($records as $row): ?>
                  <tr>
                    <td>
                      <img src="<?php echo htmlspecialchars($row['photo_url']); ?>" alt="Photo" style="width: 38px; height: 46px; border-radius: 4px; object-fit: cover; border: 1px solid #cbd5e1;">
                    </td>
                    <td>
                      <strong style="font-family: monospace; color: var(--primary); font-size: 0.9rem;">
                        <?php echo htmlspecialchars($row['application_id']); ?>
                      </strong>
                    </td>
                    <td>
                      <strong><?php echo htmlspecialchars($row['full_name']); ?></strong><br>
                      <small style="color: var(--primary); font-weight: 600;"><?php echo htmlspecialchars($row['phone']); ?></small><br>
                      <small style="color: var(--text-muted);"><?php echo htmlspecialchars($row['email']); ?></small>
                    </td>
                    <td>
                      <span style="font-weight: 600; font-size: 0.88rem; color: #1e293b;"><?php echo htmlspecialchars($row['course']); ?></span><br>
                      <small style="color: var(--text-muted);"><?php echo htmlspecialchars($row['study_mode']); ?></small>
                    </td>
                    <td>
                      <strong><?php echo htmlspecialchars($row['city'] . ', ' . $row['state']); ?></strong><br>
                      <span style="font-family: monospace; font-size: 0.85rem; color: var(--accent); font-weight: 700;">
                        PIN: <?php echo htmlspecialchars($row['zip_code']); ?>
                      </span>
                    </td>
                    <td><small><?php echo htmlspecialchars($row['created_at']); ?></small></td>
                    <td style="text-align: right;">
                      <a href="display.php?id=<?php echo urlencode($row['application_id']); ?>" class="btn-tool" style="background: var(--primary); padding: 5px 12px;">
                        <i class="fas fa-eye"></i> View Slip
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script>
    $('#searchBox').on('keyup', function() {
      const val = $(this).val().toLowerCase();
      $('#recordsTable tbody tr').filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
      });
    });
  </script>
</body>
</html>
