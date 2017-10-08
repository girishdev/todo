<?php

	session_start();
	if(isset($_SESSION['todo_user'])){
		session_destroy();
		//$session_name = $_SESSION['todo_user'];
		header('Location: login.php');
	}

?>