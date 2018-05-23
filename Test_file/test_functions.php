<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<title>Page Title</title>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link href="../css/bootstrap.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" media="screen" href="main.css" />

	<script type="text/javascript" language="javascript" src="../js/jquery-2.1.3.min.js"></script>
	<script type="text/javascript" language="javascript" src="../js/jquery-ui.js"></script>
	<script src="main.js"></script>
</head>
<body>
	<div class="container user_from">
		<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
			<div class="form-group">
				<label for="email">Name :</label>
				<input type="text" class="form-control" id="name" name="name">
			</div>
			<div class="form-group">
				<label for="email">Email address:</label>
				<input type="email" class="form-control" id="email" name="email">
			</div>
			<div class="form-group">
				<label for="pwd">Password:</label>
				<input type="password" class="form-control" id="pwd" name="pwd">
			</div>
			<button type="submit" class="btn btn-default" name="submit">Submit</button>
		</form>
	</div>
</body>
</html>


<?php

if(isset($_POST['submit'])){
	echo 'In If...';
}

exit;

class Car {
	public $model;
    function Car() {
        $this->model = "VW";
        $this->test_var = "1616";
    }
}
// create an object
$herbie = new Car();

// show object properties
echo $herbie->model;
echo '<br />';
echo $herbie->test_var;

echo '<br />';
echo '<br />';

	function myTest() {
	    static $x = 0;
	    echo $x;
	    $x++;
	}

	myTest();
	echo "<br>";
	myTest();
	echo "<br>";
	myTest();

?>