<?php
    session_start();    
    unset($_SESSION['game']);

    if(isset($_POST['logout'])) {
        unset($_SESSION['player']);
        unset($_POST['logout']);
    }

    $input_type;
    $username = "";
    $password = "";
    $users;
    $errorMsg;
    
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

    if (!empty($input_type) && !empty($username) && !empty($password)) {
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
            if (!$users && !array_key_exists($username, $users)) {
                $users[$username] = ["password" => $password, "create_date" => $date, "last_login" => $date];
                $_SESSION['users'] = $users;
                $_SESSION['player'] = $users[$username];
                $_SESSION['player']["username"] = $username;
                header('Location: game.php');
            } else {
                $errorMsg = "Username is not available. Please enter a different one.";
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
    <link rel="icon" href="assets/icon.jpg">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deal or No Deal</title>
</head>

<body>
    <?php echo '<div id="main"'.(!isset($_POST['play']) ? ' class="fade-in"': '').'>'; ?>
        <form action="index.php" method="post">
            <table id="input">
                <?php
                    if (isset($_POST['play']) && !empty($errorMsg)) {
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
        </form>
        <img id="intro" src="assets/bg_intro.webp" alt="background">
    </div>
</body>

</html>