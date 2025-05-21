<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conn = new mysqli("localhost", "root", "", "learnify");
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    // Process quiz submission here
    $quiz_title = $conn->real_escape_string($_POST['quiz_title']);
    $instructor = $_SESSION['username'];
    
    $sql = "INSERT INTO quizzes (title, instructor) VALUES ('$quiz_title', '$instructor')";
    
    if ($conn->query($sql)) {
        $quiz_id = $conn->insert_id;
        header("Location: add_questions.php?title=" . urlencode($quiz_title));
        exit();
    } else {
        $error = "Error creating quiz: " . $conn->error;
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Quiz</title>
    <style>
        .quiz-form {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .submit-btn {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="quiz-form">
        <h2>Create New Quiz</h2>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="quiz_title">Quiz Title:</label>
                <input type="text" id="quiz_title" name="quiz_title" required>
            </div>
            <button type="submit" class="submit-btn">Create Quiz</button>
        </form>
    </div>
</body>
</html>