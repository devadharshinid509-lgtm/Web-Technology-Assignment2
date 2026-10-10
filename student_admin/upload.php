
<?php
require 'db.php';

$message = "";
$student_id = 0;

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = filter_input(
        INPUT_POST,
        'student_id',
        FILTER_VALIDATE_INT
    );

    // Check student ID
    if (!$student_id || $student_id < 1) {
        $message = "Please enter a valid student ID.";
    } else {

        // Verify student exists
        $check = $conn->prepare(
            "SELECT id FROM students WHERE id = ?"
        );
        $check->bind_param("i", $student_id);
        $check->execute();
        $student = $check->get_result()->fetch_assoc();
        $check->close();

        if (!$student) {
            $message = "Student not found.";
        } elseif (
            !isset($_FILES['student_file'])
        ) {
            $message = "Please select a file to upload.";
        } else {

            $file = $_FILES['student_file'];
            $error = $file['error'];

            // TEST 1 and TEST 3: Upload error and file size
            if ($error === UPLOAD_ERR_NO_FILE) {
                $message = "Please select a file to upload.";

            } elseif (
                $error === UPLOAD_ERR_INI_SIZE ||
                $error === UPLOAD_ERR_FORM_SIZE
            ) {
                $message = "File size exceeds the allowed limit. Maximum size is 2 MB.";

            } elseif ($error !== UPLOAD_ERR_OK) {
                $message = "Upload failed. Please try again with a smaller file.";

            } elseif ($file['size'] > 2 * 1024 * 1024) {
                $message = "File size must be less than or equal to 2 MB.";

            } else {

                // TEST 2: Validate actual file MIME type
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);

                $allowedTypes = [
                    'application/pdf' => 'pdf',
                    'image/jpeg'      => 'jpg',
                    'image/png'       => 'png'
                ];

                if (!isset($allowedTypes[$mime])) {
                    $message = "Invalid file type. Only PDF, JPG and PNG files are allowed.";

                } else {

                    $uploadDir = __DIR__ . '/uploads/';

                    if (!is_dir($uploadDir) ||
                        !is_writable($uploadDir)) {
                        $message = "Uploads folder is missing or not writable.";

                    } else {

                        // TEST 4: Generate a unique filename
                        // Never intentionally reuse an existing filename
                        do {
                            $filename = bin2hex(random_bytes(16))
                                . '.' . $allowedTypes[$mime];

                            $destination = $uploadDir . $filename;

                        } while (file_exists($destination));

                        $relativePath = 'uploads/' . $filename;

                        if (move_uploaded_file(
                            $file['tmp_name'],
                            $destination
                        )) {
                            $size = (int) $file['size'];

                            // Save file metadata in MySQL
                            $stmt = $conn->prepare(
                                "INSERT INTO student_files
                                (student_id, file_path, file_type, file_size)
                                VALUES (?, ?, ?, ?)"
                            );

                            $stmt->bind_param(
                                "issi",
                                $student_id,
                                $relativePath,
                                $mime,
                                $size
                            );

                            if ($stmt->execute()) {
                                $message = "File uploaded successfully!";
                            } else {
                                // Remove file if metadata save fails
                                unlink($destination);
                                $message = "Could not save file details to the database.";
                            }

                            $stmt->close();

                        } else {
                            $message = "File upload failed. Please try again.";
                        }
                    }
                }
            }
        }
    }
}

// Load uploaded files for this student
$files = [];

if ($student_id > 0) {
    $stmt = $conn->prepare(
        "SELECT id, file_path, file_type, file_size, upload_date
         FROM student_files
         WHERE student_id = ?
         ORDER BY id DESC"
    );

    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $files[] = $row;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Student File</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f9;
            padding: 30px;
        }

        .container {
            max-width: 650px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        input, button {
            padding: 10px;
            margin: 8px 0;
        }

        button {
            background: #1769aa;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        .message {
            padding: 10px;
            background: #eef3ff;
            overflow-wrap: anywhere;
        }

        .file {
            background: #f5f7fb;
            padding: 12px;
            margin: 12px 0;
            border-radius: 6px;
        }
    </style>
</head>

<body>
<div class="container">

    <h2>Upload Student File</h2>

    <?php if ($message !== ""): ?>
        <p class="message">
            <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <label>Student ID:</label><br>
        <input
            type="number"
            name="student_id"
            min="1"
            value="<?= (int)$student_id ?>"
            required
        >
        <br>

        <label>Select PDF, JPG or PNG (maximum 2 MB):</label><br>
        <input
            type="file"
            name="student_file"
            accept=".pdf,.jpg,.jpeg,.png"
        >
        <br>

        <button type="submit">Upload File</button>
    </form>

    <h3>Uploaded Files</h3>

    <?php if (count($files) > 0): ?>
        <?php foreach ($files as $file): ?>
            <div class="file">
                <p>
                    <b>File Type:</b>
                    <?= htmlspecialchars($file['file_type'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p>
                    <b>File Size:</b>
                    <?= number_format($file['file_size'] / 1024, 1) ?> KB
                </p>

                <p>
                    <b>Upload Date:</b>
                    <?= htmlspecialchars($file['upload_date'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <?php
                $path = $file['file_path'];
                $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

                $validExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                $validPath = strpos($path, 'uploads/') === 0
                    && strpos($path, '..') === false;
                ?>

                <?php if (
                    $validPath &&
                    in_array($extension, $validExtensions, true)
                ): ?>
                    <a
                        href="<?= htmlspecialchars($path, ENT_QUOTES, 'UTF-8') ?>"
                        target="_blank"
                        rel="noopener"
                    >View File</a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No files uploaded for this student yet.</p>
    <?php endif; ?>

    <p><a href="index.php">Back to Student List</a></p>

</div>
</body>
</html>
