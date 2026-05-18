<?php
/**
 * IntelliMeteo Competency Assessment Engine
 * Validates operational mastery over WMO, ICAO Annex 3, and specialized weather regimes.
 */

class AssessmentEngine {
    
    private $db;

    public function __construct($pdoInstance) {
        $this->db = $pdoInstance;
    }

    /**
     * Centralized Question Repository structured by domain specialization
     */
    public static function getQuestionBank() {
        return [
            'aviation' => [
                [
                    'id' => 'av_1',
                    'question' => 'According to ICAO Annex 3 standards, a surface wind speed of 2 knots or less with variable direction must be coded using which format?',
                    'options' => [
                        'A' => '00000KT',
                        'B' => 'VRB02KT',
                        'C' => 'CLM02KT',
                        'D' => 'Variable winds are omitted from reports below 5 knots'
                    ],
                    'correct' => 'B'
                ],
                [
                    'id' => 'av_2',
                    'question' => 'Under what specific structural condition can the CAVOK operational shortcut override horizontal visibility and cloud parameters?',
                    'options' => [
                        'A' => 'Visibility ≥ 10km, no convective clouds, and no significant weather changes.',
                        'B' => 'Visibility ≥ 10km, no clouds below 5,000 feet (or highest minimum sector altitude), and no operational significant weather.',
                        'C' => 'Clear skies below 10,000 feet absolute altitude.',
                        'D' => 'When a high-pressure anticyclone cell settles completely over the terminal airfield indicators.'
                    ],
                    'correct' => 'B'
                ],
                [
                    'id' => 'av_3',
                    'question' => 'If a TAF contains the weather token "OVC015CB", what critical hazard warning is implicitly delivered to flight dispatchers?',
                    'options' => [
                        'A' => 'An automated sensor error caused an invalid cloud layer output.',
                        'B' => 'Overcast layer with present visibility restrictions due to coastal sea breeze advection.',
                        'C' => 'Overcast cloud layer at 1,500 feet containing hazardous Cumulonimbus storm structures.',
                        'D' => 'Cirrostratus cloud bands developing over mountain wave terrain lines.'
                    ],
                    'correct' => 'C'
                ]
            ],
            'general' => [
                [
                    'id' => 'gen_1',
                    'question' => 'What meteorological outcome is expected when the ambient air temperature drops to match the dew point temperature?',
                    'options' => [
                        'A' => 'Rapid atmospheric evaporation begins instantly.',
                        'B' => 'Relative humidity drops to 0%, inducing clear sky conditions.',
                        'C' => 'The air reaches 100% saturation, forcing water vapor condensation into mist, fog, or dew.',
                        'D' => 'Barometric pressure increases rapidly causing sudden katabatic drainage winds.'
                    ],
                    'correct' => 'C'
                ],
                [
                    'id' => 'gen_2',
                    'question' => 'The process by which solar energy converts ground moisture directly into atmospheric vapor, driving early morning convection cycles, is termed:',
                    'options' => [
                        'A' => 'Sublimation Matrix',
                        'B' => 'Adiabatic expansion',
                        'C' => 'Evapotranspiration',
                        'D' => 'Thermodynamic Advection'
                    ],
                    'correct' => 'C'
                ]
            ]
        ];
    }

    /**
     * Processes raw exam submittals, grades performance percentages, and logs records.
     */
    public function evaluateSubmission($userId, $category, $userAnswers) {
        $bank = self::getQuestionBank();
        if (!isset($bank[$category])) return false;

        $questions = $bank[$category];
        $totalQuestions = count($questions);
        $scoreAchieved = 0;

        foreach ($questions as $q) {
            $qId = $q['id'];
            if (isset($userAnswers[$qId]) && $userAnswers[$qId] === $q['correct']) {
                $scoreAchieved++;
            }
        }

        $percentage = ($totalQuestions > 0) ? ($scoreAchieved / $totalQuestions) * 100 : 0;

        // Persist attempt metrics to database logs
        $stmt = $this->db->prepare("
            INSERT INTO assessment_scores (user_id, category, score_achieved, total_questions, percentage) 
            VALUES (:uid, :cat, :score, :total, :pct)
        ");
        
        $stmt->execute([
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

    /**
     * Collects past performance history for analytics rendering
     */
    public function getUserHistory($userId) {
        $stmt = $this->db->prepare("
            SELECT category, score_achieved, total_questions, percentage, attempted_at 
            FROM assessment_scores 
            WHERE user_id = :uid 
            ORDER BY attempted_at DESC
        ");
        $stmt->execute(['uid' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}