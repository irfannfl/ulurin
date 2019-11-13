<?PHP
session_start();

if(isset($_SESSION["username"])){
	$user = $_SESSION["username"];
}

if(isset($_GET['logout'])){
    //Destory session
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header("Location:".$_GET['logout']);
    exit;
}

header('Content-Type: application/json');

if(isset($_SERVER['HTTP_ORIGIN'])){
	header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);	//allow cross origin
	header('Access-Control-Allow-Credentials: true');
}

if(isset($user)){
    $data = ['username' => $user];
    echo json_encode($data);
}else{
    http_response_code(400);
    echo 'not logged-in';
}
