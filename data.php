<?php
    $game_state;
    $prize_status = [];
    $num_cases = 24;
    $num_rows = 4;
    $num_cols = 6;   
    $cases = [];
    $player_case;
    $round;
    $cases_per_round = [6, 5, 4, 3, 2, 1, 1];
    $banker_offer;
    $player_winnings;

    if (isset($_POST['state'])) {
        switch($_POST['state']) {
            case "new_game":
                resetGame();
                setupGame();
                break;
            case "player_case":
                setPlayerCase();
                break;
            case "round":
                updateRound();
                break;
            case "banker_offer":
                setBankerOffer();
                break;
            case "final_reveal":
                finalReveal();
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
            $cases[] = ["caseId" => $i + 1, "value" => $money_values[$i], "picked" => false];
        }        
        
        $_SESSION["cases"] = $cases;
       
        updateGameState("player_case");
    }

    function loadSavedData() {
        global $prize_status;
        global $cases;
        global $player_case;
        global $game_state;
        global $round;

        if(isset($_SESSION["prize_status"]))
            $prize_status = $_SESSION["prize_status"];
        if(isset($_SESSION["cases"]))
            $cases = $_SESSION["cases"];
        if(isset($_SESSION["player_case"]))
            $player_case = $_SESSION["player_case"];
        if(isset($_SESSION["game_state"]))
            $game_state = $_SESSION["game_state"];
        if(isset($_SESSION["round"]))
            $round = $_SESSION["round"];
        if(isset($_SESSION["banker_offer"]))
        if(isset($_SESSION["player_winnings"]))
            $player_winnings = $_SESSION["player_winnings"];
    }

    function resetGame() {
        unset($_SESSION["prize_status"]);
        unset($_SESSION["cases"]);
        unset($_SESSION["player_case"]);
        unset($_SESSION["game_state"]);
        unset($_SESSION["round"]);
        unset($SESSION["banker_offer"]);
        unset($SESSION["player_winnings"]);
    }

    function updateGameState($new_state) {
        global $game_state;

        $game_state = $new_state;
        $_SESSION["game_state"] = $game_state;
    }

    function setPlayerCase() {
        global $player_case;

        loadSavedData();

        if (isset($_POST['selected_case'])) {
            $player_case = $_POST['selected_case'];
            $_SESSION['player_case'] = $player_case;
            updateGameState("round");
            updateRound();                    
            updateCases();
        }
    }

    function updateRound() {
        global $cases_per_round;
        global $round;
        
        if(isset($_SESSION["round"])) {
            loadSavedData();
            updateCases();

            $round = $_SESSION["round"];

            $round["to_open"] -= 1;

            if ($round["to_open"] == 0) {                
                if ($round["number"] < count($cases_per_round)) {                    
                    $round["number"] += 1;
                    $round["to_open"] = $cases_per_round[$round["number"] - 1];
                    updateGameState("banker_offer");
                    unset($SESSION["banker_offer"]);
                } else {
                    updateGameState("final_reveal");
                }                
            }            
        } else {
            $round = ["number" => 1, "to_open" => $cases_per_round[0]];
        }
        $_SESSION["round"] = $round;
    }

    function updateCases() {
        global $cases;
        global $player_case;
        global $prize_status;
        $selected_case;

        foreach ($cases as &$case) {
            if ($case['caseId'] == $_POST['selected_case']) {
                $case['picked'] = true;
                $selected_case = $case;
                break;
            }
        }
        $_SESSION["cases"] = $cases;
        
        if ($selected_case['caseId'] != $player_case) {
            foreach($prize_status as &$prize) {
                if ($prize['amount'] == $selected_case['value']) {
                    $prize['isSeen'] = true;
                    break;
                }
            }
        }
        $_SESSION['prize_status'] = $prize_status;
    }

    function setBankerOffer() {
        global $prize_status;
        global $banker_offer;
        global $player_winnings;
        $remaining_num_cases;
        $remaining_values_sum;
        $remaining_average;
        $remaining_max;

        loadSavedData();
        if(!isset($_POST["accept_offer"])) {
            foreach (array_reverse($prize_status) as $prize) {
                if (!$prize['isSeen']) {
                    $remaining_num_cases += 1;
                    $remaining_values_sum += $prize['amount'];
                    if($prize['amount'] > $remaining_max) {
                        $remaining_max = $prize['amount'];
                    }
                }
            }
            $remaining_average = $remaining_values_sum / $remaining_num_cases;

            $banker_offer = floor($remaining_average);
            $_SESSION['banker_offer'] = $banker_offer;
        } else {
            if(filter_var($_POST["accept_offer"], FILTER_VALIDATE_BOOLEAN)) {
                $player_winnings = $banker_offer;
                $_SESSION['player_winnings'] = $player_winnings;
                updateGameState("final_reveal");
            } else {
                updateGameState("round");
                updateRound();
            }
        }
    }

    function finalReveal() {        
        loadSavedData();
        echo "At Final Reveal";
    }
?>