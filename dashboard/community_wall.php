               <?php

                include '../config/connection.php';
                session_start();

                // Check if form was submitted
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acknowledge'])) {
                    $_SESSION['privacy_acknowledged'] = true;
                    // Redirect to same page to prevent resubmission
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                }

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Get current month
                $curMonth = date('n'); // returns 1-12

                // Call stored procedure
                $sql = "CALL COMMUNITY_WALL_BIRTHDAYS(?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $curMonth);
                $stmt->execute();
                $result = $stmt->get_result();

                ?>

               <style>
                   .header {
                       text-align: center;
                       margin-bottom: 40px;
                   }

                   .header h1 {
                       margin-bottom: 5px;
                       color: #2d3a4b;
                       font-weight: 700;
                       letter-spacing: 1px;
                   }

                   .section-title {
                       font-size: 1.2rem;
                       font-weight: 600;
                       letter-spacing: 0.5px;
                   }

                   .card {
                       border-radius: 14px;
                       box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
                       border: none;
                   }

                   .card-header {
                       border-radius: 14px 14px 0 0;
                       font-size: 1.1rem;
                       font-weight: 600;
                   }

                   .list-group-item {
                       border: none;
                       border-bottom: 1px solid #f0f0f0;
                       background: transparent;
                   }

                   .list-group-item:last-child {
                       border-bottom: none;
                   }

                   .view-memo-btn {
                       margin-top: 8px;
                       background: linear-gradient(90deg, #6366f1 0%, #60a5fa 100%);
                       color: #fff;
                       border: none;
                       border-radius: 6px;
                       padding: 6px 18px;
                       font-size: 0.98rem;
                       font-weight: 500;
                       box-shadow: 0 2px 8px rgba(99, 102, 241, 0.08);
                       transition: background 0.2s, box-shadow 0.2s;
                   }

                   .view-memo-btn:hover {
                       background: linear-gradient(90deg, #4f46e5 0%, #2563eb 100%);
                       color: #fff;
                       box-shadow: 0 4px 16px rgba(99, 102, 241, 0.13);
                   }

                   .modal-content {
                       border-radius: 16px;
                   }

                   .modal-header {
                       border-radius: 16px 16px 0 0;
                       background: #f1f5f9;
                   }

                   .modal-title {
                       font-weight: 600;
                       color: #374151;
                   }

                   .modal-body {
                       background: #f8fafc;
                   }

                   .card-header.bg-primary {
                       background: linear-gradient(90deg, #6366f1 0%, #60a5fa 100%) !important;
                   }

                   .card-header.bg-warning {
                       background: linear-gradient(90deg, #fbbf24 0%, #f59e42 100%) !important;
                       color: #fff !important;
                   }

                   .card-header.bg-info {
                       background: linear-gradient(90deg, #38bdf8 0%, #0ea5e9 100%) !important;
                       color: #fff !important;
                   }

                   .card-header.bg-success {
                       background: linear-gradient(90deg, #34d399 0%, #059669 100%) !important;
                       color: #fff !important;
                   }

                   .celebrant-photo {
                       width: 48px;
                       height: 48px;
                       border-radius: 50%;
                       object-fit: cover;
                       border: 2px solid #dee2e6;
                   }


                   @media (max-width: 991px) {
                       .board-container {
                           padding: 18px 4vw 18px 4vw;
                       }
                   }

                   @media (max-width: 767px) {
                       .board-container {
                           padding: 8px 0 8px 0;
                       }
                   }
               </style>

               <div id="main-content" class="container-fluid mb-2">
                   <div class="header">
                       <h1>📢 Community Wall</h1>
                       <div class="text-secondary mb-2" style="font-size:1.1rem;">Stay updated with the latest news, memos, and events!</div>
                   </div>
                   <div class="row g-4">
                       <!-- Announcements -->
                       <div class="col-lg-6 mb-4">
                           <div class="card">
                               <div class="card-header bg-primary text-white section-title">📌 Announcements</div>
                               <ul class="list-group list-group-flush">
                                   <li class="list-group-item">Pasig Day on July 2, 2025 🎉.</li>
                                   <li class="list-group-item">System maintenance scheduled for July 10 (12AM–4AM)</li>
                               </ul>
                           </div>
                       </div>
                       <!-- Memos as List with Modal Trigger -->
                       <div class="col-lg-6 mb-4">
                           <div class="card">
                               <div class="card-header bg-warning section-title">📝 Memos</div>
                               <ul class="list-group list-group-flush">
                                   <li class="list-group-item">
                                       <p class="mb-1 fw-semibold"><strong>Subject:</strong> Process Time of Employee Documents Request</p>
                                       <p class="mb-1 text-muted"><small>Posted by: HR Department</small></p>
                                       <a class="view-memo-btn" href="uploads/Memos/HR_MEMO_NO_222.pdf" target="_blank">View Memo (PDF)</a>
                                   </li>
                                   <li class="list-group-item">
                                       <p class="mb-1 fw-semibold"><strong>Subject:</strong> New Dress Code Policy</p>
                                       <p class="mb-1">A new dress code policy will take effect starting August 1, 2025.</p>
                                       <p class="mb-1 text-muted"><small>Posted by: Admin Office</small></p>
                                       <a class="view-memo-btn" href="uploads/Memos/memo-dresscode.pdf" target="_blank">View Memo (PDF)</a>
                                   </li>
                                   <li class="list-group-item">
                                       <p class="mb-1 fw-semibold"><strong>Subject:</strong> Office Sanitation Schedule</p>
                                       <p class="mb-1">General cleaning of all departments will be conducted every Friday afternoon.</p>
                                       <p class="mb-1 text-muted"><small>Posted by: Facilities Management</small></p>
                                       <a class="view-memo-btn" href="uploads/Memos/memo-cleaning.pdf" target="_blank">View Memo (PDF)</a>
                                   </li>
                               </ul>
                           </div>
                       </div>
                       <!-- Events -->
                       <div class="col-lg-6 mb-4">
                           <div class="card">
                               <div class="card-header bg-info text-white section-title">📅 Upcoming Events</div>
                               <ul class="list-group list-group-flush">
                                   <li class="list-group-item">July 10: Cybersecurity Awareness Webinar</li>
                               </ul>
                           </div>
                       </div>


                       <!-- Birthday Celebrants -->
                       <div class="col-lg-6 mb-4">
                           <div class="card">
                               <div class="card-header bg-success text-white section-title">
                                   🎂 Birthday Celebrants for the Month of <?php echo date('F'); ?>
                               </div>
                               <!-- Add max-height and scroll -->
                               <ul class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                                   <?php while ($row = $result->fetch_assoc()): ?>
                                       <?php
                                        $hasPhoto = !empty($row['picture']); // check if BLOB is not null or empty

                                        if ($hasPhoto) {
                                            $imgData = base64_encode($row['picture']); // BLOB to base64
                                            $imgSrc = "data:image/jpeg;base64,{$imgData}"; // adjust to PNG if needed
                                        } else {
                                            $imgSrc = "assets/imgs/user_default.png"; // path to default image
                                        }
                                        ?>

                                       <li class="list-group-item d-flex justify-content-between align-items-start">
                                           <div class="d-flex">
                                               <img src="<?php echo $imgSrc; ?>" class="celebrant-photo me-3" alt="Photo">
                                               <div>
                                                   <strong>
                                                       <?php
                                                        echo $row['lastname'] . ', ' . $row['firstname'] . ' ' . $row['middlename'];
                                                        if ($row['suffix'] != 'NOT APPLICABLE') echo ' ' . $row['suffix'];
                                                        ?>
                                                   </strong> —
                                                   <span class="text-end"><?php echo date('F j', strtotime($row['birthdate'])); ?></span>
                                                   <span class="text-muted d-block">
                                                       <small><?php echo htmlspecialchars($row['area']); ?> - <?php echo htmlspecialchars($row['position']); ?></small>
                                                   </span>
                                               </div>
                                           </div>

                                           <!-- Greet Button -->
                                           <button class="btn btn-success btn-sm greet-btn" data-name="<?php echo $row['firstname']; ?>">
                                               🎉 Greet
                                           </button>
                                       </li>

                                   <?php endwhile; ?>
                               </ul>
                           </div>
                       </div>


                       <?php
                        $stmt->close();
                        $conn->close();
                        ?>

                   </div>
               </div>

               <!-- Memo Modal -->
               <div class="modal fade" id="memoModal" tabindex="-1" aria-labelledby="memoModalLabel" aria-hidden="true">
                   <div class="modal-dialog modal-xl modal-dialog-scrollable">
                       <div class="modal-content">
                           <div class="modal-header">
                               <h5 class="modal-title" id="memoModalLabel">📄 Memo Viewer</h5>
                               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                           </div>
                           <div class="modal-body" style="height: 80vh;">
                               <iframe id="memoFrame" src="" width="100%" height="100%" style="border: none;"></iframe>
                           </div>
                       </div>
                   </div>
               </div>