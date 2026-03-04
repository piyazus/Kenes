<?php
/**
 * ============================================================
 * AI_MODULE.PHP — Simulated KenesCloud AI Analysis Engine
 * ============================================================
 * Purpose:  Simulates an external AI module (KenesCloud) that analyzes
 *           loan applications and returns a risk assessment. In a
 *           production system, this would call an external API.
 *           Here it uses rule-based logic with randomized risk scoring.
 *
 * Input Parameters:
 *   - $application_id : The unique ID of the loan application
 *   - $service_type   : Type of loan (e.g., 'sme_business', 'microcredit')
 *   - $amount         : Requested loan amount in KZT
 *   - $purpose        : Description of how funds will be used
 *
 * Output (Associative Array):
 *   - analysis_json : JSON-encoded detailed breakdown
 *   - status        : 'approved' or 'rejected'
 *   - reason        : Human-readable explanation of the decision
 *
 * Risk Score Thresholds:
 *   - 300–499: Rejected       (high risk, score too low)
 *   - 500–649: Partial Approval (moderate risk, reduced amount)
 *   - 650–850: Full Approval  (strong profile, standard terms)
 * ============================================================
 */

/**
 * analyzeApplication()
 * Performs a simulated AI risk analysis on a loan application.
 *
 * @param int    $application_id   Unique application identifier
 * @param string $service_type     Type of lending service requested
 * @param float  $amount           Requested loan amount in KZT
 * @param string $purpose          Purpose of the loan (text description)
 * @return array  Contains 'analysis_json', 'status', and 'reason'
 */
function analyzeApplication($application_id, $service_type, $amount, $purpose)
{
    /**
     * SIMULATE API DELAY
     * In a real system, this would be the time taken for the
     * AI API to process the request. sleep(1) pauses execution
     * for 1 second to simulate network latency.
     */
    sleep(1);

    /**
     * GENERATE RISK SCORE
     * rand() generates a random integer between 300 and 850.
     * In production, this would come from a credit scoring algorithm
     * that analyzes financial history, collateral, and market data.
     */
    $risk_score = rand(300, 850);

    // Initialize default values
    $approved_amount = $amount;   // Start with full requested amount
    $interest_rate = 14.0;        // Default base interest rate (14%)

    /**
     * RULE-BASED DECISION ENGINE
     * Apply business rules based on the risk score:
     *
     * LOW SCORE (300-499):   Reject — financial risk is too high
     * MEDIUM SCORE (500-649): Partial approval — reduce amount, higher rate
     * HIGH SCORE (650-850):  Full approval — favorable terms
     */
    if ($risk_score < 500) {
        // HIGH RISK: Application is rejected
        $status = 'rejected';
        $approved_amount = 0;               // No funding approved
        $reason = "Risk score too low based on financial history.";

    } elseif ($risk_score < 650) {
        // MODERATE RISK: Approved with conditions
        $status = 'approved';
        $approved_amount = $amount * 0.8;   // 80% of requested amount
        $interest_rate = 18.5;              // Higher interest rate (18.5%)
        $reason = "Moderate risk. Approved with reduced amount.";

    } else {
        // LOW RISK: Full approval with favorable terms
        $status = 'approved';
        $interest_rate = 12.0;              // Lower interest rate (12%)
        $reason = "Strong profile. Full approval recommended.";
    }

    /**
     * CONSTRUCT ANALYSIS RESULT
     * Build a detailed associative array containing all analysis data.
     * This will be JSON-encoded for storage in the proposals table.
     * The 'key_factors' array lists the criteria used in the assessment.
     */
    $analysis_result = [
        'risk_score' => $risk_score,        // Numeric risk score (300-850)
        'recommended_status' => $status,            // 'approved' or 'rejected'
        'approved_amount' => $approved_amount,   // Final approved amount
        'interest_rate' => $interest_rate,     // Annual interest rate (%)
        'key_factors' => [                   // Factors considered in analysis
            'Credit History Length',
            'Debt-to-Income Ratio',
            'Sector Performance'
        ],
        'generated_at' => date('Y-m-d H:i:s')      // Timestamp of analysis
    ];

    /**
     * RETURN RESULTS
     * Return an array with:
     *   - analysis_json: JSON string for database storage
     *   - status: quick-access decision status
     *   - reason: human-readable explanation
     */
    return [
        'analysis_json' => json_encode($analysis_result),  // For DB storage
        'status' => $status,                         // Decision outcome
        'reason' => $reason                          // Explanation text
    ];
}
?>