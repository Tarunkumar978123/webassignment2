<?php
// submit.php
// Simple secure-ish server-side handling and display.
// Make sure uploads/ is writable by the webserver and exists.

function clean($v) {
    return htmlspecialchars(trim($v), ENT_QUOTES, 'UTF-8');
}

// Server-side validation
$errors = [];

// Required fields
$fullname = isset($_POST['fullname']) ? clean($_POST['fullname']) : '';
$email    = isset($_POST['email']) ? clean($_POST['email']) : '';
$phone    = isset($_POST['phone']) ? preg_replace('/\D+/', '', $_POST['phone']) : '';
$dob      = isset($_POST['dob']) ? clean($_POST['dob']) : '';
$gender   = isset($_POST['gender']) ? clean($_POST['gender']) : 'Not specified';
$address  = isset($_POST['address']) ? clean($_POST['address']) : '';
$course   = isset($_POST['course']) ? clean($_POST['course']) : '';

if ($fullname === '' || strlen($fullname) < 3) $errors[] = 'Full name is required (min 3 chars).';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if ($phone === '' || !preg_match('/^[6-9]\d{9}$/', $phone)) $errors[] = 'Valid 10-digit mobile number is required.';
if ($course === '') $errors[] = 'Course selection is required.';

// Handle file upload (optional)
$uploadDir = __DIR__ . '/uploads/';
$photoUrl = null;
if (!is_dir($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}
if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['photo'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'File upload error.';
    } else {
        $maxBytes = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxBytes) {
            $errors[] = 'Uploaded image must be <= 2MB.';
        } else {
            // Validate mime/type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
            if (!array_key_exists($mime, $allowed)) {
                $errors[] = 'Only JPG, PNG or GIF image files allowed.';
            } else {
                $ext = $allowed[$mime];
                $safeName = preg_replace('/[^a-z0-9_\-]/i', '_', strtolower(pathinfo($file['name'], PATHINFO_FILENAME)));
                $target = $uploadDir . $safeName . '_' . time() . '.' . $ext;
                if (!move_uploaded_file($file['tmp_name'], $target)) {
                    $errors[] = 'Failed to move uploaded file.';
                } else {
                    // make a web-accessible URL (assumes uploads/ is web-accessible)
                    $photoUrl = 'uploads/' . basename($target);
                }
            }
        }
    }
}

// If errors, show them (you can redirect back in production)
if (!empty($errors)) {
    echo "<!doctype html><html><head><meta charset='utf-8'><title>Submission - Error</title>";
    echo "<link rel='stylesheet' href='css/style.css'>";
    echo "</head><body><main class='container'><div class='form-card'>";
    echo "<h2 style='color:#ef4444'>Submission failed</h2><ul>";
    foreach ($errors as $err) {
        echo "<li style='color:#c92a2a;margin-bottom:6px;'>" . clean($err) . "</li>";
    }
    echo "</ul>";
    echo "<p><a href='index.html' class='btn light'>Go back to form</a></p>";
    echo "</div></main></body></html>";
    exit;
}

// All good — display the formatted output
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8" />
  <title>Submission Successful</title>
  <link rel="stylesheet" href="css/style.css" />
  <style>
    .display-card{
      margin-top:18px;
      padding:20px;
      border-radius:12px;
      box-shadow:0 6px 20px rgba(2,6,23,0.06);
      display:flex;
      gap:18px;
      align-items:flex-start;
      background: #fff;
    }
    .photo{
      width:140px;height:140px;border-radius:10px;overflow:hidden;border:1px solid #eee;
      display:flex;align-items:center;justify-content:center;background:#f8fafc;
    }
    .photo img{width:100%;height:100%;object-fit:cover}
    .info{flex:1}
    .info h2{margin:0 0 8px}
    .info .row{margin-bottom:8px}
    .label{color:#475569;font-weight:700;margin-right:6px}
    .value{color:#0f172a}
    .success{color: #0f5132;background:#ecfdf5;padding:8px;border-radius:8px;display:inline-block;margin-bottom:12px}
  </style>
</head>
<body>
  <main class="container">
    <header class="header">
      <h1>Registration Received</h1>
      <p class="subtitle">Thank you — submission successful.</p>
    </header>

    <div class="display-card">
      <div class="photo">
        <?php if ($photoUrl): ?>
          <img src="<?php echo htmlspecialchars($photoUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="Uploaded photo" />
        <?php else: ?>
          <div style="text-align:center;color:#94a3b8;font-size:0.95rem;padding:8px">No Photo</div>
        <?php endif; ?>
      </div>

      <div class="info">
        <div class="success">✔ Submission successful</div>
        <h2><?php echo $fullname; ?></h2>

        <div class="row"><span class="label">Email:</span> <span class="value"><?php echo $email; ?></span></div>
        <div class="row"><span class="label">Mobile:</span> <span class="value"><?php echo $phone; ?></span></div>
        <div class="row"><span class="label">DOB:</span> <span class="value"><?php echo ($dob ?: 'Not provided'); ?></span></div>
        <div class="row"><span class="label">Gender:</span> <span class="value"><?php echo $gender; ?></span></div>
        <div class="row"><span class="label">Course:</span> <span class="value"><?php echo $course; ?></span></div>
        <div class="row"><span class="label">Address:</span> <span class="value"><?php echo ($address ?: 'Not provided'); ?></span></div>

        <p style="margin-top:12px">
          <a href="index.html" class="btn light">Submit another response</a>
          <button onclick="window.print()" class="btn primary" style="margin-left:8px">Print</button>
        </p>
      </div>
    </div>

    <footer class="footer" style="margin-top:16px">
      <small>Data shown above is what was submitted. In a production app you'd store these in a database.</small>
    </footer>
  </main>
</body>
</html>
