<?php
require 'db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $roll = trim($_POST['roll_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');

    if (
        $name === '' ||
        $roll === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $course === ''
    ) {
        $message = 'Please enter valid details in all fields.';
    } else {
        $stmt = $conn->prepare(
            'INSERT INTO students (name, roll_number, email, course)
             VALUES (?, ?, ?, ?)'
        );

        $stmt->bind_param('ssss', $name, $roll, $email, $course);

        if ($stmt->execute()) {
            header('Location: index.php');
            exit;
        } else {
            $message = 'Could not add student. Roll number may already exist.';
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef3ff;
            padding: 20px;
        }

        .container {
            max-width: 450px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            box-sizing: border-box;
        }

        button {
            background: #205fc4;
            color: white;
            border: none;
            padding: 12px;
            width: 100%;
            cursor: pointer;
        }

        .message {
            color: red;
        }
    </style>
</head>

<body>
<div class="container">
    <h2>Add Student</h2>

    <p class="message"><?= htmlspecialchars($message) ?></p>

    <form method="post">
        <label>Student Name</label>
        <input type="text" name="name" required>

        <label>Roll Number</label>
        <input type="text" name="roll_number" required>

        <label>Email</label>
        <input type="email" name="email" required>

        <label>Course</label>
        <select name="course" required>
            <option value="">Select Course</option>
            <option value="CSE">CSE</option>
            <option value="IT">IT</option>
            <option value="ECE">ECE</option>
            <option value="EEE">EEE</option>
        </select>

        <button type="submit">Save Student</button>
    </form>

    <p><a href="index.php">Back to Student List</a></p>
</div>
</body>
</html>
