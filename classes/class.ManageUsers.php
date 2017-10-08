<?php

	include_once('class.database.php');
	
	class ManageUsers {
		public $link;
		
		function __construct() {
			$db_connection = new dbConnection();
			$this->link = $db_connection->connect();
			return $this->link;
		}
		
		function registerUsers($username, $email, $password, $ip_address, $date, $time) {
			echo $username;
			echo $email; 
			echo $password;
			echo $ip_address; 
			echo $date;
			echo $time;
			exit();
			$query = $this->link->prepare("INSERT INTO  users (username, email, password, ip_address, date, time) VALUES (?,?,?,?,?,?)");
			$values = array($username, $email, $password, $ip_address, $date, $time);
			$query->execute($values);
			$counts = $query->rowCount();
			return $counts;
		}
		
		function LoginUsers($username, $password){
			$query = $this->link->query("SELECT * FROM users WHERE username = '$username' AND password = '$password'");
			$rowcount = $query->rowCount();
			return $rowcount;
		}
		
		function GetUserInfo($username){
			$query = $this->link->query("SELECT * FROM users WHERE username = '$username'");
			$rowcount = $query->rowCount();
			if($rowcount == 1) {
				$result = $query->fetchAll();
				return $result;
			} else {
				return $rowcount;
			}
			
			
		}
	}
	
// echo $users->registerUsers('Girish','123456','127.0.0.1','12:00','29-02-2012');
?>