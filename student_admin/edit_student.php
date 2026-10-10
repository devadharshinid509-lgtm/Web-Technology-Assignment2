<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("Invalid student ID.");
}

$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    die("Student not found. Check the students table.");
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $roll = trim($_POST["roll_number"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $course = trim($_POST["course"] ?? "");

    if ($name === "" || $roll === "" || $course === "" ||
        !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter valid details in all fields.";
    } else {
        $update = $conn->prepare(
            "UPDATE students SET name=?, roll_number=?, email=?, course=? WHERE id=?"
        );

        $update->bind_param("ssssi", $name, $roll, $email, $course, $id);

        if ($update->execute()) {
            $update->close();
            header("Location: index.php");
            exit;
        } else {
            $message = "Update failed: " . $update->error;
            $update->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef3ff;
            padding: 20px;
        }

        .container {
            max-width: 450px;
            margin: 30px auto;
            padding: 25px;
            background: white;
            border-radius: 10px;
        }

        h2 { color: #174a8b; }

        label {
            display: block;
            margin-top: 15px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 6px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            margin-top: 20px;
            padding: 12px;
            background: #205fc4;
            color: white;
            border: none;
            cursor: pointer;
        }

        .message { color: red; }
    </style>
</head>

<body>
<div class="container">
    <h2>Edit Student Details</h2>

    <p class="message"><?= htmlspecialchars($message) ?></p>

    <form method="post">
        <label>Student Name</label>
        <input type="text" name="name"
            value="<?= htmlspecialchars($student['name']) ?>" required>

        <label>Roll Number</label>
        <input type="text" name="roll_number"
            value="<?= htmlspecialchars($student['roll_number']) ?>" required>

        <label>Email</label>
        <input type="email" name="email"
            value="<?= htmlspecialchars($student['email']) ?>" required>

        <label>Course</label>
        <select name="course" required>
            <?php foreach (["CSE", "IT", "ECE", "EEE"] as $c) { ?>
                <option value="<?= $c ?>"
                    <?= $student['course'] === $c ? "selected" : "" ?>>
                    <?= $c ?>
                </option>
            <?php } ?>
        </select>

        <button type="submit">Update Student</button>
    </form>

    <p><a href="index.php">Back to Student List</a></p>
</div>
</body>
</html>
