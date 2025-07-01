<?php
require_once 'db_connect.php';

$locationName = "N/A";
$timeOfSubmission = "N/A";
$imageData = null;
$message = "";

if (isset($_GET['id'])) {
    $submissionId = intval($_GET['id']); // Sanitize the ID

    // Prepare and execute the query to fetch the specific submission
    // Ensure 'image1' is included in the SELECT statement
    $stmt = $conn->prepare("SELECT location_name, time_of_submission, image1 FROM submissions WHERE id = ?");
    if ($stmt === false) {
        $message = "Database query preparation failed: " . $conn->error;
    } else {
        $stmt->bind_param("i", $submissionId); // 'i' for integer
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Bind the result columns to PHP variables
            $stmt->bind_result($locationName, $timeOfSubmission, $imageData);
            $stmt->fetch(); // Fetch the data into the bound variables
        } else {
            $message = "No submission found with ID: " . htmlspecialchars($submissionId);
        }
        $stmt->close();
    }
} else {
    $message = "No submission ID provided.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submission Details</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="wrapper">
        <div class="form-upload">
            <h2 class="form-upload-heading">อัพโหลดสำเร็จ</h2>
            <?php if ($message): ?>
                <p><?php echo $message; ?></p>
            <?php else: ?>
                <p><strong>ชื่อสถานที่:</strong> <?php echo htmlspecialchars($locationName); ?></p>
                <p><strong>เวลาที่ตรวจ:</strong> <?php echo htmlspecialchars($timeOfSubmission); ?></p>
                <?php if ($imageData): // Check if $imageData actually contains data ?>
                    <div style="text-align: center; margin-top: 20px;">
                        <img src="data:image/jpeg;base64,<?php echo base64_encode($imageData); ?>" 
                             alt="Uploaded Image" 
                             style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    </div>
                <?php else: ?>
                    <p>No image available for this submission or image data is empty.</p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
