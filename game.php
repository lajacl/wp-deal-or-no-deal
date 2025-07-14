<?php
    session_start();
    if (!$_SESSION['player']) {
        header('Location: index.php?redirect');
    }
    require 'data.php';
    
    $decimal_seperator = '.';

    function displayPrompt() {
        global $player_case;
        global $round;
        global $game_state;
        global $banker_offer;
        global $accept_offer;
        global $player_prize;
        global $player;

        echo '<span id="prompt">';
        if ($game_state == "round" && $round["to_open"] > 0 && isset($player_case)) {
            echo $player["username"].', for round '.$round["number"].', choose '.$round["to_open"].' '.($round["to_open"] > 1 ? 'cases' : 'case').' (one at a time):';
        } elseif ($game_state == "player_case") {
            echo $player["username"].', choose your case first:';
        } elseif ($game_state == "banker_offer") {
            if (!$banker_offer) {
                echo $player["username"].', the Banker is calling, answer the phone:';
            } else {
                echo 'Banker: "I will offer you $'.number_format($banker_offer).' for your case. Is it a deal?"';
            }
        } elseif ($game_state == "final_reveal") {
            echo 'Let\'s see what your case '.($accept_offer ? 'was' : 'is').' worth:';
        } elseif ($game_state == "win_screen") {
            echo 'You won $'.number_format($player_prize).'!';
            echo "\t".($accept_offer ? '(Banker Deal Accepted)' : '(All Deals Rejected)');
        }
        echo '</span>';
    }

    function loadPrizeBoard() {
        global $prize_status;
        global $decimal_seperator;
        
        if ($prize_status) {
            for ($i = 0; $i < (count($prize_status) / 2); $i++) {
                echo '<tr>';
                echo (!$prize_status[$i]['isSeen'] ? '<td>' : '<td class="seen">').'<span>$</span><span>'.rtrim(rtrim(number_format($prize_status[$i]['amount'], 2), '0'), $decimal_seperator).'</span></td>';
                echo (!$prize_status[$i + (count($prize_status) / 2)]['isSeen'] ? '<td>' : '<td class="seen">').'<span>$</span><span>'.rtrim(rtrim(number_format($prize_status[$i + (count($prize_status) / 2)]['amount'], 2), '0'), $decimal_seperator).'</span></td>';
                echo '</tr>';
            }
        }
    }

    function displayBoard() {  
        global $game_state;
        global $num_rows;
        global $num_cols;
        global $banker_offer;
        global $offer_history;
        global $player_case;
        global $accept_offer;
        global $decimal_seperator;

        if ($game_state == "banker_offer" && isset($banker_offer))  {
            echo '<tr><td id="banker" colspan="'.(empty($offer_history) ? $num_cols : ($num_cols / 2)).'"><div><img src="assets/banker.gif" alt="The Banker"></div></td>';
            if (!empty($offer_history)) {
                echo '<td colspan="'.($num_cols / 2).'">';
                echo '<div id="past-offers"><h3>Past Banker Offers:</h3>';
                foreach ($offer_history as $offer) {
                    echo '$ '.number_format($offer).'<br><br>';
                }
            echo '</td>';
            }
            echo '</tr>';
        } elseif ($game_state == "final_reveal") {
            echo '<tr><td class="case-wrapper"><div id="reveal"><span id="closed"><img src="assets/case.png" alt="closed briefcase"><span class="case-num">'.$player_case["caseId"].'</span></span>';
            echo '<span id="open"><span id="case-val">$'.rtrim(rtrim(number_format($player_case["value"], 2), '0'), $decimal_seperator).'</span><img src="assets/case_open.png" alt="open briefcase"></span></div></td><tr>';

        } elseif ($game_state == "win_screen") {
            echo '<tr>';
            if ($accept_offer)  {
                echo '<td id="banker" colspan="'.($num_cols / 2).'"><div><img src="assets/banker.gif" alt="The Banker"></div></td>';
            } else {
                echo '<td class="case-wrapper" id="open" colspan="'.($num_cols / 2).'"><div id="reveal"><img src="assets/case_open.png" alt="open briefcase"><span id="case-val">$'.rtrim(rtrim(number_format($player_case["value"], 2), '0'), $decimal_seperator).
                '</span></div></td>';
            }
            echo '<td colspan="'.($num_cols / 2).'"><div id="past-offers"><h3>Past Banker Offers:</h3>';
            foreach ($offer_history as $offer) {
                echo '$ '.number_format($offer).'<br><br>';                
            }
            echo '</div></td></tr>';
        } else {
            displayCases();
        }
    }

    function displayCases() {
        global $cases;
        global $num_rows;
        global $num_cols;
        global $player_case;
        global $game_state;
        global $decimal_seperator;
        
        for ($i = $num_rows - 1; $i >= 0; $i--) {
            echo "<tr>";
            for ($j = 0; $j < $num_cols; $j++) {
                $index = ($i * $num_cols) + $j;

                if ($cases[$index]["caseId"] != $player_case['caseId'] && $cases[$index]["caseId"] == $_POST['selected_case']) {
                    echo '<td class="case-wrapper"><span id="case-closed"><img src="assets/case.png" alt="closed briefcase"><span class="case-num">'.$cases[$index]["caseId"].'</span></span>';
                    echo '<span id="case-open"><span id="case-val">$'.rtrim(rtrim(number_format($cases[$index]["value"], 2), '0'), $decimal_seperator).'</span>
                    <img src="assets/case_open.png" alt="open briefcase"></span></td>';
                } elseif (!$cases[$index]["picked"]) {             
                    echo '<td class="case-wrapper'.($game_state != "banker_offer" ? ' case-active' : '').'">';
                    if ($game_state != "banker_offer") {
                        echo '<label>';
                        echo '<input type="radio" name="selected_case" value="'.$cases[$index]["caseId"].'" required>';
                        echo '<img class="case" src="assets/case.png" alt="selected briefcase"><span class="case-num">'.$cases[$index]["caseId"].'</span>';
                        echo '</label>';
                    } else  {
                        echo '<img class="case" src="assets/case.png" alt="selected briefcase"><span class="case-num">'.$cases[$index]["caseId"].'</span>';
                    }
                    echo '</td>';
                } else {
                    echo '<td class="case-wrapper"><img class="case hidden-case" src="assets/case.png"></td>';
                }
            }
            echo "</tr>";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" type="text/css" href="game.css">
    <link rel="icon" href="assets/icon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deal or No Deal</title>
</head>

<body<?php if ($game_state == "player_case") { echo ' class="fade-in"'; } ?>>    
    <form action="game.php" method="post">
        <?php echo '<input type="hidden" name="state" value="'.$game_state.'">'; ?>

        <div id="header">
            <?php displayPrompt(); ?>
            <img id="banner" src="assets/banner.png" alt="banner">
        </div>

        <div id="main">
            <div id="prize_board">
                <table>
                    <?php loadPrizeBoard(); ?>
                </table>
            </div>
            <div id="board">
                <table>
                    <?php displayBoard(); ?>                    
                    <tr id="hud">                            
                        <?php if ($game_state == 'final_reveal' || $game_state == 'win_screen'): ?>
                            <?php if ($game_state == 'final_reveal'): ?>
                                <td id="buttons">
                                <button class="btn" type="submit" name="prize" value="true">See Prize Won</button></td>
                            <?php elseif ($game_state == 'win_screen'): ?>
                                <td id="buttons" colspan="6">
                                    <?php                                        
                                        if (isset($player['high_score'])) {
                                            echo '<div id="high-score">Your Highest Prize So Far:<br>$'.number_format($player['high_score']).' won on '.$player['high_score_date'].'</div><br>';
                                        }
                                    ?>
                                    <a href="credits.html"><button class="btn" type="button">Credits</button></a>
                                </td>
                            <?php endif; ?>                                       
                        <?php else: ?>                     
                            <td id="buttons" colspan="4"> 
                            <?php if ($game_state == "player_case" || $game_state == "round"): ?>
                                <button id="btn-confirm" class="btn" type="submit">Confirm Choice</button>                             
                            <?php elseif ($game_state == "banker_offer" && !$banker_offer): ?>
                                <input type="image" id="phone" src="assets/phone.png" alt="cell phone">                                                    
                            <?php elseif ($game_state == "banker_offer" && $banker_offer): ?>
                                <span>
                                    <button type="submit" name="accept_offer" value="true" class="round-btn">DEAL</button>
                                    <button type="submit" name="accept_offer" value="false" class="round-btn">NO DEAL</button>
                                </span>
                            <?php endif; ?>
                            </td>
                            <td id="case-label">Your Case:</td>
                            <td class="case-wrapper"><img class="case" src="assets/case.png" alt="briefcase"><span class="case-num"><?php echo isset($player_case) ? $player_case['caseId'] : '?'; ?></span></td>
                        <?php endif; ?>
                    </tr>
                </table>
            </div>
        </div>
    </form>
</body>

</html>