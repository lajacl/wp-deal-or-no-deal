<?php
    global $prize_status;
    global $num_cases;
    global $num_rows;
    global $num_cols;    
    global $cases;

    $prize_status = [];
    $num_cases = 24;
    $num_rows = 4;
    $num_cols = 6;

    $money_values = [
        0.01, 
        1, 
        5, 
        10, 
        25, 
        50, 
        // 75, 
        100, 
        200, 
        300, 
        400, 
        500, 
        750, 
        1000, 
        5000, 
        10000, 
        25000, 
        50000, 
        // 75000, 
        100000, 
        200000, 
        300000, 
        400000, 
        500000, 
        750000, 
        1000000
    ];

    foreach ($money_values as $value) {
            $prize_status[] = ['amount' => $value, 'isSeen' => false];
    }

    shuffle($money_values);
    $cases = [];
    for ($i = 0; $i < $num_cases; $i++) {
        $cases[] = ["caseId" => $i + 1, "value" => $money_values[$i]];
    }

    $_SESSION["cases"] = $cases;
?>