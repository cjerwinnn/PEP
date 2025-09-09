<?php
session_start();
if (!isset($_SESSION['employeeid'])) {
    header("Location: ../index.php");
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
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="../assets/imgs/pdmc_logo_white.png" type="image/png" />
</head>

<body>
    <div class="app-container">
        <div class="navigation">
            <ul style="max-height: 100vh; overflow-y: auto; overflow-x: hidden;" class="list-group">
                <li>
                    <a href="#" class="d-flex align-items-center flex-nowrap text-decoration-none px-3 py-2">
                        <span class="icon me-4">
                            <img src="../assets/imgs/pdmc_logo_white.png" alt="Brand Logo" style="height: 20px;">
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
                    <a href="../pages/coe_submenu.php" class="nav-link">
                        <span class="ms-1 icon me-4"><ion-icon name="document-text-outline"></ion-icon></span>
                        <span class="title">C.O.E</span>
                    </a>
                </li>

                <li>
                    <a href="wtm_submenu.php" class="nav-link">
                        <span class="ms-1 icon me-4"><ion-icon name="time-outline"></ion-icon></span>
                        <span class="title">WTM</span>
                    </a>
                </li>

                <li>
                    <a href="#" onclick="Load_ChatDesk()">
                        <span class="ms-1 icon me-4"> <ion-icon name="chatbubble-outline"></ion-icon></span>
                        <span class="title">Help Desk</span>
                    </a>
                </li>

                <li class="mt-4">
                    <a href="maintenance_submenu.php" class="nav-link" data-page="" onclick="loadPage('maintenance_submenu.php')">
                        <span class="ms-1 icon me-4"><ion-icon name="settings-outline"></ion-icon></span>
                        <span class="title">Maintenance</span>
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
                                    <input type="hidden" id="employeeId" value="<?= htmlspecialchars($user_id ?? '') ?>">
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
                            <a id="logout-link" class="dropdown-item py-2 px-3 rounded-3 d-flex align-items-center gap-2 text-danger hover-bg-danger" href="../functions/whp_logout.php">
                                <i class="bi bi-box-arrow-right text-danger"></i>
                                <span class="fw-medium">Logout</span>
                            </a>
                        </li>
                    </ul>

                </div>
            </div>