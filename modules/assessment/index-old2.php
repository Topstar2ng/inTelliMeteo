<?php
require_once '../../includes/db.php'; // Ensures session storage and core PDO $pdo connections are active
require_once 'AssessmentEngine.php';
require_once '../../includes/auth_check.php';

$userId = $_SESSION['user_id'];
$engine = new AssessmentEngine($pdo); // Instantiates instance utilizing system global PDO context
$questionBank = AssessmentEngine::getQuestionBank();

$evaluationResult = null;
$activeTab = 'test-panel';

// Process Exam form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_exam') {
    $category = $_POST['category'] ?? '';
    $answers = $_POST['answers'] ?? [];
    
    $evaluationResult = $engine->evaluateSubmission($userId, $category, $answers);
    $activeTab = 'history-panel'; // Route view to analytics to show performance updates instantly
}

$history = $engine->getUserHistory($userId);
?>
<?php include '../../includes/header.php'; ?>

<style>
    body {
        background-color: #0b0d13 !important;
        color: #e2e8f0 !important;
    }
    .assessment-card {
        background: #121622;
        border: 1px solid #1e2538;
        border-radius: 12px;
    }
    .question-box {
        background: #191f32;
        border: 1px solid #232d44;
        border-radius: 8px;
        transition: border-color 0.2s ease;
    }
    .question-box:hover {
        border-color: #3b82f6;
    }
    .option-label {
        cursor: pointer;
        display: block;
        padding: 10px 14px;
        background: #111420;
        border: 1px solid #1e2538;
        border-radius: 6px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
    }
    .form-check-input:checked + .option-label {
        background: rgba(59, 130, 246, 0.15);
        border-color: #3b82f6;
        color: #60a5fa;
    }
    .nav-tabs .nav-link {
        color: #94a3b8;
        border: none;
        background: transparent;
    }
    .nav-tabs .nav-link.active {
        color: #38bdf8 !important;
        background: transparent;
        border-bottom: 2px solid #38bdf8;
    }
    .history-row {
        background: #151a2a;
        border-left: 4px solid #10b981;
    }
    .history-row.fail-gradient {
        border-left-color: #ef4444;
    }
