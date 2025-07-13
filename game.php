<?php
    session_start();
    require 'data.php';

    function load_prize_board() {
        global $prize_status;
        
        if ($prize_status) {
            for($i = 0; $i < (count($prize_status) / 2); $i++) {
                echo '<tr>';
                echo '<td><span>$</span><span>'.number_format($prize_status[$i]['amount']).'</span></td>';
                echo '<td><span>$</span><span>'.number_format($prize_status[$i + (count($prize_status) / 2)]['amount']).'</span></td>';
                echo '</tr>';
            }
        }
    }    

    function display_cases() {
        global $cases;
        global $num_rows;
        global $num_cols;
        global $player_case;
        
        for ($i = $num_rows - 1; $i >= 0; $i--) {
            echo "<tr>";
            for($j = 0; $j < $num_cols; $j++) {
                $index = ($i * $num_cols) + $j;
                if ($cases[$index]["caseId"] != $player_case) {             
                    echo '<td class="case"><label><input type="radio" name="selected_case" value="'.$cases[$index]["caseId"].'" required><span class="case-num">'.$cases[$index]["caseId"].'</span><img class="case" src="assets/case.png" alt="briefcase"></label></td>';
                } else {
                    echo "<td></td>";
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
    <link rel="icon" href="assets/icon.jpg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deal or No Deal</title>
</head>

<body>    
    <form action="game.php" method="post">
        <?php echo '<input type="hidden" name="state" value="'.$game_state.'">'; ?>

        <div id="header">
            <?php if(isset($player_case)): ?>
                <span id="prompt">Choose a case:</span>
            <?php else: ?>
                <span id="prompt">First, choose your case:</span>
            <?php endif; ?>
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
                    <?php display_cases(); ?>                    
                    <tr id="selections">
                        <td id="buttons" colspan="4">
                            <button id="btn-select" type="submit">Confirm Choice</button>
                        </td>
                        <td id="label">Your Case:</td>
                        <td><span class="case-num"><?php echo $player_case; ?></span><img class="case" src="assets/case.png" alt="briefcase"></td>
                    </tr>
                </table>
            </div>
        </div>
    </form>
</body>

</html>