<?php include_once('libs/session.php');?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <!--<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">-->
	<title>Todo Maker</title>
	<link href="css/bootstrap.css" rel="stylesheet" type="text/css" />
	<link href="css/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="css/jquery-ui.css">

	<script type="text/javascript" language="javascript" src="js/jquery-2.1.3.min.js"></script>
	<!--<script type="text/javascript" language="javascript" src="js/jquery-1.10.2.js"></script>-->
	<script type="text/javascript" language="javascript" src="js/jquery-ui.js"></script>
	<script type="text/javascript" language="javascript" src="js/todo.js"></script>
	<script>
		$( function() {
			var Currentval = $('#progress_val').val();
			$( "#seekbar" ).slider({
				range: "max",
				min: 0,
				max: 100,
				value: Currentval,
				slide: function( event, ui ) {
					$("#progress").html( ui.value + ' %');
					$("#progress_val").val( ui.value );
				}
			});
			$("#progress_val").val( $( "#seekbar" ).slider( "value" ) );
		});
	</script>
</head>

<body>
	<div id="mainWrapper">
		<div id="td_container" class="clearfix">
			<div class="brand_name">
				<a href="index.php?label=inbox" class="brand clearfix"> Todo Maker </a>
			</div>
			<div id="sidebar" class="clearfix">
				<h2>Main Menu</h2>
				<ul class="nav nav-list">
					<li><a href="index.php?label=inbox"><i class="icon-book"></i>Inbox</a></li>
					<li><a href="index.php?label=read later"><i class="icon-book"></i>Read Later</a></li>
					<li><a href="index.php?label=important"><i class="icon-book"></i>Important</a></li>
					<li><a href="logout.php"><i class="icon-book"></i>Logout</a></li>
				</ul>
			</div>