<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: loginpage.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "learnify");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$quiz_title = isset($_GET['title']) ? $_GET['title'] : '';

if (empty($quiz_title)) {
    header("Location: instructor_dashboard.php");
    exit();
}

// Fetch quiz questions
$sql = "SELECT * FROM quizzes WHERE title = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $quiz_title);
$stmt->execute();
$questions = $stmt->get_result();

// Store questions in session for result processing
$_SESSION['quiz_questions'] = [];
while ($row = $questions->fetch_assoc()) {
    $_SESSION['quiz_questions'][] = $row;
}
$questions->data_seek(0); // Reset result pointer
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz - <?php echo htmlspecialchars($quiz_title); ?></title>
    <style>
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
        }
        .question-card {
            background-color: #f9f9f9;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .options {
            margin-top: 10px;
        }
        .option-label {
            display: block;
            padding: 10px;
            margin: 5px 0;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
        }
        .option-label:hover {
            background-color: #f0f0f0;
        }
        .submit-btn {
            background-color: #4CAF50;
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin-top: 20px;
            cursor: pointer;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="container">
        <h1><?php echo htmlspecialchars($quiz_title); ?></h1>
        
        <form method="POST" action="quiz_result.php">
            <input type="hidden" name="quiz_title" value="<?php echo htmlspecialchars($quiz_title); ?>">
            <input type="hidden" name="correct_answers" value='<?php echo json_encode(array_column($_SESSION['quiz_questions'], 'correct_answer')); ?>'>
            
            <?php 
            $question_num = 1;
            while($question = $questions->fetch_assoc()): 
                // Create array of answers in random order
                $answers = [
                    ['text' => $question['correct_answer'], 'correct' => true],
                    ['text' => $question['wrong_answer1'], 'correct' => false],
                    ['text' => $question['wrong_answer2'], 'correct' => false],
                    ['text' => $question['wrong_answer3'], 'correct' => false]
                ];
                shuffle($answers);
            ?>
            <div class="question-card">
                <h3>Question <?php echo $question_num; ?></h3>
                <p><?php echo htmlspecialchars($question['question_text']); ?></p>
                
                <div class="options">
                    <?php foreach($answers as $index => $answer): ?>
                    <label class="option-label">
                        <input type="radio" name="q<?php echo $question_num; ?>" 
                               value="<?php echo $answer['text']; ?>" required>
                        <?php echo htmlspecialchars($answer['text']); ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php 
            $question_num++;
            endwhile; 
            ?>
            
            <button type="submit" class="submit-btn">Submit Quiz</button>
        </form>
    </div>
</body>
</html>