<?php

	session_start();
	if(isset($_SESSION['todo_user'])){
		$session_name = $_SESSION['todo_user'];
	}else {
		header('Location: login.php');
	}

?>