<?php
$url = 'https://add.ulur.in/';	//default redirect

if (isset($_GET['id'])) {

	require "add.ulur.in/backend/sql.php";

	//Cek apakah id ada di database
	$select = $db->prepare('SELECT url FROM redirect WHERE id = ?');
	$select->bind_param('s', $_GET['id']);
	$select->execute();
	$select->store_result();
	if($select->num_rows > 0){

		//update var url
		$select->bind_result($url);
		$select->fetch();

		//update counter
		$update = $db->prepare('UPDATE redirect SET hits = hits + 1 WHERE id = ?');
		$update->bind_param('s', $_GET['id']);
		$update->execute();
		$update->close();
	}
	$select->close();
	$db->close();
}
header("Location: " . $url);	//redirect utama
?>

<!-- redirect cadangan kalo header gagal-->
<meta http-equiv=refresh content="0;URL=<?php echo $url; ?>">
<a href="<?php echo $url; ?>">Continue</a>
<script>
	location.href=<?php echo $url; ?>;
</script>

