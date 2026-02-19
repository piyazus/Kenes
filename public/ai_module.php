<?php
// ai_module.php - Simulates external AI analysis

function analyzeApplication($application_id, $service_type, $amount, $purpose)
{
    // Simulate API delay
    sleep(1);

    // Mock analysis logic based on rules
    $risk_score = rand(300, 850);
    $approved_amount = $amount;
    $interest_rate = 14.0;

    if ($risk_score < 500) {
        $status = 'rejected';
        $approved_amount = 0;
        $reason = "Risk score too low based on financial history.";
    } elseif ($risk_score < 650) {
        $status = 'approved';
        $approved_amount = $amount * 0.8; // Partial approval
        $interest_rate = 18.5;
        $reason = "Moderate risk. Approved with reduced amount.";
    } else {
        $status = 'approved';
        $interest_rate = 12.0;
        $reason = "Strong profile. Full approval recommended.";
    }

    $analysis_result = [
        'risk_score' => $risk_score,
        'recommended_status' => $status,
        'approved_amount' => $approved_amount,
        'interest_rate' => $interest_rate,
        'key_factors' => [
            'Credit History Length',
            'Debt-to-Income Ratio',
            'Sector Performance'
        ],
        'generated_at' => date('Y-m-d H:i:s')
    ];

    return [
        'analysis_json' => json_encode($analysis_result),
        'status' => $status,
        'reason' => $reason
    ];
}
?>