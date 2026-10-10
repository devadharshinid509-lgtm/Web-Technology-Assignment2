<?php
require 'db.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid student ID.");
}

$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$student) {
    exit("Student not found.");
}

// Get uploaded files for this student
$stmt = $conn->prepare(
    "SELECT * FROM student_files
     WHERE student_id = ?
     ORDER BY id DESC"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

$files = [];
while ($row = $result->fetch_assoc()) {
    $files[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef3ff;
            padding: 20px;
        }

        .container {
            max-width: 650px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        h2, h3 {
            color: #174a8b;
        }

        p {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            overflow-wrap: anywhere;
        }

        .file {
            background: #f5f7fb;
            padding: 12px;
            margin: 12px 0;
            border-radius: 6px;
        }

        img {
            display: block;
            max-width: 220px;
            max-height: 220px;
            margin-top: 10px;
            object-fit: contain;
        }

        a {
            color: #174a8b;
        }

        .button {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 14px;
            background: #174a8b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>

<body>
<div class="container">

    <h2>Student Details</h2>

    <p><b>Name:</b>
        <?= htmlspecialchars($student['name'], ENT_QUOTES, 'UTF-8') ?>
    </p>

    <p><b>Roll Number:</b>
        <?= htmlspecialchars($student['roll_number'], ENT_QUOTES, 'UTF-8') ?>
    </p>

    <p><b>Email:</b>
        <?= htmlspecialchars($student['email'], ENT_QUOTES, 'UTF-8') ?>
    </p>

    <p><b>Course:</b>
        <?= htmlspecialchars($student['course'], ENT_QUOTES, 'UTF-8') ?>
    </p>

    <h3>Uploaded Documents and Images</h3>

    <?php if (count($files) > 0): ?>

        <?php foreach ($files as $file): ?>
            <div class="file">

                <p><b>File Type:</b>
                    <?= htmlspecialchars($file['file_type'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <p><b>File Size:</b>
                    <?= number_format($file['file_size'] / 1024, 1) ?> KB
                </p>

                <p><b>Upload Date:</b>
                    <?= htmlspecialchars($file['upload_date'], ENT_QUOTES, 'UTF-8') ?>
                </p>

                <?php
                $filePath = $file['file_path'];
                $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $safeExtensions = ['pdf', 'jpg', 'jpeg', 'png'];
                $isImage = in_array($extension, ['jpg', 'jpeg', 'png'], true);
                ?>

                <?php if (
                    in_array($extension, $safeExtensions, true) &&
                    strpos($filePath, 'uploads/') === 0 &&
                    !str_contains($filePath, '..')
                ): ?>

                    <?php if ($isImage): ?>
                        <img
                            src="<?= htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') ?>"
                            alt="Student uploaded image"
                        >
                    <?php endif; ?>

                    <a href="<?= htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') ?>"
                       target="_blank" rel="noopener">
                        View / Open File
                    </a>

                <?php else: ?>
                    <p>File link is invalid.</p>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <p>No files uploaded for this student yet.</p>
    <?php endif; ?>

    <a class="button"
       href="upload.php?student_id=<?= (int)$student['id'] ?>">
        Upload Another File
    </a>

    <br>

    <a href="index.php">Back to Student List</a>

</div>
</body>
</html>
