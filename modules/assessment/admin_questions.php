<?php
require_once '../../includes/db.php'; // Ensures session storage and core PDO $pdo connections are active
require_once 'AssessmentEngine.php';
require_once '../../includes/auth_check.php';

$userId = $_SESSION['user_id'];
if (!$isAdmin) {
    header("Location: ../../index.php");
    exit();
}
$engine = new AssessmentEngine($pdo); // Instantiates instance utilizing system global PDO context
$statusMessage = null;

// Process Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_question') {
        $success = $engine->addQuestion(
            $_POST['category'] ?? '',
            $_POST['question_text'] ?? '',
            $_POST['option_a'] ?? '',
            $_POST['option_b'] ?? '',
            $_POST['option_c'] ?? '',
            $_POST['option_d'] ?? '',
            $_POST['correct_option'] ?? ''
        );
        $statusMessage = $success ? "Question logged inside evaluation matrix successfully." : "Database validation error.";
    }
}

$allQuestions = $engine->getAllQuestionsForAdmin();
?>
<?php include '../../includes/header.php'; ?>

<style>
    body { background-color: #0b0d13 !important; color: #e2e8f0 !important; }
    .admin-card { background: #121622; border: 1px solid #1e2538; border-radius: 12px; }
    .form-control, .form-select { background-color: #191f32 !important; border: 1px solid #2d3954 !important; color: #ffffff !important; }
    .form-control:focus, .form-select:focus { border-color: #38bdf8 !important; box-shadow: none !important; }
    .question-list-item { background: #151a2a; border-left: 4px solid #38bdf8; border-radius: 4px; }
</style>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0"><i class="bi bi-sliders2"></i> CBT Admin Console</h2>
            <small class="text-dark">Configure baseline exam targets and telemetry parameters dynamically</small>
        </div>
        <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Assessment Page</a>
    </div>

    <?php if ($statusMessage): ?>
        <div class="alert alert-success alert-dismissible border-0 text-dark mb-4"> <i class="bi bi-check-circle"></i> <?php echo $statusMessage; ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Column 1: Creation Terminal Form -->
        <div class="col-md-5">
            <div class="card admin-card p-4 shadow-lg position-sticky" style="top: 20px;">
                <h5 class="text-white fw-bold mb-3 border-bottom border-secondary pb-2">
                    <i class="bi bi-plus-circle text-success me-2"></i>Create New Matrix Item
                </h5>
                
                <form action="" method="POST">
                    <input type="hidden" name="action" value="create_question">

                    <div class="mb-3">
                        <label class="form-label small text-warning text-uppercase">Operational Category</label>
                        <select class="form-select" name="category" required>
                            <option value="aviation">Aviation (ICAO / WMO Annex 3)</option>
                            <option value="general">General Meteorology</option>
                            <option value="agro">AgroMeteo Intelligence</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small text-warning text-uppercase">Question Prompt Text</label>
                        <textarea class="form-control" name="question_text" rows="3" required placeholder="Type operational question details here..."></textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small text-warning">Option A</label>
                            <input type="text" class="form-control" name="option_a" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-warning">Option B</label>
                            <input type="text" class="form-control" name="option_b" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-warning">Option C</label>
                            <input type="text" class="form-control" name="option_c" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-warning">Option D</label>
                            <input type="text" class="form-control" name="option_d" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small text-warning text-uppercase">Verified Correct Key Answer Choice</label>
                        <select class="form-select" name="correct_option" required>
                            <option value="A">Option A</option>
                            <option value="B">Option B</option>
                            <option value="C">Option C</option>
                            <option value="D">Option D</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-bold">
                        <i class="bi bi-disk-fill me-2"></i>Commit Question to System
                    </button>
                </form>
            </div>
        </div>

        <!-- Column 2: Live Question Bank View Inspector -->
        <div class="col-md-7">
            <div class="card admin-card p-4 shadow-lg">
                <h5 class="text-white fw-bold mb-3 border-bottom border-secondary pb-2">
                    <i class="bi bi-database-check text-info me-2"></i>Active Bank Inspector Registry
                </h5>
                
                <div class="d-flex flex-column gap-3 max-vh-100 overflow-auto pe-1">
                    <?php if (!empty($allQuestions)): ?>
                        <?php foreach ($allQuestions as $q): ?>
                            <div class="p-3 question-list-item">
                                
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="btn-group">
                                        <span class="badge bg-dark text-info border border-secondary text-uppercase font-monospace" style="font-size: 0.75rem;">
                                            <?php echo htmlspecialchars($q['category']); ?>
                                        </span>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi bi-three-dots"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="editQuestion(<?php echo $q['id']; ?>)"><i class="bi bi-pencil-square"></i> Edit Question</a></li>
                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="deleteQuestion(<?php echo $q['id']; ?>)"><i class="bi bi-trash3-fill"></i> Delete Question</a></li>
                                        </ul>
                                </div>    
                                    
                                    <small class="text-warning font-monospace">UID: #<?php echo $q['id']; ?></small>
                                </div>
                                <p class="text-white fw-medium mb-2 small"><?php echo htmlspecialchars($q['question_text']); ?></p>
                                
                                <div class="row g-1 text-warning x-small" style="font-size: 0.8rem;">
                                    <div class="col-6 <?php echo $q['correct_option'] === 'A' ? 'text-success fw-bold' : ''; ?>">A: <?php echo htmlspecialchars($q['option_a']); ?></div>
                                    <div class="col-6 <?php echo $q['correct_option'] === 'B' ? 'text-success fw-bold' : ''; ?>">B: <?php echo htmlspecialchars($q['option_b']); ?></div>
                                    <div class="col-6 <?php echo $q['correct_option'] === 'C' ? 'text-success fw-bold' : ''; ?>">C: <?php echo htmlspecialchars($q['option_c']); ?></div>
                                    <div class="col-6 <?php echo $q['correct_option'] === 'D' ? 'text-success fw-bold' : ''; ?>">D: <?php echo htmlspecialchars($q['option_d']); ?></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-5 text-warning">No configuration entries discovered inside core database tables.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
<script>
    /**
 * Asynchronously handles fetching and updating a specific quiz question via SweetAlert2
 * @param {number|string} id - The database primary key ID of the question
 */
async function editQuestion(id) {
    try {
        // 1. Fetch current question data from the server to populate the form
        const response = await fetch(`get_question.php?id=${id}`);
        if (!response.ok) throw new Error('Failed to retrieve question metrics.');
        const question = await response.json();

        if (question.error) {
            Swal.fire('Error', question.error, 'error');
            return;
        }

        // 2. Render SweetAlert2 form containing fields mapped to your schema
        const { value: formValues } = await Swal.fire({
            title: '<h4 class="text-info fw-bold mb-0">Modify Assessment Item</h4>',
            html: `
                <div class="text-start" style="color: #e2e8f0;">
                    <div class="mb-3">
                        <label class="small text-muted font-monospace">Question Body</label>
                        <textarea id="swal-question-text" class="form-control bg-dark text-light border-secondary" rows="2" required>${question.question_text}</textarea>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="small text-muted font-monospace">Option A</label>
                            <input id="swal-opt-a" class="form-control bg-dark text-light border-secondary form-control-sm" value="${question.option_a}" required>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted font-monospace">Option B</label>
                            <input id="swal-opt-b" class="form-control bg-dark text-light border-secondary form-control-sm" value="${question.option_b}" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="small text-muted font-monospace">Option C</label>
                            <input id="swal-opt-c" class="form-control bg-dark text-light border-secondary form-control-sm" value="${question.option_c}" required>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted font-monospace">Option D</label>
                            <input id="swal-opt-d" class="form-control bg-dark text-light border-secondary form-control-sm" value="${question.option_d}" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="small text-muted font-monospace">Correct Key Vector</label>
                        <select id="swal-correct" class="form-select bg-dark text-light border-secondary form-control-sm">
                            <option value="A" ${question.correct_option === 'A' ? 'selected' : ''}>A</option>
                            <option value="B" ${question.correct_option === 'B' ? 'selected' : ''}>B</option>
                            <option value="C" ${question.correct_option === 'C' ? 'selected' : ''}>C</option>
                            <option value="D" ${question.correct_option === 'D' ? 'selected' : ''}>D</option>
                        </select>
                    </div>
                </div>
            `,
            background: '#121622',
            showCancelButton: true,
            confirmButtonColor: '#2563eb',
            cancelButtonColor: '#475569',
            confirmButtonText: '<i class="bi bi-save me-1"></i> Save Changes',
            focusConfirm: false,
            preConfirm: () => {
                // Read and package values from the modal input nodes
                return {
                    id: id,
                    question_text: document.getElementById('swal-question-text').value.trim(),
                    option_a: document.getElementById('swal-opt-a').value.trim(),
                    option_b: document.getElementById('swal-opt-b').value.trim(),
                    option_c: document.getElementById('swal-opt-c').value.trim(),
                    option_d: document.getElementById('swal-opt-d').value.trim(),
                    correct_option: document.getElementById('swal-correct').value
                };
            }
        });

        // 3. Post updated values to the data layer processing file if validated
        if (formValues) {
            const saveResponse = await fetch('update_question.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formValues)
            });
            const result = await saveResponse.json();

            if (result.success) {
                Swal.fire({ title: 'Success!', text: result.message, icon: 'success', timer: 1500, showConfirmButton: false });
                setTimeout(() => location.reload(), 1500); // Reload page to reveal updates instantly
            } else {
                throw new Error(result.message || 'Error executing modification query.');
            }
        }
    } catch (error) {
        Swal.fire('Execution Deficient', error.message, 'error');
    }
}

/**
 * Warns operational admins prior to erasing a distinct testing variable structure from the server
 * @param {number|string} id - The database primary key ID of the target deletion row
 */
function deleteQuestion(id) {
    Swal.fire({
        title: 'Are you absolute?',
        text: "This operation completely removes this question profile index from active databases!",
        icon: 'warning',
        background: '#121622',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#475569',
        confirmButtonText: 'Yes, delete asset record!',
        cancelButtonText: 'Abort'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('delete_question.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ title: 'Purged!', text: data.message, icon: 'success', timer: 1500, showConfirmButton: false });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire('Halted', data.message, 'error');
                }
            })
            .catch(() => Swal.fire('Error', 'Connection matrix dropped during execution.', 'error'));
        }
    });
}
</script>