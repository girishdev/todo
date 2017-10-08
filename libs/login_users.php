<?php

	if(isset($_POST['register'])) {
		session_start();
		include_once('classes/class.ManageUsers.php');
		$users = new ManageUsers();
		
		$username = $_POST['username'];
		$password = $_POST['password'];
		$email = $_POST['email'];
		$repassword = $_POST['repassword'];
		$ip_address = $_SERVER['REMOTE_ADDR'];
		$date = date("Y-m-d");
		$time = date("H:i:s");
		
		if(empty($username) || empty($email) || empty($password) || empty($repassword)){
			$error = 'All fields are required';
		} elseif ($password !== $repassword){
			$error = 'Password does not match';
		} elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$error = 'Email is not valid';
		} else {
			$check_availablity = $users->GetUserInfo($username);
			if($check_availablity == 0){
				$password = md5($password);
				$register_user = $users->registerUsers($username, $email, $password, $ip_address, $date, $time);
				if($register_user == 1){
					echo $make_sessions = $users->GetUserInfo($username);
					// echo 'Test';
					// exit();
					foreach($make_sessions as $usersession) {
						$_SESSION['todo_name'] = $usersession['username'];
						if(isset($_SESSION['todo_name'])) {
							// echo 'Test';
							// exit();
							header("Location: index.php");
						}
					}
					//print_r($make_sessions);
				} else {
					echo 'In else part123...';
				}
			} else {
				$error = 'Username already exist.';
			}
		}
	}
	
	if(isset($_POST['login'])){
		session_start();
		include_once('classes/class.ManageUsers.php');
		$username = $_POST['login_username'];
		$password = $_POST['login_password'];
		if(empty($username) || empty($password)) {
			$error = 'All fields are required.';
		} else {
			// $password = md5($password);
			$login_users = new ManageUsers();
			$auth_user = $login_users->LoginUsers($username, $password);
			if($auth_user == 1){
				$make_sessions = $login_users->GetUserInfo($username);
				foreach($make_sessions as $userSessions) {
					$_SESSION['todo_user']  = $userSessions['username'];
					if(isset($_SESSION['todo_user'])){
						header('Location: index.php');
					}					
				}
			} else {
				$error = 'Invalid credentials';
			} 
		}
	}

?>