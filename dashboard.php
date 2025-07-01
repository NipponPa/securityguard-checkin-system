<?php
require_once 'db_connect.php'; // Include your database connection file

$filterDate = ''; // Changed to a single date variable
$submissions = [];
$message = '';

// Check if date filter is applied
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['filter_date']) && !empty($_GET['filter_date'])) {
    $filterDate = $_GET['filter_date'];

    // Build the WHERE clause for single date filtering
    // We check if the submission time falls within the selected day (from 00:00:00 to 23:59:59)
    $sql = "SELECT id, location_name, time_of_submission, image1 FROM submissions WHERE time_of_submission >= ? AND time_of_submission <= ? ORDER BY time_of_submission DESC";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $message = "Database query preparation failed: " . $conn->error;
    } else {
        $startDate = $filterDate . " 00:00:00"; // Start of the selected day
        $endDate = $filterDate . " 23:59:59";   // End of the selected day
        $stmt->bind_param("ss", $startDate, $endDate); // Bind two string parameters
        
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $submissions[] = $row;
            }
        } else {
            $message = "ไม่พบข้อมูลในวันที่เลือก (" . htmlspecialchars($filterDate) . ").";
        }
        $stmt->close();
    }
} else {
    // Default: Fetch all submissions if no filter is applied or filter is empty
    $sql = "SELECT id, location_name, time_of_submission, image1 FROM submissions ORDER BY time_of_submission DESC";
    $result = $conn->query($sql);

    if ($result === false) {
        $message = "Database query failed: " . $conn->error;
    } elseif ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $submissions[] = $row;
        }
    } else {
        $message = "No submissions found in the database.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 20px;
            color: #3c4043;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            padding: 30px;
        }
        h1 {
            text-align: center;
            color: #202124;
            margin-bottom: 30px;
            font-size: 2.2rem;
            font-weight: 700;
        }
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 30px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #dadce0;
            align-items: flex-end;
            justify-content: center; /* Center form elements */
        }
        .filter-form label {
            font-weight: 500;
            color: #5f6368;
            margin-bottom: 5px;
            display: block;
        }
        .filter-form input[type="date"] {
            padding: 10px 12px;
            border: 1px solid #dadce0;
            border-radius: 6px;
            font-size: 1rem;
            flex-grow: 1;
            min-width: 150px;
        }
        .filter-form button {
            padding: 10px 20px;
            background-color: #4285F4; /* Google Blue */
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .filter-form button:hover {
            background-color: #3367D6; /* Darker Blue */
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .filter-form button:active {
            transform: translateY(1px);
        }

        .message {
            text-align: center;
            padding: 15px;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .submissions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); /* Responsive grid */
            gap: 25px;
        }
        .submission-card {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .submission-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        .submission-card img {
            width: 100%;
            height: 200px; /* Fixed height for consistency */
            object-fit: cover; /* Cover the area, cropping if necessary */
            display: block;
            border-bottom: 1px solid #eee;
        }
        .submission-info {
            padding: 15px;
        }
        .submission-info p {
            margin: 0 0 8px 0;
            font-size: 0.95rem;
            line-height: 1.4;
        }
        .submission-info strong {
            color: #202124;
            font-weight: 600;
        }
        .submission-info .location-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #4285F4; /* Google Blue for location name */
            margin-bottom: 10px;
        }
        .download-button {
            display: block; /* Make it a block-level element */
            width: calc(100% - 30px); /* Adjust width to account for padding */
            margin: 10px auto 0; /* Center and add top margin */
            padding: 8px 15px;
            background-color: #34A853; /* Google Green */
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 6px;
            font-size: 0.9rem;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .download-button:hover {
            background-color: #2E8B57; /* Darker Green */
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .download-button:active {
            transform: translateY(1px);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-form input[type="date"],
            .filter-form button {
                width: 100%;
                min-width: unset;
            }
            .download-button {
                width: calc(100% - 30px); /* Maintain width adjustment for smaller screens */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Admin Dashboard</h1>

        <form class="filter-form" method="GET" action="dashboard.php">
            <div>
                <label for="filter_date">เลือกวันที่:</label>
                <input type="date" id="filter_date" name="filter_date" value="<?php echo htmlspecialchars($filterDate); ?>">
            </div>
            <button type="submit">ค้นหาตามวันที่</button>
            <!-- Removed Clear Filter Button -->
        </form>

        <?php if ($message): ?>
            <div class="message"><?php echo $message; ?></div>
        <?php endif; ?>

        <?php if (!empty($submissions)): ?>
            <div class="submissions-grid">
                <?php foreach ($submissions as $submission): ?>
                    <div class="submission-card">
                        <?php if (!empty($submission['image1'])): ?>
                            <img src="data:image/jpeg;base64,<?php echo base64_encode($submission['image1']); ?>" alt="Uploaded Image">
                        <?php else: ?>
                            <img src="https://placehold.co/600x400/e0e0e0/555555?text=No+Image" alt="No Image Available">
                        <?php endif; ?>
                        <div class="submission-info">
                            <p class="location-name"><strong><?php echo htmlspecialchars($submission['location_name']); ?></strong></p>
                            <p><strong>ID:</strong> <?php echo htmlspecialchars($submission['id']); ?></p>
                            <p><strong>เวลา:</strong> <?php echo htmlspecialchars($submission['time_of_submission']); ?></p>
                            <!-- Download Image Button -->
                            <?php if (!empty($submission['image1'])): ?>
                                <a href="data:image/jpeg;base64,<?php echo base64_encode($submission['image1']); ?>" 
                                   download="<?php echo htmlspecialchars($submission['location_name'] . '_' . $submission['id'] . '.jpg'); ?>" 
                                   class="download-button">
                                    	ดาวน์โหลด รูปภาพ
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php elseif (!$message): ?>
            <div class="message">No submissions to display.</div>
        <?php endif; ?>
    </div>
</body>
</html>
