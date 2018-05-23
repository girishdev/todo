<?php
	include_once('libs/login_users.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Todo Maker</title>
	<link href="css/bootstrap.css" rel="stylesheet" type="text/css" />
	<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="css/style.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript" language="javascript" src="js/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" language="javascript" src="js/todo.js"></script>
	<script type="text/javascript">
	</script>
</head>

<body>
	<div id="mainWrapper">
			<div class="navbar">
				<div class="navbar-inner">
					<div class="container">
						<a class="brand" href="">Todo Maker</a>
					</div>
				</div>
			</div>
			
			<div id="content">
				<?php 
					if(isset($error)){
						echo '<div class="alert alert-error">'.$error.'</div>';
					}
				?>	
				
				<div class="login_form">
					<div id="form">
						<form method="POST" action="login.php">
							<h2>Login Here</h2>
							<div class="form_elements">
								<label for="Username">Username</label><br/>
								<input type="text" name="login_username" id="username" />
							</div>
							<div class="form_elements">
								<label for="Password">Password</label><br/>
								<input type="password" name="login_password" id="password" />
							</div>
							<div class="form_elements">
								<input type="submit" name="login" id="login" value="Login" class="btn btn-success" />
							</div>
						</form>
						<!--<span id="show_register">Don't have an account ?</span>-->
						<a href="#" id="show_register">Don't have an account ?</a>
					</div>
				</div>
				
				<div class="register_form">
					<div id="form">
						<form method="POST" action="login.php">
							<h2> Register Here</h2>
							<div class="form_elements">
								<label for="Username">Username</label><br/>
								<input type="text" name="username" id="username" />
							</div>
							<div class="form_elements">
								<label for="Email">Email</label><br/>
								<input type="text" name="email" id="email" />
							</div>
							<div class="form_elements">
								<label for="Password">Password</label><br/>
								<input type="password" name="password" id="password" />
							</div>
							<div class="form_elements">
								<label for="Password">Re-Password</label><br/>
								<input type="password" name="repassword" id="repassword" />
							</div>
							<div class="form_elements">
								<input type="submit" name="register" id="register" value="SUBMIT" class="btn btn-success" />
							</div>
						</form>
						<a href="#" id="show_login">Already have an account ?</a>
					</div>
				</div>
			</div>
			
			
	</div>
</body>

</html>