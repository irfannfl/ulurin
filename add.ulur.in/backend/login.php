<?php
session_start();
 
// Kalo dah login lempar ke homepage
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: user");
    exit;
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // cek username kosong
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // cek pass kosong
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }

    require "sql.php";
    
    // Validate user pass
    if(empty($username_err) && empty($password_err)){
        $query = $db->prepare('SELECT password FROM users WHERE username = ?');
        $query->bind_param('s', $username);
        if ($query->execute()) {
            $query->bind_result($hash);
            $query->fetch();
            if(password_verify($password, $hash)){
                
                // set session var
                $_SESSION["loggedin"] = true;
                $_SESSION["username"] = $username;                            
                
                // Redirect ke homepage
                $db->close();
                header("location: user");
            } else{
                //$password_err = "The password you entered was not valid.";
                if(isset($_POST["origin"])){
                    header("location: ".$_POST["origin"]."?err=".$username);   //balik ke origin page
                }else{
                    header("location: /?err=".$username);   //balik ke mainpage
                }
            }
        }else {
            Die("Internal server error");
        }
        $query->close();
    }
    $db->close();
}
?>


<!--legacy login page, you are viewing this page because you don't include POST request-->
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control">
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
        </form>
    </div>    
</body>
</html>