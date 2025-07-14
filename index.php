<?php
    session_start();    
    unset($_SESSION['game']);

    if(isset($_POST['logout'])) {
        unset($_SESSION['player']);
        unset($_POST['logout']);
    }

    if(isset($_GET['redirect']) && isset($_POST['play'])) {
        unset($_GET['redirect']);
    }

    $input_type;
    $username = "";
    $password = "";
    $users;
    $errorMsg;
    $rules;
    $show_rules;
    
    date_default_timezone_set("America/New_York");
    $date = date("F j, Y g:i a");
    
    if (isset($_POST['input_type'])) {
        $input_type = $_POST['input_type'];
    }
    
    if (isset($_POST['username'])) {
        $username = trim($_POST['username']);
    }
    
    if (isset($_POST['password'])) {
        $password = trim($_POST['password']);
    }
    
    $users = isset($_SESSION['users']) ? $_SESSION['users'] : [];

    if(isset($_POST['rules'])) {
        $show_rules = ($_POST['rules'] == "show") ? true : false;
        unset($_POST['rules']);
    } elseif(isset($_GET['redirect'])) {
        $errorMsg = "Please login to play.";
    } elseif (!empty($input_type) && !empty($username) && !empty($password)) {
        if ($input_type == "login") {
            if (!empty($users) && array_key_exists($username, $users) && ($users[$username]["password"] == $password)) {
                $users[$username]["last_login"] = $date;
                $_SESSION['users'] = $users;
                $_SESSION['player'] = $users[$username];
                $_SESSION['player']["username"] = $username;
                header('Location: game.php');
            } else {
                $errorMsg = "Login username or password invalid.";
            }
        }  elseif ($input_type == "register") {
            if (!$users || !array_key_exists($username, $users)) {
                $users[$username] = ["password" => $password, "create_date" => $date, "last_login" => $date];
                $_SESSION['users'] = $users;
                $_SESSION['player'] = $users[$username];
                $_SESSION['player']["username"] = $username;
                header('Location: game.php');
            } else {
                $errorMsg = "Username is not available.";
            }
        }
    } else {
        $errorMsg = "Please complete all fields.";
    }
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" type="text/css" href="intro.css">
    <link rel="icon" href="assets/icon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deal or No Deal</title>
</head>

<body<?php echo (!isset($_POST['play']) && !isset($show_rules) ? ' class="fade-in"': '').'>'; ?>>
    <div id="main">
    <form action="index.php" method="post">
        <table id="login">
            <?php
                if ((isset($_POST['play']) || isset($_GET['redirect'])) && !empty($errorMsg)) {
                    echo '<tr id ="error"><td colspan="2">'.$errorMsg.'</td></tr>';
                }
            ?>
            <tr>
                <td>
                    <?php echo '<input type="text" name="username" placeholder="Username" value="'.$username.'">'; ?>
                </td>
                <td></td>
            </tr>
            <tr>
                <td>
                    <?php echo '<input type="password" name="password" placeholder="Password" value="'.$password.'"></td>'; ?>
                <td>
                    <button id="btn-play" type="submit" name="play" value="true">PLAY GAME</button>
                </td>
            </tr>
            <tr>
                <td>
                    <?php
                        echo '<label><input type="radio" name="input_type" value="login" required'.((empty($input_type) || $input_type) == "login" ? " checked" : "").'>Login</label>';
                        echo '<label><input type="radio" name="input_type" value="register" required'.((!empty($input_type) && $input_type == "register") ? " checked" : "").'>Register</label>';
                    ?>
                </td>
                <td></td>
            </tr>
        </table>

        <img id="intro" src="assets/bg_intro.webp" alt="game background">
        
        <button id="btn-rules" type="submit" name="rules" value="show">RULES</button>
        <div <?php echo 'class="rules'.($show_rules ? ' show': '').'"'; ?>>
            <div id="rules-close"><input type="submit" name="rules" value="&times"></div>
            <h1>Deal or No Deal - Game Rules</h1>

            <h2>Overview</h2>
            <p>In "Deal or No Deal," a contestant chooses one briefcase from 24, each containing a different dollar amount. The goal is to eliminate other briefcases, revealing their values and ultimately aiming for the highest possible prize (often $1,000,000). Periodically, "The Banker" offers a cash amount to buy the contestant's briefcase; the contestant then decides whether to accept the "Deal" or reject it for the chance to reveal higher amounts in later rounds (a "No Deal" decision). If the contestant rejects all offers, they keep the amount in their chosen briefcase.</p>
            
            <h2>Briefcases:</h2>
            <p>24 briefcases, each containing a unique dollar amount, ranging from the smallest amount $0.01 to the largest amount $1,000,000.</p>

            <h2>Choosing a Case:</h2>
            <p>The game starts with the contestant selecting one briefcase, which remains theirs until the end of the game.</p>

            <h2>Elimination Rounds:</h2>
            <p>The contestant opens a set number of other briefcases in each round, revealing their values and eliminating those amounts from play.</p>

            <h2>The Banker's Offer:</h2>
            <p>After each round, The Banker makes a cash offer to buy the contestant's briefcase.</p>

            <h2>"Deal or No Deal":</h2>
            <p>The contestant must decide whether to accept The Banker's offer ("Deal") or reject it ("No Deal").</p>

            <h2>Continuing or Ending:</h2>
            <p>If the contestant accepts the "Deal," they win the offered amount and the game ends. If they choose "No Deal," the game continues with more briefcase eliminations and potentially a new Banker's offer.</p>

            <h2>Final Round:</h2>
            <p>If the contestant rejects all offers, they keep the amount in their chosen briefcase.</p>

            <h2>Winning:</h2>
            <p>The contestant wins the amount from The Banker if they accept an offer or the amount of money revealed in their chosen final briefcase if no Banker offers are accepted.</p>
        </div>
    </form>
    </div>
</body>

</html>