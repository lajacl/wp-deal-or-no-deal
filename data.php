<?php
    $money_values = [
        0.01, 
        1, 
        5, 
        10, 
        25, 
        50, 
        75, 
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
        75000, 
        100000, 
        200000, 
        300000, 
        400000, 
        500000, 
        750000, 
        1000000
    ];

    
    global $prize_status;
    $prize_status = [];

    foreach ($money_values as $value) {
            $prize_status[] = ['amount' => $value, 'isSeen' => false];
    }
?>