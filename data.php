<?php
    $game_state;
    $prize_status = [];
    $num_cases = 24;
    $num_rows = 4;
    $num_cols = 6;   
    $cases = [];
    $player_case = '?';
    $round;

    if (isset($_POST['state'])) {
        switch($_POST['state']) {
            case "new_game":
                setupGame();
                break;
            case "player_case":
                setPlayerCase();
                break;
        }
    }

    function setupGame() {
        global $prize_status;
        global $cases;
        global $num_cases;

        $money_values = [
            0.01, 
            1, 
            5, 
            10, 
            25, 
            50,
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
        $_SESSION["prize_status"] = $prize_status;

        shuffle($money_values);
        $cases = [];
        
        for ($i = 0; $i < $num_cases; $i++) {
            $cases[] = ["caseId" => $i + 1, "value" => $money_values[$i]];
        }        
        
        $_SESSION["cases"] = $cases;
       
        updateGameState("player_case");
    }

    function loadSavedData() {
        global $prize_status;
        global $cases;
        global $player_case;

        if(isset($_SESSION["prize_status"]))
            $prize_status = $_SESSION["prize_status"];
        if(isset($_SESSION["cases"]))
            $cases = $_SESSION["cases"];
        if(isset($_SESSION["player_case"]))
            $player_case = $_SESSION["player_case"];
    }

    function updateGameState($new_state) {
        global $game_state;

        $game_state = $new_state;
        $_SESSION["game_state"] = $new_state;
    }

    function setPlayerCase() {
        global $player_case;

        loadSavedData();

        if (isset($_POST['selected_case'])) {
            $player_case = $_POST['selected_case'];
            $_SESSION['player_case'] = $player_case;
        }
        
        updateGameState("round");
        $round = 1;
    }
?>