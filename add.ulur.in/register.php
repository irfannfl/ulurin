<?php
if($_SERVER["REQUEST_METHOD"] == "POST"){

    //RE-CAPTCHA
    $postfields = array('secret'=>'SECRET_TOKEN', 'response'=>$_POST['g-recaptcha-response']);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.google.com/recaptcha/api/siteverify');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $result = curl_exec($ch);
    $raw =  json_decode($result, true);
    if(!$raw['success']){
        $captcha_err = 'Failed captcha.';
    }else{

        //PASSED CAPTCHA
        require"backend/sql.php";

        // cek username
        if(empty(trim($_POST["username"]))){
            $username_err = "Tolong masukan username.";
        } else{
            $username = trim($_POST["username"]);

            $query = $db->prepare('SELECT id FROM users WHERE username = ?');
            $query->bind_param('s', $username);
            if ($query->execute()) {
                $query->store_result();
                if($query->num_rows > 0){
                    $username_err = "Username ini sudah diambil.";
                }
            }else{
                die("Gagal membaca database.");
            }
            $query->close();
        }
        
        // cek password
        if(empty(trim($_POST["password"]))){
            $password_err = "Tolong masukan password.";     
        } elseif(strlen(trim($_POST["password"])) < 6){
            $password_err = "Password minimal 6 karakter.";
        } else{
            $password = trim($_POST["password"]);
        }
        
        // cek confirm password
        if(empty(trim($_POST["confirm_password"]))){
            $confirm_password_err = "Tolong ulangi password anda.";     
        } else{
            $confirm_password = trim($_POST["confirm_password"]);
            if(empty($password_err) && ($password != $confirm_password)){
                $confirm_password_err = "Password tidak cocok.";
            }
        }
        
        // database insert
        if(empty($username_err) && empty($password_err) && empty($confirm_password_err)){
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $query = $db->prepare('INSERT INTO users (username, password) VALUES ( ?, ?)');
            $query->bind_param('ss', $username, $hash);
            if ($query->execute()) {
                $success=true;  //success
                unset($username_err,$password_err,$confirm_password_err);
            }else{
                die("Gagal membuat record di database.");
            }
        }
        
        // close connection
        $db->close();
    }
}
?>

<!DOCTYPE html><html>
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Free URL shortener, simple and easy">
    <meta name="keywords" content="url,Shortener,ulur.in,ulurin,ulur,shortlink">
	<meta name="theme-color" content="#181818">
	<title>Register | ULUR.IN Shortener</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
	<script type="text/javascript" src="assets/js/main.js"></script>
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>

<body onload='check()'>
    <div id="nav" class="topnav">
        <a class="icon" onclick="menu()">
			Menu<i class="glyphicon glyphicon-menu-hamburger"></i>
		</a>
        <a href="/">Home</a>
        <a href="user">User Panel</a>
        <a class="active" href="register">Register</a>
        <a href="dev.html">API Docs</a>
        <div id="toptext"></div>
        <div class="login-container"></div>
    </div>

    <div class="middle">
        <div style='text-align:center'>
            <?php 
				if($success==true){
					echo	"Registration success!, now you can login";
					exit;
				}
			?>

            Register
            <br>To create account, please fill this form.	
            <br><br><br>
        </div>
        <form style="text-align:left" onsubmit="regis.disabled = true" action="register" method="post">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has-error' : ''; ?>">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?php echo $username; ?>">
                <span class="help-block" style="color:#ff7575"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                <label>Password</label>
                <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                <span class="help-block" style="color:#ff7575"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                <span class="help-block" style="color:#ff7575"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="g-recaptcha" data-sitekey="SITE_KEY"></div>
            <span class="help-block" style="color:#ff7575"><?php echo $captcha_err; ?></span>
            <div class="form-group">
                <button type="submit" name="regis" class="btn btn-primary" value="Submit">Register</button>
            </div>
        </form>
    </div>
</body>
</html>