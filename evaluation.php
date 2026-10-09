<?php
// evaluate_intern.php - Comprehensive intern evaluation system

include 'db_connect.php';
session_start();
$supervisor_id = $_SESSION['user_id'];
$message = '';
$intern = null;
$evaluation_history = [];

// Get intern ID from URL
if (!isset($_GET['id'])) {
    header("Location: monitor.php");
    exit();
}

$intern_id = intval($_GET['id']);

// Verify this intern belongs to this supervisor
$verify_sql = "SELECT u.id, u.name, u.email, ip.full_name, ip.school, ip.department, ip.status
               FROM users u
               LEFT JOIN intern_profiles ip ON u.id = ip.user_id
               WHERE u.id = ? AND u.role = 'intern' AND ip.supervisor_id = ?";
$stmt = $conn->prepare($verify_sql);
$stmt->bind_param("ii", $intern_id, $supervisor_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Access denied! This intern is not assigned to you.");
}

$intern = $result->fetch_assoc();

// Handle evaluation submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_evaluation'])) {
    $technical_skills = intval($_POST['technical_skills']);
    $communication = intval($_POST['communication']);
    $teamwork = intval($_POST['teamwork']);
    $initiative = intval($_POST['initiative']);
    $punctuality = intval($_POST['punctuality']);
    $problem_solving = intval($_POST['problem_solving']);
    $learning_ability = intval($_POST['learning_ability']);
    $professionalism = intval($_POST['professionalism']);
    
    // Calculate overall performance (average)
    $overall_performance = round(($technical_skills + $communication + $teamwork + $initiative + 
                                  $punctuality + $problem_solving + $learning_ability + $professionalism) / 8);
    
    $strengths = trim($_POST['strengths']);
    $areas_for_improvement = trim($_POST['areas_for_improvement']);
    $supervisor_comments = trim($_POST['supervisor_comments']);
    $goals_set = trim($_POST['goals_set']);
    $goals_achieved = trim($_POST['goals_achieved']);
    
    // Check if table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'intern_performance'");
    
    if ($table_check->num_rows == 0) {
        // Create the table if it doesn't exist
        $create_table = "CREATE TABLE intern_performance (
            id INT PRIMARY KEY AUTO_INCREMENT,
            intern_id INT NOT NULL,
            supervisor_id INT NOT NULL,
            evaluation_date DATE NOT NULL,
            technical_skills INT CHECK (technical_skills BETWEEN 1 AND 5),
            communication INT CHECK (communication BETWEEN 1 AND 5),
            teamwork INT CHECK (teamwork BETWEEN 1 AND 5),
            initiative INT CHECK (initiative BETWEEN 1 AND 5),
            punctuality INT CHECK (punctuality BETWEEN 1 AND 5),
            problem_solving INT DEFAULT 0,
            learning_ability INT DEFAULT 0,
            professionalism INT DEFAULT 0,
            overall_performance INT CHECK (overall_performance BETWEEN 1 AND 5),
            strengths TEXT,
            areas_for_improvement TEXT,
            supervisor_comments TEXT,
            goals_set TEXT,
            goals_achieved TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (intern_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (supervisor_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $conn->query($create_table);
    }
    
    // Insert evaluation
    $sql = "INSERT INTO intern_performance 
            (intern_id, supervisor_id, evaluation_date, technical_skills, communication, teamwork, 
             initiative, punctuality, problem_solving, learning_ability, professionalism, 
             overall_performance, strengths, areas_for_improvement, supervisor_comments, 
             goals_set, goals_achieved) 
            VALUES (?, ?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiiiiiiiissssss", 
        $intern_id, $supervisor_id, $technical_skills, $communication, $teamwork, 
        $initiative, $punctuality, $problem_solving, $learning_ability, $professionalism,
        $overall_performance, $strengths, $areas_for_improvement, $supervisor_comments, 
        $goals_set, $goals_achieved
    );
    
    if ($stmt->execute()) {
        $message = '<div class="alert alert-success"><i class="fas fa-check-circle"></i> Evaluation submitted successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger"><i class="fas fa-times-circle"></i> Error submitting evaluation.</div>';
    }
}

// Get evaluation history
$table_check = $conn->query("SHOW TABLES LIKE 'intern_performance'");
if ($table_check->num_rows > 0) {
    $history_sql = "SELECT * FROM intern_performance 
                   WHERE intern_id = ? AND supervisor_id = ? 
                   ORDER BY evaluation_date DESC";
    $stmt = $conn->prepare($history_sql);
    $stmt->bind_param("ii", $intern_id, $supervisor_id);
    $stmt->execute();
    $evaluation_history = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluate Intern - Supervisor Portal</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            padding: 30px 20px;
        }
        .page-title {
            color: white;
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 25px;
            background: white;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 20px 20px 0 0;
        }
        .intern-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 25px;
        }
        .rating-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            border: 2px solid #e0e0e0;
        }
        .rating-section h6 {
            color: #667eea;
            font-weight: bold;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
        }
        .rating-group {
            margin-bottom: 20px;
        }
        .rating-label {
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .star-rating {
            display: flex;
            gap: 10px;
            flex-direction: row-reverse;
            justify-content: flex-end;
        }
        .star-rating input {
            display: none;
        }
        .star-rating label {
            cursor: pointer;
            font-size: 2rem;
            color: #ddd;
            transition: color 0.2s;
        }
        .star-rating input:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: #ffc107;
        }
        .selected-rating {
            background: #667eea;
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 0.9rem;
        }
        .form-control, .form-select, textarea {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 12px;
        }
        .form-control:focus, textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 40px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1.1rem;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        .history-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        .overall-score {
            display: inline-block;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            font-size: 1.2rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 1000px;">
        <!-- Page Title -->
        <h1 class="page-title">
            <i class="fas fa-star"></i> Evaluate Intern Performance
        </h1>
        
        <!-- Messages -->
        <?php echo $message; ?>
        
        <!-- Intern Information -->
        <div class="intern-info">
            <h5 style="color: #667eea; margin-bottom: 15px;">
                <i class="fas fa-user-graduate"></i> Intern Information
            </h5>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;">
                <div>
                    <strong>Name:</strong><br>
                    <?php echo htmlspecialchars($intern['full_name'] ?? $intern['name']); ?>
                </div>
                <div>
                    <strong>Email:</strong><br>
                    <?php echo htmlspecialchars($intern['email']); ?>
                </div>
                <?php if ($intern['school']): ?>
                <div>
                    <strong>School:</strong><br>
                    <?php echo htmlspecialchars($intern['school']); ?>
                </div>
                <?php endif; ?>
                <?php if ($intern['department']): ?>
                <div>
                    <strong>Department:</strong><br>
                    <?php echo htmlspecialchars($intern['department']); ?>
                </div>
                <?php endif; ?>
                <div>
                    <strong>Status:</strong><br>
                    <span style="background: #d4edda; color: #155724; padding: 5px 15px; border-radius: 15px;">
                        <?php echo strtoupper($intern['status'] ?? 'ACTIVE'); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Evaluation Form -->
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-clipboard-check"></i> New Performance Evaluation</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <!-- Performance Ratings -->
                    <div class="rating-section">
                        <h6><i class="fas fa-star"></i> Performance Ratings (1-5 Stars)</h6>
                        
                        <?php
                        $criteria = [
                            'technical_skills' => 'Technical Skills & Knowledge',
                            'communication' => 'Communication Skills',
                            'teamwork' => 'Teamwork & Collaboration',
                            'initiative' => 'Initiative & Proactivity',
                            'punctuality' => 'Punctuality & Reliability',
                            'problem_solving' => 'Problem Solving Ability',
                            'learning_ability' => 'Learning & Adaptability',
                            'professionalism' => 'Professionalism & Work Ethic'
                        ];
                        
                        foreach ($criteria as $name => $label):
                        ?>
                            <div class="rating-group">
                                <div class="rating-label">
                                    <span><?php echo $label; ?> *</span>
                                    <span class="selected-rating" id="<?php echo $name; ?>_display">Not Rated</span>
                                </div>
                                <div class="star-rating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <input type="radio" name="<?php echo $name; ?>" 
                                               id="<?php echo $name; ?>_<?php echo $i; ?>" 
                                               value="<?php echo $i; ?>" required
                                               onchange="updateDisplay('<?php echo $name; ?>', <?php echo $i; ?>)">
                                        <label for="<?php echo $name; ?>_<?php echo $i; ?>">★</label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Text Feedback -->
                    <div class="rating-section">
                        <h6><i class="fas fa-comments"></i> Detailed Feedback</h6>
                        
                        <div class="mb-3">
                            <label class="form-label"><strong>Strengths & Positive Qualities *</strong></label>
                            <textarea name="strengths" class="form-control" rows="4" required
                                      placeholder="List the intern's key strengths, achievements, and positive qualities..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><strong>Areas for Improvement *</strong></label>
                            <textarea name="areas_for_improvement" class="form-control" rows="4" required
                                      placeholder="Identify areas where the intern can improve and develop..."></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><strong>Overall Comments & Recommendations</strong></label>
                            <textarea name="supervisor_comments" class="form-control" rows="4"
                                      placeholder="Additional comments, recommendations, or observations..."></textarea>
                        </div>
                    </div>
                    
                    <!-- Goals -->
                    <div class="rating-section">
                        <h6><i class="fas fa-bullseye"></i> Goals & Progress</h6>
                        
                        <div class="mb-3">
                            <label class="form-label"><strong>Goals Set for This Period</strong></label>
                            <textarea name="goals_set" class="form-control" rows="3"
                                      placeholder="What goals were set for the intern?"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><strong>Goals Achieved</strong></label>
                            <textarea name="goals_achieved" class="form-control" rows="3"
                                      placeholder="Which goals did the intern achieve?"></textarea>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <div class="text-center mt-4">
                        <button type="submit" name="submit_evaluation" class="btn-submit">
                            <i class="fas fa-check-circle"></i> Submit Evaluation
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Evaluation History -->
        <?php if ($evaluation_history && $evaluation_history->num_rows > 0): ?>
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> Previous Evaluations</h5>
            </div>
            <div class="card-body">
                <?php while ($eval = $evaluation_history->fetch_assoc()): ?>
                    <div class="history-item">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <div>
                                <strong style="font-size: 1.1rem;">
                                    <i class="far fa-calendar"></i> 
                                    <?php echo date('F d, Y', strtotime($eval['evaluation_date'])); ?>
                                </strong>
                            </div>
                            <div class="overall-score">
                                <i class="fas fa-star"></i> <?php echo $eval['overall_performance']; ?>/5
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 15px;">
                            <div><small>Technical:</small> <strong><?php echo $eval['technical_skills']; ?>/5</strong></div>
                            <div><small>Communication:</small> <strong><?php echo $eval['communication']; ?>/5</strong></div>
                            <div><small>Teamwork:</small> <strong><?php echo $eval['teamwork']; ?>/5</strong></div>
                            <div><small>Initiative:</small> <strong><?php echo $eval['initiative']; ?>/5</strong></div>
                        </div>
                        
                        <?php if ($eval['strengths']): ?>
                        <div style="margin-bottom: 10px;">
                            <strong style="color: #28a745;"><i class="fas fa-thumbs-up"></i> Strengths:</strong><br>
                            <span style="white-space: pre-wrap;"><?php echo htmlspecialchars($eval['strengths']); ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($eval['areas_for_improvement']): ?>
                        <div>
                            <strong style="color: #ffc107;"><i class="fas fa-arrow-up"></i> Areas for Improvement:</strong><br>
                            <span style="white-space: pre-wrap;"><?php echo htmlspecialchars($eval['areas_for_improvement']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Back Button -->
        <div class="text-center mb-4">
            <a href="monitor.php" class="btn btn-secondary btn-lg">
                <i class="fas fa-arrow-left"></i> Back to Monitoring
            </a>
        </div>
    </div>

    <script>
        // Update rating display
        function updateDisplay(name, value) {
            const labels = {
                1: '⭐ Poor',
                2: '⭐⭐ Fair',
                3: '⭐⭐⭐ Good',
                4: '⭐⭐⭐⭐ Very Good',
                5: '⭐⭐⭐⭐⭐ Excellent'
            };
            document.getElementById(name + '_display').textContent = labels[value];
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>