<?php
require_once 'db_connect.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if an image file is uploaded
    if (isset($_FILES['image1']) && $_FILES['image1']['error'] == 0) {
        $image = $_FILES['image1'];
        $imageTmpName = $image['tmp_name'];
        $imageData = file_get_contents($imageTmpName); // Get the binary data of the image

        // Check if temporary file exists and is readable
        if (file_exists($imageTmpName) && is_readable($imageTmpName)) {
            // Get the location name from the form
            $locationName = $_POST['location_name'];

            // Prepare the statement
            // The statement has two placeholders: ?, NOW(), ?
            // So, we need to bind two parameters.
            $stmt = $conn->prepare("INSERT INTO submissions (location_name, time_of_submission, image1) VALUES (?, NOW(), ?)");
            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }

            // Declare a dummy variable for the BLOB parameter.
            // mysqli_stmt_send_long_data will overwrite this value.
            $dummyImage = null; 

            // Bind both parameters. 's' for location_name (string), 'b' for image1 (blob).
            // The number of type characters ('sb') must match the number of '?' in the query (2).
            $stmt->bind_param("sb", $locationName, $dummyImage);

            // Send the image data using send_long_data for the second '?' (index 1)
            if (!empty($imageData)) {
                // The parameter index for image1 is 1 (0-indexed for the second placeholder)
                if (!$stmt->send_long_data(1, $imageData)) {
                    // Log the error or handle it gracefully without exposing to user
                    error_log("Error sending long data for image1: " . $stmt->error);
                    $stmt->close();
                    $conn->close();
                    exit(); 
                }
            } else {
                // If imageData is empty, image1 column might be NULL.
                error_log("Warning: \$imageData is empty, image1 column might be NULL for location: " . $locationName);
            }
            
            // Execute the statement
            if ($stmt->execute()) {
                $last_id = $conn->insert_id;
                // Redirect to submitted.php with the ID
                header("Location: submitted.php?id=" . $last_id);
                exit(); // Important: Stop script execution after redirection
            } else {
                // Log the error or handle it gracefully
                error_log("Error executing statement: " . $stmt->error);
                die("Error uploading image. Please try again later.");
            }

            // Close the statement
            $stmt->close();
        } else {
            // Log the error or handle it gracefully
            error_log("Temporary file does not exist or is not readable: " . $imageTmpName);
            die("Error processing uploaded file. Please try again.");
        }
    } else {
        // Log specific upload errors
        error_log("No image uploaded or there was an upload error. Error code: " . ($_FILES['image1']['error'] ?? 'N/A'));
        die("Please select an image to upload.");
    }
}

// Close the connection (only if it was opened and not already closed by a die/exit)
if (isset($conn) && $conn->ping()) {
    $conn->close();
}
?>
