<?php

	if(isset($_POST['edit_todo'])) {
		include_once('classes/class.ManageTodo.php');
		include_once('session.php');
		$init = new ManageTodo();
		
		$username = $session_name;
		$id = $_GET['id'];
		$title = $_POST['title'];
		$description = $_POST['description'];
		$description = mysql_real_escape_string($description);
		$due_date = $_POST['due_date'];
		$label = $_POST['label_under'];
		$progress = $_POST['progress_val'];	
		
		$edit = $init->EditTodo($username, $id, $title, $description, $progress, $due_date, $label);
		
		if($edit == 1) {
			header('Location: index.php?id='.$id.'');
			//$success = 'Todo edited successfully';
		} else {
			$error = 'There was an error';
		}
	}

?>