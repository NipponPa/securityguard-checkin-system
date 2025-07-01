<?php
require_once 'upload.php';

// Define the location name
$locationName = "จุดสแกนตรวจ รปภ. โรงจอดรถ"; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Upload Form</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="wrapper">
        <form class="form-upload" method="post" action="" enctype="multipart/form-data">        
            <h2 class="form-upload-heading"><?php echo htmlspecialchars($locationName); ?></h2>
            <input type="hidden" name="location_name" value="<?php echo htmlspecialchars($locationName); ?>" />
            <input type="file" class="form-control" name="image1" accept="image/*" required />      
            <button class="btn btn-lg btn-primary" type="submit">อัพโหลด</button>    
        </form>
    </div>
</body>
</html>