</style>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary" style="border-color: rgba(255,255,255,0.08) !important;">
        <div>
            <h2 class="fw-bold text-info mb-0 d-flex align-items-center">
                <i class="bi bi-shield-check me-2"></i> Professional Competency Assessment
            </h2>
            <small class="text-muted">Validate precision knowledge frameworks against regulatory ICAO & WMO guidelines</small>
        </div>
        <div class="btn-group shadow-sm" role="group">
            <a href="../../index.php" class="btn btn-outline-secondary btn-sm" title="Dashboard"><i class="bi bi-house-heart"></i></a>
            <a href="../aerometeo/index.php" class="btn btn-outline-secondary btn-sm" title="AeroMeteo"><i class="bi bi-airplane-fill"></i></a>
            <a href="../agrometeo/index.php" class="btn btn-outline-secondary btn-sm" title="AgroMeteo"><i class="bi bi-tree-fill"></i></a>
        </div>
    </div>

    <ul class="nav nav-tabs border-secondary mb-4" id="assessmentTabs" role="tablist">
        <li class="nav-item">
            <button class="nav-link fw-bold text-uppercase small tracking-wide <?php echo ($activeTab === 'test-panel') ? 'active' : ''; ?>" 
                    id="test-tab" data-bs-toggle="tab" data-bs-target="#test-panel" type="button" role="tab">
                <i class="bi bi-journal-code me-2"></i>Examination Center
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold text-uppercase small tracking-wide <?php echo ($activeTab === 'history-panel') ? 'active' : ''; ?>" 
                    id="history-tab" data-bs-toggle="tab" data-bs-target="#history-panel" type="button" role="tab">
                <i class="bi bi-clock-history me-2"></i>Performance History Log
            </button>
        </li>
    </ul>

    <div class="tab-content" id="assessmentTabsContent">
        
        <div class="tab-pane fade <?php echo ($activeTab === 'test-panel') ? 'show active' : ''; ?>" id="test-panel" role="tabpanel">
            <div class="row g-4">
                <?php foreach ($questionBank as $catKey => $quizGroup): ?>
                    <div class="col-12 mb-4">
                        <div class="card assessment-card p-4 shadow-lg">
                            <div class="d-flex align-items-center justify-content-between border-bottom border-secondary pb-3 mb-4" style="border-color: rgba(255,255,255,0.05) !important;">
                                <h4 class="text-white fw-bold mb-0 text-capitalize">
                                    <i class="bi bi-bookmark-star text-warning me-2"></i><?php echo $catKey; ?> Meteorology Focus Module
                                </h4>
                                <span class="badge bg-dark border border-secondary text-light px-3 py-2 font-monospace">
                                    <?php echo count($quizGroup); ?> Validation Questions
                                </span>
                            </div>

                            <form action="" method="POST">
                                <input type="hidden" name="action" value="submit_exam">
                                <input type="hidden" name="category" value="<?php echo htmlspecialchars($catKey); ?>">

                                <?php foreach ($quizGroup as $index => $q): ?>
                                    <div class="question-box p-4 mb-4">
                                        <h5 class="text-white fw-semibold mb-3">
                                            <span class="text-info font-monospace me-2">Q<?php echo ($index + 1); ?>.</span>
                                            <?php echo htmlspecialchars($q['question']); ?>
                                        </h5>

                                        <div class="options-container ps-2">
                                            <?php foreach ($q['options'] as $key => $optionValue): ?>
                                                <div class="form-check position-relative p-0 m-0 text-light">
                                                    <input class="form-check-input d-none" type="radio" 
                                                           name="answers[<?php echo $q['id']; ?>]" 
                                                           id="opt_<?php echo $q['id'] . '_' . $key; ?>" 
                                                           value="<?php echo $key; ?>" required>
                                                    <label class="option-label" for="opt_<?php echo $q['id'] . '_' . $key; ?>">
                                                        <span class="fw-bold text-warning me-2"><?php echo $key; ?>.</span> 
                                                        <?php echo htmlspecialchars($optionValue); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <button type="submit" class="btn btn-primary px-4 fw-semibold tracking-wide">
                                    <i class="bi bi-cloud-arrow-up-fill me-2"></i>Submit Exam for Verification
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tab-pane fade <?php echo ($activeTab === 'history-panel') ? 'show active' : ''; ?>" id="history-panel" role="tabpanel">
            
            <?php if ($evaluationResult): ?>
                <div class="alert assessment-card border-0 p-4 shadow-lg mb-4 text-center">
                    <span class="text-uppercase small tracking-widest text-muted d-block mb-1">Grading System Acknowledgment</span>
                    <h3 class="fw-bold text-white mb-2">Evaluation Completed!</h3>
                    <div class="display-4 fw-bold text-<?php echo $evaluationResult['percentage'] >= 70 ? 'success' : 'warning'; ?> font-monospace my-3">
                        <?php echo $evaluationResult['percentage']; ?>%
                    </div>
                    <p class="text-muted small mx-auto" style="max-width: 500px;">
                        You answered <strong class="text-white"><?php echo $evaluationResult['score']; ?></strong> correctly out of <strong class="text-white"><?php echo $evaluationResult['total']; ?></strong> variables. The data record matrix has been saved to your metrics history table.
                    </p>
                </div>
            <?php endif; ?>

            <div class="card assessment-card p-4 shadow-lg">
                <h5 class="text-white fw-bold mb-3 d-flex align-items-center">
                    <i class="bi bi-graph-up-arrow text-success me-2"></i>Historical Verification Logs
                </h5>
                <p class="text-muted small mb-4">Continuous logging of assessment metrics establishes proof of currency logs required across modern aviation operations profiles.</p>

                <div class="d-flex flex-column gap-3">
                    <?php if (!empty($history)): ?>
                        <?php foreach ($history as $row): ?>
                            <?php $isPassed = $row['percentage'] >= 70; ?>
                            <div class="p-3 rounded history-row <?php echo $isPassed ? '' : 'fail-gradient'; ?> d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div>
                                    <h6 class="text-white fw-semibold mb-1"><?php echo htmlspecialchars($row['category']); ?> Module</h6>
                                    <small class="text-muted font-monospace">
                                        <i class="bi bi-calendar3 me-1"></i><?php echo date('Y-m-d H:i UTC', strtotime($row['attempted_at'])); ?>
                                    </small>
                                </div>
                                <div class="text-md-end d-flex align-items-center gap-4 justify-content-between justify-content-md-end">
                                    <div>
                                        <div class="text-light fw-bold mb-0 font-monospace" style="font-size: 1.1rem;">
                                            <?php echo $row['score_achieved']; ?> / <?php echo $row['total_questions']; ?>
                                        </div>
                                        <small class="text-muted d-block small">Raw Score</small>
                                    </div>
                                    <div class="text-center" style="min-width: 75px;">
                                        <span class="badge rounded-pill font-monospace p-2 bg-dark border text-<?php echo $isPassed ? 'success border-success' : 'danger border-danger'; ?>" style="font-size: 0.9rem; width: 100%;">
                                            <?php echo $row['percentage']; ?>%
                                        </span>
                                        <small class="text-muted d-block mt-1 style-status" style="font-size: 0.7rem; text-transform: uppercase;">
                                            <?php echo $isPassed ? 'Passed' : 'Deficient'; ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted italic">
                            <i class="bi bi-folder-x fs-1 d-block mb-2 opacity-50"></i>
                            No logged examination indices located inside your account container profile.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</div>

<?php include '../../includes/footer.php'; ?>