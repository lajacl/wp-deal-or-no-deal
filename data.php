<?php
    $num_cases = 24;
    $num_rows = 4;
    $num_cols = 6;   
    $cases_per_round = [6, 5, 4, 3, 2, 1, 1];
    $game_state;
    $game_state_idx;
    $round;
    $prize_status = [];
    $cases = [];
    $player_case;
    $banker_offer;
    $accept_offer;
    $offer_history = [];
    $player_prize;
    $player;

    if (isset($_POST['state'])) {
        switch($_POST['state']) {
            case "player_case":                
                loadSavedData();    
                setPlayerCase();
                break;
            case "round":                
                loadSavedData(); 
                updateRound();
                break;
            case "banker_offer":                
                loadSavedData(); 
                setBankerOffer();
                break;
            case "final_reveal":                
                loadSavedData(); 
                finalReveal();
                break;
            case "win_screen":                
                loadSavedData();
                break;
        }
    } else {
        resetGame();
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
        $_SESSION['game']['prize_status'] = $prize_status;

        shuffle($money_values);
        $cases = [];
        
        for ($i = 0; $i < $num_cases; $i++) {
            $cases[] = ["caseId" => $i + 1, "value" => $money_values[$i], "picked" => false];
        }        
        
        $_SESSION['game']['cases'] = $cases;
       
        updateGameState("player_case");
    }

    function loadSavedData() {
        global $game_state;
        global $prize_status;
        global $cases;
        global $player_case;
        global $round;
        global $banker_offer;
        global $accept_offer;
        global $offer_history;
        global $player_prize;
        global $player;

        if (isset($_SESSION['game']['state']))
            $game_state = $_SESSION['game']['state'];
        if (isset($_SESSION['game']['round']))
            $round = $_SESSION['game']['round'];
        if (isset($_SESSION['game']['prize_status']))
            $prize_status = $_SESSION['game']['prize_status'];
        if (isset($_SESSION['game']['cases']))
            $cases = $_SESSION['game']['cases'];
        if (isset($_SESSION['game']['player_case']))
            $player_case = $_SESSION['game']['player_case'];
        if (isset($_SESSION['game']['banker_offer']))
            $banker_offer = $_SESSION['game']['banker_offer'];
        if (isset($_SESSION['game']['accept_offer']))
            $accept_offer = $_SESSION['game']['accept_offer'];
        if (isset($_SESSION['game']['offer_history']))
            $offer_history = $_SESSION['game']['offer_history'];
        if (isset($_SESSION['game']['player_prize']))
            $player_prize = $_SESSION['game']['player_prize'];
        if (isset($_SESSION['player']))
            $player = $_SESSION['player'];
    }

    function resetGame() {
        global $game_state;
        global $player;       
        unset($_SESSION['game']);
        $game_state = "new_game";
        $player = $_SESSION['player'];
        setupGame();
    }

    function updateGameState($new_state) {
        global $game_state;

        $game_state = $new_state;
        $_SESSION['game']['state'] = $game_state;
    }

    function setPlayerCase() {
        global $player_case;
        global $cases;

        if (isset($_POST['selected_case'])) {
            $player_case = $cases[$_POST['selected_case'] - 1];
            $_SESSION['game']['player_case'] = $player_case;
            updateGameState("round");
            updateRound();                    
            updateCases();
        }
    }

    function updateRound() {
        global $cases_per_round;
        global $round;
        global $banker_offer;
        
        if (isset($_SESSION['game']['round'])) {
            updateCases();

            $round["to_open"] -= 1;
            if ($round["to_open"] <= 0) {  
                if (empty($banker_offer)) {
                    updateGameState("banker_offer");
                } elseif ($round['number'] < count($cases_per_round)) {             
                    $round['number'] += 1;
                    $round['to_open'] = $cases_per_round[$round["number"] - 1];
                    unset($_SESSION['game']['banker_offer']);
                } else {
                    updateGameState("final_reveal");
                }                
            }     
        } else {
            $round = ["number" => 1, "to_open" => $cases_per_round[0]];
        }
        $_SESSION['game']['round'] = $round;
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
        $_SESSION['game']['cases'] = $cases;
        
        if ($selected_case['caseId'] != $player_case['caseId']) {
            foreach ($prize_status as &$prize) {
                if ($prize['amount'] == $selected_case['value']) {
                    $prize['isSeen'] = true;
                    break;
                }
            }
        }
        $_SESSION['game']['prize_status'] = $prize_status;
    }

    function setBankerOffer() {
        global $prize_status;
        global $banker_offer;
        global $player_prize;
        global $offer_history;
        global $accept_offer;
        global $round;
        $remaining_num_cases;
        $remaining_values_sum;
        $remaining_average;

        if (!isset($_POST['accept_offer'])) {
            foreach (array_reverse($prize_status) as $prize) {
                if (!$prize['isSeen']) {
                    $remaining_num_cases += 1;
                    $remaining_values_sum += $prize['amount'];
                }
            }
            
            $remaining_average = $remaining_values_sum / $remaining_num_cases;
            $banker_offer = floor($remaining_average * $round["number"] / 10);
            $_SESSION['game']['banker_offer'] = $banker_offer;
        } else {
            $offer_history[] = $banker_offer;
            $_SESSION['game']['offer_history'] = $offer_history;
            
            $accept_offer = filter_var($_POST['accept_offer'], FILTER_VALIDATE_BOOLEAN);
            $_SESSION['game']['accept_offer'] = $accept_offer;

            if ($accept_offer) {
                $player_prize = $banker_offer;
                $_SESSION['game']['player_prize'] = $player_prize;
                updateGameState("final_reveal");
            } else {
                updateGameState("round");
                updateRound();
            }
        }
    }

    function finalReveal() {
        global $player_case;   
        global $player_prize;
        global $player;

        if (empty($player_prize)) {
            $player_prize = $player_case['value'];
            $_SESSION['game']['player_prize'] = $player_prize;
        }

        if (isset($_SESSION['users'])) {
            $userInfo = $_SESSION['users'][$player['username']];

            if (!isset($player['high_score']) || (isset($player['high_score']) && $player_prize > ($player['high_score']))) {                    
                date_default_timezone_set("America/New_York");
                $date = date("F j, Y g:i a");

                $userInfo['high_score'] = $player_prize;
                $userInfo['high_score_date'] = $date;
                $_SESSION['users'][$player['username']] = $userInfo;
                
                $player['high_score'] = $player_prize;
                $player['high_score_date'] = $date;
                $_SESSION['player'] = $player;
            }
        }
        
        if (isset($_POST['prize'])) {
            unset($_POST['prize']);
            updateGameState('win_screen');
        }
    }
?>