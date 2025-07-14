<?php
    session_start();
    require 'data.php';
    
    $decimal_seperator = '.';

    function load_prize_board() {
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

                  
        echo '<tr>';
        if ($game_state == "banker_offer" && isset($banker_offer))  {
            echo '<td colspan="'.($num_cols / 2).'" rowspan="'.$num_rows.'"><div id="banker"><img src="assets/banker.gif" alt="The Banker"></div></td>';
            echo '<td colspan="'.($num_cols / 2).'" rowspan="'.$num_rows.'"><div id="past-offers"> Previous Offers:<br><br>';
            foreach ($offer_history as $offer) {
                echo '$ '.number_format($offer).'<br><br>';
            }
            echo '</div></td>';
        } elseif ($game_state == "final_reveal") {
            echo '<td colspan="'.$num_cols.'" rowspan="'.$num_rows.'"><div id="final-reveal"><span id="closed"><span class="case-num">'.$player_case["caseId"].'</span><img src="assets/case.png" alt="closed briefcase"></span>';
            echo '<span id="open"><span id="case-val">$'.rtrim(rtrim(number_format($player_case["value"], 2), '0'), $decimal_seperator).'</span><img src="assets/case_open.png" alt="open briefcase"></span></div></td>';

        } elseif ($game_state == "win_screen") {
            if ($accept_offer)  {
                echo '<td colspan="'.($num_cols / 2).'" rowspan="'.$num_rows.'"><div id="banker"><img src="assets/banker.gif" alt="The Banker"></div></td>';
            } else {
                echo '<td id="open" colspan="'.($num_cols / 2).'" rowspan="'.$num_rows.'"><span id ="case-open"><span id="case-val">$'.rtrim(rtrim(number_format($player_case["value"], 2), '0'), $decimal_seperator).
                '</span><img src="assets/case_open.png" alt="open briefcase"></span></td>';
            }
            echo '<td colspan="'.($num_cols / 2).'" rowspan="'.$num_rows.'"><div id="past-offers"> Previous Offers:<br><br>';
            foreach ($offer_history as $offer) {
                echo '$ '.number_format($offer).'<br><br>';                
            }
            echo '</div></td>';
        } else {
            displayCases();
        }  
        echo '</tr><tr><td></td><td></td><td></td><td></td><td></td><td></td></tr><tr>';
        echo '<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
        echo '<tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
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
                    echo '<td><span id="case-closed"><span class="case-num">'.$cases[$index]["caseId"].'</span><img src="assets/case.png" alt="closed briefcase"></span>';
                    echo '<span id ="case-open"><span id="case-val">$'.rtrim(rtrim(number_format($cases[$index]["value"], 2), '0'), $decimal_seperator).'</span><img src="assets/case_open.png" alt="open briefcase"></span></td>';
                } elseif (!$cases[$index]["picked"]) {             
                    echo '<td';
                    if ($game_state != "banker_offer") {
                        echo ' class="case"><label><input type="radio" name="selected_case" value="'.$cases[$index]["caseId"].'" required>';
                    } else {
                        echo '>';
                    }
                    echo '<span class="case-num">'.$cases[$index]["caseId"].'</span><img class="case" src="assets/case.png" alt="briefcase">';
                    if ($game_state != "banker_offer") {
                        echo '</label>';
                    }
                    echo '</td>';
                } else {
                    echo '<td><img class="case hidden-case" src="assets/case.png" alt="briefcase"></td>';
                }
            }
            echo "</tr>";
        }
    }

    function displayPrompt() {
        global $player_case;
        global $round;
        global $game_state;
        global $banker_offer;
        global $accept_offer;
        global $player_winnings;

        echo '<span id="prompt">';
        if ($game_state == "round" && $round["to_open"] > 0 && isset($player_case)) {
            echo 'For round '.$round["number"].', choose '.$round["to_open"].' '.($round["to_open"] > 1 ? 'cases' : 'case').' (one at a time):';
        } elseif (!isset($player_case)) {
            echo 'First, choose your case:';
        } elseif ($game_state == "banker_offer") {
            if (!$banker_offer) {
                echo 'The Banker is calling, answer the phone:';
            } else {
                echo 'Banker: "I will offer you $'.number_format($banker_offer).' for your case. Is it a deal?"';
            }
        } elseif ($game_state == "final_reveal") {
            echo 'Let\'s see what your case '.($accept_offer ? 'was' : 'is').' worth:';
        } elseif ($game_state == "win_screen") {
            echo 'You won $'.$player_winnings.'!';
        }
        echo '</span>';
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" type="text/css" href="game.css">
    <link rel="icon" href="assets/icon.jpg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deal or No Deal</title>
</head>

<body>    
    <form action="game.php" method="post">
        <?php echo '<input type="hidden" name="state" value="'.$game_state.'">'; ?>

        <div id="header">
            <?php displayPrompt(); ?>
            <img id="banner" src="assets/banner.png" alt="banner">
        </div>

        <div id="main">
            <div id="amounts">
                <table>
                    <?php load_prize_board(); ?>
                </table>
            </div>
                <div id="options">
                    <table>
                        <?php displayBoard(); ?>                    
                        <tr id="selections">
                            <?php if ($game_state == 'final_reveal' || $game_state == 'win_screen'): ?>
                                <td id="buttons" colspan="6">    
                            <?php else: ?>                                        
                                <td id="buttons" colspan="4"> 
                            <?php endif; ?>  
                                <?php if ($game_state == "player_case" || $game_state == "round"): ?>
                                    <button id="btn-select" class="btn" type="submit">Confirm Choice</button>                             
                                <?php elseif ($game_state == "banker_offer" && !$banker_offer): ?>
                                    <input type="image" id="phone" src="assets/phone.png" alt="Cell Phone">                                                    
                                <?php elseif ($game_state == "banker_offer" && $banker_offer): ?>
                                    <button type="submit" name="accept_offer" value="true" class="round-btn">DEAL</button>
                                    <button type="submit" name="accept_offer" value="false" class="round-btn">NO DEAL</button>
                                <?php elseif ($game_state == "final_reveal"): ?>
                                    <button class="btn" type="submit" name="end" value="true">End Game</button>
                                <?php elseif ($game_state == "win_screen"): ?>
                                    <a href="credits.html"><button class="btn" type="button">Credits</button></a>
                                <?php endif; ?>
                                </td>
                            <td id="case-label"><?php echo ($game_state != 'final_reveal' && $game_state != 'win_screen') ? 'Your Case:' : ''; ?></td>
                            <td>
                                <?php if ($game_state != 'final_reveal' && $game_state != 'win_screen'): ?>
                                    <span class="case-num"><?php echo isset($player_case) ? $player_case['caseId'] : '?'; ?></span><img class="case" src="assets/case.png" alt="briefcase">
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
            </div>
        </div>
    </form>
</body>

</html>