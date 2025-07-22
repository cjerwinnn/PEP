<?php
include 'config/connection.php';
session_start();
if (!isset($_SESSION['employeeid'])) {
    header("Location: index.php");
    exit();
}

$user_id = isset($_SESSION['employeeid']) ? $_SESSION['employeeid'] : '';
$user_fullname = isset($_SESSION['user_fullname']) ? $_SESSION['user_fullname'] : '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$picture = isset($_SESSION['picture']) ? $_SESSION['picture'] : '';
$default_picture = 'assets/imgs/user.jpg';

function isValidBase64Image($data)
{
    return $data && base64_decode($data, true) !== false;
}

$img_src = isValidBase64Image($picture)
    ? 'data:image/jpeg;base64,' . $picture
    : $default_picture;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Hub Portal</title>
    <!-- ======= Styles ====== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/imgs/pdmc_logo_white.png" type="image/png" />
</head>

<body>
    <!-- =============== Navigation ================ -->
    <div class="app-container">
        <div class="navigation">
            <ul style="max-height: 100vh; overflow-y: auto; overflow-x: hidden;" class="list-group">
                <li>
                    <a href="#" class="d-flex align-items-center flex-nowrap text-decoration-none px-3 py-2">
                        <span class="icon me-4">
                            <img src="assets/imgs/pdmc_logo_white.png" alt="Brand Logo" style="height: 20px;">
                        </span>
                        <div class="d-flex flex-column">
                            <span class="title fw-bold text-white">Work Hub Portal</span>
                        </div>
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link" data-page="dashboard/community_wall.php">
                        <span class="ms-1 icon me-4"><ion-icon name="megaphone-outline"></ion-icon></span>
                        <span class="title">Community Wall</span>
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link" data-page="dashboard_submenu.php">
                        <span class="ms-1 icon me-4"><ion-icon name="home-outline"></ion-icon></span>
                        <span class="title">Dashboard</span>
                    </a>
                </li>

                <li>
                    <a href="#" class="nav-link" data-page="coe_submenu.php" onclick="loadPage('coe_submenu.php')">
                        <span class="ms-1 icon me-4"><ion-icon name="document-text-outline"></ion-icon></span>
                        <span class="title">C.O.E</span>
                    </a>
                </li>

                <li>
                    <a href="#" onclick="Load_ChatDesk()">
                        <span class="ms-1 icon me-4"> <ion-icon name="chatbubble-outline"></ion-icon></span>
                        <span class="title">Help Desk</span>
                    </a>
                </li>

                <input type="hidden" id="current_user" value="<?php echo htmlspecialchars($user_id); ?>" disabled>
            </ul>
        </div>

        <!-- ========================= Main ==================== -->
        <div class="main">

            <div class="topbar">
                <div class="toggle">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>

                <div class="dropdown">

                    <button class="btn dropdown-toggle border-0 bg-transparent p-0"
                        type="button"
                        id="profileDropdown"
                        data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <img src="<?php echo htmlspecialchars($img_src); ?>" alt="Profile"
                            class="rounded-circle border border-2 border-secondary"
                            style="width: 40px; height: 40px; object-fit: cover;">

                    </button>

                    <ul class="dropdown-menu dropdown-menu-end p-4 shadow-lg rounded-4 border-0"
                        aria-labelledby="profileDropdown"
                        style="min-width: 300px;">

                        <!-- Profile Info -->
                        <li class="mb-3">
                            <div class="d-flex align-items-center gap-3">
                                <!-- Optional: Add profile image -->
                                <img src="<?= htmlspecialchars($img_src); ?>" alt="Profile" width="48" height="48"
                                    class="rounded-circle border border-2 border-secondary" style="object-fit: cover;">

                                <div>
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($user_fullname) ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($user_id ?? '') ?></div>
                                    <div class="text-muted small"><?= htmlspecialchars($email ?? '') ?></div>
                                </div>
                            </div>
                        </li>

                        <li>
                            <hr class="dropdown-divider">
                        </li>

                        <!-- Account Settings -->
                        <li>
                            <a class="dropdown-item py-2 px-3 rounded-3 d-flex align-items-center gap-2 hover-bg" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#setVaultPasswordModal">
                                <i class="bi bi-gear-fill text-primary"></i>
                                <span class="fw-medium">Account Settings</span>
                            </a>
                        </li>

                        <!-- Logout -->
                        <li>
                            <a id="logout-link" class="dropdown-item py-2 px-3 rounded-3 d-flex align-items-center gap-2 text-danger hover-bg-danger" href="functions/whp_logout.php">
                                <i class="bi bi-box-arrow-right text-danger"></i>
                                <span class="fw-medium">Logout</span>
                            </a>
                        </li>
                    </ul>


                </div>
            </div>


            <!-- ========== Dynamic Content ========== -->
            <div id="main-content">
                <!-- Content like dashboard.php will load here -->
            </div>
        </div>
    </div>

    <div id="alert-placeholder" style="position: fixed; top: 10px; right: 10px; z-index: 1056;"></div>
    <div id="alert-success-placeholder" style="position: fixed; top: 10px; right: 10px; z-index: 1056;"></div>

    <?php if (!isset($_SESSION['privacy_acknowledged'])): ?>
        <script>
            window.addEventListener('load', function() {
                var modal = new bootstrap.Modal(document.getElementById('dataPrivacyModal'));
                modal.show();
            });
        </script>
    <?php endif; ?>


    <!-- Data Privacy Notice Modal -->
    <div class="modal fade" id="dataPrivacyModal" tabindex="-1"
        aria-labelledby="dataPrivacyModalLabel" aria-hidden="true"
        data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-white">
                    <h5 class="modal-title" id="dataPrivacyModalLabel">🔒 Data Privacy Notice</h5>
                </div>
                <div class="modal-body">
                    <p>PDMC values and upholds your right to data privacy. As part of our compliance with the Data Privacy Act of 2012, we ensure that all personal information you provide on this portal is collected, processed, and stored with utmost confidentiality and security.</p>
                    <ul>
                        <li>Your data will only be used for legitimate HR and administrative purposes.</li>
                        <li>We will not disclose your personal information to third parties without your consent.</li>
                        <li>You have the right to access, update, and request deletion of your data, subject to legal and contractual obligations.</li>
                    </ul>
                    <p>By using this employee portal, you acknowledge and agree to the data privacy practices outlined above.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-bs-dismiss="modal">I Acknowledge</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="setVaultPasswordModal" tabindex="-1" aria-labelledby="setVaultPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="setVaultPasswordModalLabel">Set Vault Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="setVaultPasswordForm">
                        <div class="mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="newPassword" required>
                            <div id="password-strength-meter" class="password-strength-meter"></div>
                            <div id="password-strength-text" class="form-text"></div>
                        </div>
                        <div class="mb-3">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" required>
                            <div id="password-match-text" class="form-text"></div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="showPassword">
                            <label class="form-check-label" for="showPassword">
                                Show Password
                            </label>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="savePasswordBtn">Save Password</button>
                </div>
            </div>
        </div>
    </div>


    <!-- =========== Scripts =========  -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/req_coe_list.js"></script>
    <script src="assets/js/req_coe.js"></script>
    <script src="assets/js/chat_module.js"></script>
    <script src="assets/js/functions.js"></script>
    <script src="assets/js/main.js"></script>


    <!-- ====== ionicons ======= -->
    <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>
</body>

</html>