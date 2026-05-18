<?php
/**
 * IntelliMeteo Competency Assessment Engine (Dynamic DB Refactor)
 */

class AssessmentEngine {
    
    private $db;

    public function __construct($pdoInstance) {
        $this->db = $pdoInstance;
    }

    /**
     * Pulls random validation questions from the database for a specific category
     */
    public function getRandomQuestions($category, $limit = 5) {
        $stmt = $this->db->prepare("
            SELECT id, category, question_text, option_a, option_b, option_c, option_d 
            FROM quiz_questions 
            WHERE category = :cat AND status = 1 
            ORDER BY RAND() 
            LIMIT :lim
        ");
        
        // Bind parameters safely ensuring data types map properly for LIMIT clauses
        $stmt->bindValue(':cat', $category, PDO::PARAM_STR);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Backend/Admin: Commits a newly compiled question to the persistent database matrix
     */
    public function addQuestion($category, $text, $a, $b, $c, $d, $correct) {
        $stmt = $this->db->prepare("
            INSERT INTO quiz_questions (category, question_text, option_a, option_b, option_c, option_d, correct_option, status)
            VALUES (:cat, :txt, :oa, :ob, :oc, :od, :correct, 1)
        ");
        return $stmt->execute([
            'cat'     => strtolower($category),
            'txt'     => $text,
            'oa'      => $a,
            'ob'      => $b,
            'oc'      => $c,
            'od'      => $d,
            'correct' => strtoupper($correct)
        ]);
    }

    /**
     * Evaluates submissions by cross-checking submitted IDs against real answers in the DB
     */
    public function evaluateSubmission($userId, $category, $userAnswers) {
        if (empty($userAnswers)) return ['score' => 0, 'total' => 0, 'percentage' => 0];

        $totalQuestions = count($userAnswers);
        $scoreAchieved = 0;

        // Extract raw item keys safely
        $questionIds = array_keys($userAnswers);
        $clausePlaceholders = implode(',', array_fill(0, count($questionIds), '?'));

        // Query real values for precise evaluation checks
        $stmt = $this->db->prepare("SELECT id, correct_option FROM quiz_questions WHERE id IN ($clausePlaceholders)");
        $stmt->execute($questionIds);
        $realAnswers = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($userAnswers as $qId => $userChoice) {
            if (isset($realAnswers[$qId]) && strtoupper($userChoice) === strtoupper($realAnswers[$qId])) {
                $scoreAchieved++;
            }
        }

        $percentage = ($totalQuestions > 0) ? ($scoreAchieved / $totalQuestions) * 100 : 0;

        // Log results to user history logs table
        $stmtLog = $this->db->prepare("
            INSERT INTO assessment_scores (user_id, category, score_achieved, total_questions, percentage) 
            VALUES (:uid, :cat, :score, :total, :pct)
        ");
        $stmtLog->execute([
            'uid'   => $userId,
            'cat'   => ucfirst($category),
            'score' => $scoreAchieved,
            'total' => $totalQuestions,
            'pct'   => round($percentage, 2)
        ]);

        return [
            'score' => $scoreAchieved,
            'total' => $totalQuestions,
            'percentage' => round($percentage, 2)
        ];
    }

    public function getUserHistory($userId) {
        $stmt = $this->db->prepare("SELECT category, score_achieved, total_questions, percentage, attempted_at FROM assessment_scores WHERE user_id = :uid ORDER BY attempted_at DESC");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllQuestionsForAdmin() {
        $stmt = $this->db->query("SELECT * FROM quiz_questions WHERE status = 1 ORDER BY category ASC, id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}