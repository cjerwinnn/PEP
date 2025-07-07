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
    </style>
</head>

<body>
    <div id="main-content" class="container-fluid mb-2">

        <div class="board-container">
            <div class="header">
                <h1>📌 Employee Bulletin Board</h1>
                <p>Company memos and announcements</p>
            </div>

            <div id="bulletins">
                <!-- Bulletins will load here -->
            </div>
        </div>
    </div>

    <script>
        const bulletins = [{
                title: "🛠️ System Maintenance",
                message: "Our internal system will be under maintenance on July 10 from 12AM to 4AM. Please save your work early.",
                date: "July 7, 2025"
            },
            {
                title: "🎉 Team Building",
                message: "Company team building will be held at Caliraya on July 20. Wear your company shirt!",
                date: "July 6, 2025"
            },
            {
                title: "📢 Policy Update",
                message: "The updated remote work policy is now effective starting July 15. Check your emails for full details.",
                date: "July 5, 2025"
            }
        ];

        const container = document.getElementById('bulletins');

        bulletins.forEach(b => {
            const bulletinDiv = document.createElement('div');
            bulletinDiv.className = 'bulletin';
            bulletinDiv.innerHTML = `
        <h2>${b.title} <time>${b.date}</time></h2>
        <p>${b.message}</p>
      `;
            container.appendChild(bulletinDiv);
        });
    </script>

</body>

</html>