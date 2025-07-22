<?php
session_start();
if (isset($_SESSION['employeeid'])) {
  header('Location: main.php');
  exit();
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>HRIS Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body class="bg-light d-flex justify-content-center align-items-center vh-100">

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-8 col-md-6 col-lg-4">
        <div class="card shadow rounded-4 p-4 text-center">
          <!-- Logo -->
          <img src="assets/imgs/pdmc_logo.png" alt="Company Logo" class="mb-4 mx-auto" style="max-width: 150px; height: auto;" />

          <h4 class="mb-4">Login</h4>

          <form action="functions/whp_login.php" method="POST" novalidate>
            <div class="mb-3 text-start">
              <label for="username" class="form-label small">Username</label>
              <input type="text" class="form-control rounded-4" id="username" name="username" value="" required />
            </div>
            <div class="mb-3 text-start">
              <label for="password" class="form-label small">Password</label>
              <input type="password" class="form-control rounded-4" id="password" name="password" value="" required />
            </div>

            <?php if (isset($_GET['error'])): ?>
              <div class="alert alert-danger small rounded-4">
                <?php
                if ($_GET['error'] === 'wrongpass') echo "Incorrect password.";
                elseif ($_GET['error'] === 'nouser') echo "Username not found.";
                else echo "Login failed.";
                ?>
              </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-primary w-100 rounded-4">Login</button>
          </form>
        </div>
      </div>
    </div>
  </div>

</body>

</html>