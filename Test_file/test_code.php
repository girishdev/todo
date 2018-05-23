<!DOCTYPE html>
<html>
<head>

	<title>Test</title>

</head>
<body>
<?php 
/*
$count = '4';
$filenames = array('0' => '2','1' => '3','2' => '0');

if(in_array($count,$filenames)){
    echo $count;
}

echo '<br />====== Array1 ======<br />';

$people = array("Peter", "Joe", "Glenn", "Cleveland");

if (in_array("Glenn", $people)){
	echo "Match found";
} else {
	echo "Match not found";
}

echo '<br />====== Array2 ======<br />';

$count_array = count($count);/**/


$count = array('0','1','2','3');
$filenames = array('0' => '2','1' => '3','2' => '0');

// echo '<pre>';
// print_r($count);
// print_r($filenames);
// $res = array_merge($count, $filenames);
$res = array_unique (array_merge($filenames,$count));
// print_r($res);

?> 

<button class="change_filename">Change Filename</button>

<form method="POST" action="test_code2.php">

<div class="new_filename">
	New Filename: <input type="text" name="new_filenames">
</div>

<div class="current_filename">
<select class="form-control select2 required" name="filename[]" multiple data-tags="true" id="fileName"> 
<?php 
	/*$res_count = count($res);
	for ($i=0; $i < $res_count; $i++) { ?>
		<?php 
		if (in_array($i, $filenames)){ ?>
			<option value="<?php echo $i ?>" selected>Row <?php echo $i ?></option> 
			<?php 
		} else { ?>
			<option value="<?php echo $i ?>">Row <?php echo $i ?></option>
		<?php }
	}/**/

	foreach ($filenames as $key => $value) {
		if ( in_array($value, $count) || in_array($count, $value) ){ ?>
			<option value="<?php echo $value ?>" selected>Row <?php echo $value ?></option> 
		<?php } else { ?>
			<option value="<?php echo $value ?>">Row <?php echo $value ?></option> 
		<?php }
	}

	// foreach ($filenames as $key => $value) {
	/*foreach ($res as $key => $value) {
		$newarray[] = $value; ?>
		<option value="<?php echo $key; ?>" selected>Row <?php echo $value; ?></option> 
		<?php 
	}*/

?>
</select>
</div>

<input type="submit" name="submit">
</form>


<script type="text/javascript" src="jquery-3.3.1.js"></script>

<script type="text/javascript">
$(".change_filename").click(function(){
	if ( $('.new_filename').css('display') == 'none' ){
		// alert('show New Filename');
		$(".new_filename").show();
		$(".current_filename").hide();
	} else {
		// alert('hide New Filename');
		$(".new_filename").hide();
		$(".current_filename").show();
	}

	// if($(".new_filename").hide()){
	// 	alert('show New Filename');
	// 	$(".new_filename").show();
	// } else {
	// 	alert('hide New Filename');
	// 	$(".new_filename").hide();
	// }
    // $(".current_filename").hide();
    
});
$(".new_filename").hide();
</script>

<?php 

echo '<pre>';
print_r($filename);
exit; ?>

<select class="form-control select2 required" name="filename[]" multiple data-tags="true" id="fileName">
	<?php 
		for ($i=0; $i < $count_array; $i++) { 
			foreach ($filenames as $key => $value) {
				$newarray[] = $value; ?>
				<option value="<?php echo $i; ?>" selected>Row <?php echo $value; ?></option> 
				<?php 
			}
		}
	?>
</select>

<!-- echo '<pre>';  -->
<!-- print_r($newarray); -->


?>

</body>
</html>