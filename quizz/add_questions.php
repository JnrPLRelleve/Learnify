<?php
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'instructor') {
    header("Location: add_questions.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "learnify");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$instructor = $_SESSION['username'];
$quiz_title = isset($_GET['title']) ? $_GET['title'] : '';

if (empty($quiz_title)) {
    header("Location: instructor_dashboard.php");
    exit();
}
if (isset($_POST['delete_question'])) {
    $question_id = $conn->real_escape_string($_POST['question_id']);
    $delete_sql = "DELETE FROM quizzes WHERE id = ? AND instructor = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("is", $question_id, $instructor);
    
    if ($delete_stmt->execute()) {
        $success = "Question deleted successfully!";
    } else {
        $error = "Error deleting question: " . $conn->error;
    }
}

if (isset($_POST['delete_quiz'])) {
    $delete_quiz_sql = "DELETE FROM quizzes WHERE title = ? AND instructor = ?";
    $delete_quiz_stmt = $conn->prepare($delete_quiz_sql);
    $delete_quiz_stmt->bind_param("ss", $quiz_title, $instructor);
    
    if ($delete_quiz_stmt->execute()) {
        header("Location: instructor_dashboard.php");
        exit();
    } else {
        $error = "Error deleting quiz: " . $conn->error;
    }
}
// Replace the question submission handling section with this:
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['question'])) {
    if (isset($_POST['question']) && 
        isset($_POST['correct_answer']) && 
        isset($_POST['wrong_answer1']) && 
        isset($_POST['wrong_answer2']) && 
        isset($_POST['wrong_answer3'])) {
        
        $question_text = $conn->real_escape_string($_POST['question']);
        $correct_answer = $conn->real_escape_string($_POST['correct_answer']);
        $wrong_answer1 = $conn->real_escape_string($_POST['wrong_answer1']);
        $wrong_answer2 = $conn->real_escape_string($_POST['wrong_answer2']);
        $wrong_answer3 = $conn->real_escape_string($_POST['wrong_answer3']);

        $sql = "INSERT INTO quizzes (title, instructor, question_text, correct_answer, wrong_answer1, wrong_answer2, wrong_answer3) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss", $quiz_title, $instructor, $question_text, $correct_answer, $wrong_answer1, $wrong_answer2, $wrong_answer3);
        
        if ($stmt->execute()) {
            $success = "Question added successfully!";
        } else {
            $error = "Error adding question: " . $conn->error;
        }
    }
}

// Fetch existing questions
$sql = "SELECT * FROM quizzes WHERE instructor = ? AND title = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $instructor, $quiz_title);
$stmt->execute();
$questions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Questions - <?php echo htmlspecialchars($quiz_title); ?></title>
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .question-form {
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"], textarea {
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
        .question-list {
            margin-top: 30px;
        }
        .question-item {
            background-color: #fff;
            padding: 15px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .finish-btn {
            background-color: #2196F3;
            border: none;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            display: inline-block;
            border-radius: 4px;
            margin-top: 20px;
        }
        .delete-btn {
            background-color: #ff4444;
            border: none;
            color: white;
            padding: 5px 10px;
            cursor: pointer;
            border-radius: 4px;
        }

        .delete-btn:hover {
            background-color: #cc0000;
        }

        .question-item {
            position: relative;
            padding-right: 80px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Adding Questions to: <?php echo htmlspecialchars($quiz_title); ?></h2>
        
        <?php if (isset($success)) echo "<p style='color: green;'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
        
        <div class="question-form">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="question">Question:</label>
                    <textarea id="question" name="question" required rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="correct_answer">Correct Answer:</label>
                    <input type="text" id="correct_answer" name="correct_answer" required>
                </div>
                <div class="form-group">
                    <label for="wrong_answer1">Wrong Answer 1:</label>
                    <input type="text" id="wrong_answer1" name="wrong_answer1" required>
                </div>
                <div class="form-group">
                    <label for="wrong_answer2">Wrong Answer 2:</label>
                    <input type="text" id="wrong_answer2" name="wrong_answer2" required>
                </div>
                <div class="form-group">
                    <label for="wrong_answer3">Wrong Answer 3:</label>
                    <input type="text" id="wrong_answer3" name="wrong_answer3" required>
                </div>
                <button type="submit" class="submit-btn">Add Question</button>
            </form>
        </div>

        <div class="question-list">
            <h3>Added Questions:</h3>
            <?php while($question = $questions->fetch_assoc()): ?>
            <div class="question-item">
                <form method="POST" action="" style="float: right;">
                    <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
                    <button type="submit" name="delete_question" class="delete-btn" 
                            onclick="return confirm('Are you sure you want to delete this question?')">
                        Delete
                    </button>
                </form>
                <p><strong>Q: </strong><?php echo htmlspecialchars($question['question_text']); ?></p>
                <p><strong>Correct: </strong><?php echo htmlspecialchars($question['correct_answer']); ?></p>
                <p><strong>Wrong Answers: </strong></p>
                <ul>
                    <li><?php echo htmlspecialchars($question['wrong_answer1']); ?></li>
                    <li><?php echo htmlspecialchars($question['wrong_answer2']); ?></li>
                    <li><?php echo htmlspecialchars($question['wrong_answer3']); ?></li>
                </ul>
            </div>
            <?php endwhile; ?>
        </div>
        <form method="POST" action="" style="margin-top: 20px;">
            <button type="submit" name="delete_quiz" class="delete-btn" 
                    onclick="return confirm('Are you sure you want to delete this entire quiz? This cannot be undone.')">
                Delete Entire Quiz
            </button>
        </form>
        <a href="take_quizzes.php?title=<?php echo urlencode($quiz_title); ?>" class="finish-btn">Take Quiz</a>
    </div>
</body>
</html>