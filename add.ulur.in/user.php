<?PHP
session_start();

if(isset($_SESSION["username"])){
	$user = $_SESSION["username"];
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
	<title>User Panel | ULUR.IN Shortener</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.0/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="assets/js/main.js"></script>
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
</head>

<body onload='check()'>
	<!-- Modal (popup) -->
	<div class="modal fade" id="editform" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-body">	<!--Edit form-->
					<div class="form-group">
						<label>ID</label>
						<input class="form-control" id="idedit" readonly>
					</div>
					<div class="form-group">
						<label>URL</label>
						<input class="form-control" id="urledit">
					</div>
					<p id="status">Success!</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					<button type="button" id="editbtn" class="btn btn-primary" onclick="edit()">Edit Shortlink</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="deleteform" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-body">	<!--Delete form-->
					<p id="deleteid"></p>
					<p>Are you sure want to delete?</p>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
					<button type="button" id="deletebtn" class="btn btn-danger" onclick="deletee()">Delete</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Top navigation menu -->
	<div id="nav" class="topnav">
		<a class="icon" onclick="menu()">
			Menu<i class="glyphicon glyphicon-menu-hamburger"></i>
		</a>
		<a href="/">Home</a>
		<a class="active" href="user">User Panel</a>
		<a href="register">Register</a>
		<a href="dev.html">API Docs</a>
		<div id="toptext"></div>
		<div class="login-container"></div>
	</div>
	
	<!-- Shortlink form holder -->
	<div class="middle" style="margin-top:80px;">
		<div style="text-align: center">
			<?php 
				if(!isset($user)){	//not log-in
					echo	"You need to log-in before viewing this page!<br>
							Don't have account? you can <a href='register'>register</a> for free.<br><br>
							or use this demo account<br>
							user : umum<br>password : 123456</div></div></body></html>";
					exit;
				}
			?>
			<h2>user panel <?php echo $user ?></h2>
			<br>Shortlink created here will <b>not</b> recorded in public history <!-- <a href="http://ulur.in/me">About me</a>-->
			<br><br><br>
		</div>
		<div class="form-group">
			<label>URL Link</label>
			<input class="form-control" id="linkInput" placeholder="http://example.com">
		</div>
		<div class="form-group">
			<label>Custom ID (Optional)</label>
			<input class="form-control" id="idInput" placeholder="id">
		</div>
		<button id="tombol" type="submit" onclick="submit();enableAddEntry();" class="btn btn-primary">Shorten!</button>
		<small id="error" class="form-text text-muted"></small>
		<div class='loader'>Generating....</div>
		<p id="result" style="display:none">
			Shortlink : <b onclick="copy()"></b>
			<button id="copybtn" class="btn btn-outline-secondary" onclick="copy()">Copy</button>
		</p>
	</div>
	
	<!-- Shortlink history table -->
	<div class="table-responsive">
		<table id="usertable" class="table table-sm table-dark">
			<?php	//Table data, access from API
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, 'http://add.ulur.in/api?token=TOKEN&user=' . $user);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$result = curl_exec($ch);

				$raw =  json_decode($result, true);
				if(!empty($raw['data'])){
					echo '<thead>
				<tr>
					<th scope="col">Shorturl</th>
					<th scope="col">hits</th>
					<th scope="col">date (UTC)</th>
				</tr>
			</thead>
			<tbody>';
					foreach($raw['data'] as $item) {
						echo '<tr><td><a href="'.$item['url'].'"> ulur.in/'.$item['id'].'</a></td>';
						echo '<td>'.$item['hits'].'</td>';
						echo '<td>'.$item['date'].'</td>';
						echo('<td><button class="btn btn-success" data-toggle="modal" data-target="#editform" onclick="editopen(\'' .$item['id']. '\', \'' .$item['url']. '\')">Edit</button></td>');
						echo('<td><button class="btn btn-danger" data-toggle="modal" data-target="#deleteform" onclick="deleteopen(\'' .$item['id']. '\')">Delete</button></td></tr>');
						//echo('<td><button class="btn btn-danger" onclick="deletee(\'' .$item['id']. '\')">Delete</button></td></tr>');
					}
					echo '</tbody>';
				}else{
					echo "<div style='text-align:center;color:white'>You didn't have any private shortlink yet</div>";
				}
			?>
		
		</table>
	</div>
</body>
</html>