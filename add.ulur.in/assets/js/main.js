var result = null;	//shortlink result to copy

//API URL
function getapi(){
	if (window.location.protocol != "https:"){
		return "http://add.ulur.in/api";
	} else{
		return "https://add.ulur.in/api";
	}
}

function getuserapi(){
	if (window.location.protocol != "https:"){
		return "http://add.ulur.in/usrapi";
	} else{
		return "https://add.ulur.in/usrapi";
	}
}

//--------------------------------- Shortlink input form action -------------------------------------
function submit(){
	$('#result b').html('');
	$('#error').html('');
	$('#result').css('display', 'none');
	if($('#linkInput').val()!=''){
		$(".loader").fadeIn("normal");
		document.getElementById("tombol").disabled = true;		//Prevent sapm or misclick

		var postdata;
		if($('#idInput').val()==''){
			postdata = {
				url:$('#linkInput').val()
			}
		}else{
			postdata = {
				url:$('#linkInput').val(),
				id:$('#idInput').val(),
				random:'false'
			}
		}
		$.ajax({
			url: getapi(),
			type: "POST",
			data: postdata,
			success:function(data){
				$('#result b').html(data.shortlink);
				$('#result').css('display', '');
				result = data.shortlink;
				
				if(addentry){		//Table add new entry
					var row = document.getElementById("usertable").insertRow(1);
					row.insertCell(0).innerHTML = "<a href='https://" + data.shortlink + "' >" + data.shortlink + "</a>";
					row.insertCell(1).innerHTML = "0";
					row.insertCell(2);
					row.insertCell(3).innerHTML = "<button class='btn btn-success' data-toggle='modal' data-target='#editform' onclick='editopen(&quot;" +  data.shortlink.split('/')[1] + "&quot;,&quot;" + $('#linkInput').val() + "&quot;)'>Edit</button>  <button class='btn btn-danger' data-toggle='modal' data-target='#deleteform' onclick='deleteopen(&quot;" +  data.shortlink.split('/')[1] + "&quot;)'>Delete</button>";
					addentry = false;
				}
			},
			error:function(xhr){
				var err = eval("(" + xhr.responseText + ")");
				$('#error').html(err.error);
			},
			complete:function(){
				$(".loader").css('display', 'none');
				setTimeout(function(){
					document.getElementById("tombol").disabled = false;
				},5000);
			}
		});
	}else{
		$('#error').html("Harap isi url input!");
	}
}

//Customize button
function customize() {
	var checkBox = document.getElementById("myCheck");
	var text = document.getElementById("customizepart");

	if (checkBox.checked == true){
		text.style.display = "inline";
	} else {
		text.style.display = "none";
	}
}

//Copy hasil shortlink
function copy() {
	var fullLink = document.createElement('input');
	document.body.appendChild(fullLink);
	fullLink.value = result;
	fullLink.select();
	fullLink.setSelectionRange(0, 99999); /*For mobile devices*/
	document.execCommand("copy");
	fullLink.remove();
	$('#copybtn').attr('class','btn btn-success');
	$('#copybtn').css('cursor', 'default');
	$('#copybtn').html('Copied!');
	setTimeout(function(){
		$('#copybtn').attr('class','btn btn-outline-secondary');
		$('#copybtn').css('cursor', 'pointer');
		$('#copybtn').html('Copy');
	},3000);
}

//-------------------------------------- USER PANEL -----------------------------------------
var addentry = false;

function enableAddEntry(){
	addentry = true;
}

//untuk user panel pas click edit di tabel
function editopen(id, url) {
	$('#idedit').val(id);
	$('#urledit').val(url);
}

//untuk user panel pas click delete di tabel
function deleteopen(id) {
	$('#deleteid').html(id);
}

//untuk user panel edit item
function edit() {
	document.getElementById("editbtn").disabled = true;		//Prevent sapm or misclick
	$.ajax({
		url: getapi(),
		type: "POST",
		data: {
			edit:$('#idedit').val(),
			url:$('#urledit').val()
		},
		success:function(){
			$('#status').css('display','inline');
			$('#status').attr('class','alert alert-success');
			$('#status').html('Success!');
			setTimeout(function(){
				location.reload();
			},2000);
		},
		error:function(xhr){
			var err = eval("(" + xhr.responseText + ")");
			$('#status').css('display','inline');
			$('#status').attr('class','alert alert-danger');
			$('#status').html(err.error);
			document.getElementById("editbtn").disabled = false;
		}
	});
}

//untuk user panel delete item
function deletee() {
	document.getElementById("deletebtn").disabled = true;		//Prevent sapm or misclick
	$.ajax({
		url: getapi(),
		type: "POST",
		data: {
			delete:$('#deleteid').html(),
		},
		success:function(){
			location.reload();
		},
		error:function(xhr){
			var err = eval("(" + xhr.responseText + ")");
			alert(err.error);
			document.getElementById("deletebtn").disabled = false;
		}
	});
}

//-------------------------------------- LOGIN & LOGOUT MENU -----------------------------------

//untuk login form / logout display & cek https
function check(){
	if(window.location.protocol != 'https:') {
		location.href = location.href.replace("http://", "https://");
	}
	$.ajax({
		url: getuserapi(),
		type: "GET",
		success:function(data){		//logged-in
			var content = '<button onclick="location.href=&#39;usrapi?logout=' + window.location.href + '&#39;" type="submit" >Logout</button>';
			$('.login-container').html(content);
			$('#toptext').html(data.username);
			if(window.location.href.indexOf('ulur.in/user') !== -1){		//For USER PANEL Username Display
				$('#wellcometext').html("user panel " + data.username);
			}
			$('#toptext').css('display','block');
		},
		error:function(){	//not logged-in
			var content = '<form action="login" method="POST"><input id="usern" type="text" placeholder="Username" name="username" required> <input type="hidden" name="origin" value="' + window.location.href +'"><input id="passw" type="password" placeholder="Password" name="password" required> <button type="submit">Login</button></form>';
			$('.login-container').html(content);
			var error = findGetParameter('err');
			if(window.location.href.indexOf('ulur.in/user') !== -1){		//For USER PANEL error view
				$('.middle').html("<div style='text-align: center'>You need to log-in before viewing this page!<br>Don't have account? you can <a href='register'>register</a> for free.<br><br>or use this demo account<br>user : umum<br>password : 123456</div>");
				$('.table-responsive').remove();
			}
			if(error){
				$('#passw').css('border','1px solid red');
				$('#usern').css('border','1px solid red');
				$('#usern').val(error);
				var err = document.createElement("small");
				err.classList.add("login-error");
				err.id = "errorlogin";
				err.innerHTML = "Username & password didn't match";
				document.getElementById("nav").appendChild(err);
			}
			$('#alret').css('display','block');
		}
	});
}

function findGetParameter(parameterName) {
	var result = false,
		tmp = [];
	var items = location.search.substr(1).split("&");
	for (var index = 0; index < items.length; index++) {
		tmp = items[index].split("=");
		if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
	}
	return result;
}

//-------------------------------------- MOBILE MENU BUTTON ------------------------------------------

function menu() {
	var x = document.getElementById("nav");
	if (x.className === "topnav") {
	  x.className += " responsive";
	} else {
	  x.className = "topnav";
	}
}
  