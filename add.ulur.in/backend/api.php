<?PHP
session_start();
include "ratelimiter.php";

$rateLimiter = new RateLimiter($_SERVER["REMOTE_ADDR"]);
$limit = 10;			//	limit jumlah request per $minutes
$minutes = 3;			//	periode pengecekan dalam menit
try {
	$rateLimiter->limitRequestsInMinutes($limit, $minutes);
} catch (RateExceededException $e) {
	$err = 'Spam terdeteksi!'; goto end;
}

if (isset($_POST['view'])) {

	//=============== Bagian view data =============== (Public API)
	$view = $_POST['view'];

	//Validasi authorisasi saat melihat history selain public
	if($view != 'public'){
		if ($_POST['token'] != 'TOKEN'){
			$err = 'Authorization failed!'; goto end;
		}
	}

	require "sql.php";
	
	$query = $db->prepare('SELECT id, url, date, hits FROM redirect WHERE user = ? ORDER BY date DESC');
	$query->bind_param('s', $view);
	if ($query->execute()) {
		$query->bind_result($id, $url, $date, $hits);

		$count = 1;		//awal nomor entri tabel
		$list = array();
		while ($query->fetch()) {
			$list[$count++] = ['id' => $id, 'url' => $url, 'hits' => $hits, 'date' => $date];
		}
	}else {
		$err = 'Gagal membaca database';
	}
	$query->close();
	$db->close();

} else if (isset($_POST['delete'])) {

	//=============== Bagian delete data =============== (Internal API)
	if (!isset($_SESSION['username'])) {
		$err = 'Authorization failed!'; goto end;
	}

	require "sql.php";
	
	$query = $db->prepare('DELETE FROM redirect WHERE user = ? AND id = ?');
	$query->bind_param('ss', $_SESSION['username'], $_POST['delete']);
	if ($query->execute()) {
		if($query->affected_rows<1){
			$err = 'Tidak ada entry di database';
		}
	}else {
		$err = 'Gagal mengedit database';
	}
	$query->close();
	$db->close();

} else if (isset($_POST['edit'])) {

	//=============== Bagian update data =============== (Internal API)
	if (!isset($_SESSION['username'])) {
		$err = 'Authorization failed!'; goto end;
	}

	$url = getURL();
	if($url['error']){
		$err = $url['error']; goto end;
	}

	require "sql.php";
	
	$query = $db->prepare('UPDATE redirect SET url = ? WHERE user = ? AND id = ?');
	$query->bind_param('sss', $url['validated'], $_SESSION['username'], $_POST['edit']);
	if ($query->execute()) {
		if($query->affected_rows<1){
			$err = 'Tidak ada perubahan';
		}
	}else {
		$err = 'Gagal mengedit database';
	}
	$query->close();
	$db->close();

} else {
	
	//=============== Bagian insert data =============== (Public API)
	$url = getURL();
	if($url['error']){
		$err = $url['error']; goto end;
	}

	if (isset($_SESSION['username'])) {
		$user = $_SESSION['username'];
	} else {
		$user = 'public';
	}
	
	require "sql.php";
	
	//Bagian Kelola Unique ID
	if(!isset($_POST['id']) || $_POST['random'] != 'false'){
		do{
			$id = substr(base64_encode(sha1(mt_rand())), 0, 5);
			$result = $db->query('SELECT url FROM redirect WHERE id = "' . $id . '" LIMIT 1');	
		} while ($result->num_rows > 0); 
	} else{
		$id = $_POST['id'];
		if(strlen($id)>30) {
			$err = 'ID terlalu panjang!'; goto end;
		}
		
		if(strlen($id)==0) {
			$err = 'ID terlalu pendek!'; goto end;
		}
		
		$id = preg_replace('/[^a-z0-9]/si', '', $id);
	
		$query = $db->prepare('SELECT url FROM redirect WHERE id = ? LIMIT 1');
		$query->bind_param('s', $id);
		if ($query->execute()) {
			$query->store_result();
			if($query->num_rows > 0){
				$err = 'Unique ID sudah dipakai!'; goto end;
			}
		}else{
			$query->close();
			$err = 'Gagal membaca database'; goto end;
		}
		$query->close();
	}
	
	//Bagian Insert ke DB
	$query = $db->prepare('INSERT INTO redirect (id, url, date, hits, user) VALUES ( ? , ?, NOW(), 0, ?)');
	$query->bind_param('sss', $id, $url['validated'], $user);
	if ($query->execute()) {
		$shortlink = 'ulur.in/' . $id;
	}else {
		$err = 'Gagal membuat record di database';
	}
	$query->close();
	$db->close();
}

function getURL(){	//Validasi URL dari POST
	if (!isset($_POST['url'])) {
		$msg = 'no request';
	}else{
		$url = urldecode(trim($_POST['url']));
		
		if(strlen($url)>620) {
			$msg = 'URL terlalu panjang!';
		}
		
		if(strpos($url, 'ulur.in/') === 0) {
			$msg = 'URL sudah shorturl!';
		}
	
		if (!preg_match('/(http[s]?:\/\/)?[^\s(["<,>]*\.[^\s[",><]*/si',$url)){
			$msg = 'URL Tidak Valid!';
		}
		
		if (strpos($url, "http://")===false && strpos($url, "https://")===false){
			$url = "http://" . $url;
		}
	}
	return array('error' => $msg, 'validated' => $url);
}

//Bagian akhir, response ke pemanggil
end:
header('Content-Type: application/json');

if(isset($_SERVER['HTTP_ORIGIN'])){
	header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);	//allow cross origin
	header('Access-Control-Allow-Credentials: true');
}

if(isset($err)){
	http_response_code(400);
	$data = ['error' => $err];
}else{
	if(isset($view)){
		$data = ['data' => $list];					//format respon json view data
	}else{
		$data = ['shortlink' => $shortlink];		//format respon json submit data
	}
}
echo json_encode($data);
