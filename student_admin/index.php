<?php
require 'db.php';

$result = $conn->query("SELECT * FROM students ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management System</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef3ff;
            padding: 20px;
        }

        .container {
            max-width: 1000px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
        }

        h1 {
            color: #174a8b;
            text-align: center;
        }

        .button {
            display: inline-block;
            background: #205fc4;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #205fc4;
            color: white;
        }
    </style>
</head>

<body>
<div class="container">
    <h1>Student Management System</h1>

    <a class="button" href="add_student.php">+ Add Student</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Roll Number</th>
            <th>Email</th>
            <th>Course</th>
            <th>Actions</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= (int)$row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['roll_number']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= htmlspecialchars($row['course']) ?></td>
            <td>
                <a href="view_student.php?id=<?= (int)$row['id'] ?>">View</a>
                <a href="edit_student.php?id=<?= (int)$row['id'] ?>">Edit</a>
                <form action="delete_student.php" method="post" style="display:inline"
      onsubmit="return confirm('Delete this student?')">
    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
    <button type="submit">Delete</button>
</form>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>
</body>
</html>
