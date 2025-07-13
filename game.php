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
        
        for ($i = $num_rows - 1; $i >= 0; $i--) {
            echo "<tr>";
            for($j = 0; $j < $num_cols; $j++) {
                $index = ($i * $num_cols) + $j;              
                echo '<td><label><input type="radio" name="selected_case" value="'.$cases[$index]["caseId"].'" required><span class="case-num">'.$cases[$index]["caseId"].'</span><img class="case" src="assets/case.png" alt="briefcase"></label></td>';
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
    <form method="game.php" action="post">

        <div id="header">
            <span id="prompt">Choose your case</span>
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
                        <td colspan="4">
                            <button id="btn-select" type="submit">Confirm Choice</button>
                        </td>
                        <td>Your Case:</td>
                        <td><span class="case-num">?</span><img class="case" src="assets/case.png" alt="briefcase"></td>
                    </tr>
                </table>
            </div>
        </div>
    </form>
</body>

</html>