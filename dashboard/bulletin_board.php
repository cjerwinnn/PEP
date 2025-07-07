<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Employee Bulletin Board</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f8;
            margin: 0;
            padding: 20px;
        }

        .board-container {
            max-width: 900px;
            margin: auto;
            background: #ffffff;
            padding: 30px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            margin-bottom: 5px;
            color: #333;
        }

        .bulletin {
            border-left: 6px solid #007BFF;
            background-color: #f9f9f9;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            position: relative;
        }

        .bulletin h2 {
            margin: 0;
            font-size: 1.2em;
            color: #222;
        }

        .bulletin p {
            margin-top: 8px;
            color: #555;
        }

        .bulletin time {
            font-size: 0.9em;
            color: #888;
            float: right;
        }

        .view-pdf-btn {
            margin-top: 10px;
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: rgba(0, 0, 0, 0.7);
        }

        .modal-content {
            position: relative;
            margin: 5% auto;
            width: 80%;
            height: 80%;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        .modal-content iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .close-btn {
            position: absolute;
            top: 8px;
            right: 12px;
            font-size: 24px;
            color: #aaa;
            cursor: pointer;
        }

        .close-btn:hover {
            color: #000;
        }
    </style>
</head>

<body>
    <!-- Start of Bulletin Board -->
    <div class="row">
        <div class="col-12">
            <h3 class="mt-3">📢 Employee Bulletin Board</h3>
        </div>

        <!-- Announcements -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">📌 Announcements</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">Pasig Day on July 2, 2025 🎉.</li>
                    <li class="list-group-item">System maintenance scheduled for July 10 (12AM–4AM)</li>
                </ul>
            </div>
        </div>

        <!-- Memos as List with Modal Trigger -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-warning">📝 Memos</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <p class="mb-1"><strong>Subject:</strong> Process Time of Employee Documents Request</p>
                        <p class="mb-1 text-muted"><small>Posted by: HR Department</small></p>
                        <button class="btn btn-sm btn-outline-secondary view-memo-btn" data-bs-toggle="modal" data-bs-target="#memoModal" data-pdf="../uploads/Memos/HR MEMO NO.222.pdf">View Memo (PDF)</button>
                    </li>
                    <li class="list-group-item">
                        <p class="mb-1"><strong>Subject:</strong> New Dress Code Policy</p>
                        <p class="mb-1">A new dress code policy will take effect starting August 1, 2025.</p>
                        <p class="mb-1 text-muted"><small>Posted by: Admin Office</small></p>
                        <button class="btn btn-sm btn-outline-secondary view-memo-btn" data-bs-toggle="modal" data-bs-target="#memoModal" data-pdf="uploads/Memos/memo-dresscode.pdf">View Memo (PDF)</button>
                    </li>
                    <li class="list-group-item">
                        <p class="mb-1"><strong>Subject:</strong> Office Sanitation Schedule</p>
                        <p class="mb-1">General cleaning of all departments will be conducted every Friday afternoon.</p>
                        <p class="mb-1 text-muted"><small>Posted by: Facilities Management</small></p>
                        <button class="btn btn-sm btn-outline-secondary view-memo-btn" data-bs-toggle="modal" data-bs-target="#memoModal" data-pdf="uploads/Memos/memo-cleaning.pdf">View Memo (PDF)</button>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Events -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">📅 Upcoming Events</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">July 10: Cybersecurity Awareness Webinar</li>
                    <li class="list-group-item">July 25: Team Building @ Eco Park 🌿</li>
                </ul>
            </div>
        </div>

        <!-- Birthday Celebrants -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">🎂 Birthday Celebrants for the Month of July.</div>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <strong>Carlos Jerwin Suarez</strong> — July 8
                        <span class="text-muted d-block"><small>IT Department</small></span>
                    </li>
                    <li class="list-group-item">
                        <strong>Maria Santos</strong> — July 12
                        <span class="text-muted d-block"><small>Human Resources</small></span>
                    </li>
                    <li class="list-group-item">
                        <strong>John dela Cruz</strong> — July 25
                        <span class="text-muted d-block"><small>Finance Department</small></span>
                    </li>
                </ul>
            </div>
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

</body>

</html>