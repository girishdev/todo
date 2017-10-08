<?php
	include_once('classes/class.ManageTodo.php');
	include_once('session.php');
	$init = new ManageTodo();
	
	if(isset($_GET['delete'])){
		$id = $_GET['delete']; 
		$delete = $init->deleteTodo($session_name, $id);
		if($delete == 1) {
			header('Location: index.php');
			//$success = 'You have deleted it successfully';
		} else {
			$error = 'Sorry there was an error';
		}
	}

?>