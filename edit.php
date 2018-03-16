<?php

	include_once('static/header.php');
	include_once('libs/list_todo.php');
	include_once('libs/edit_todo.php');

?>

<div id="mainContent" class="clearfix">
	<div id="head" class="clearfix">
		<h2> Edit Todo </h2>
	</div>
	<form method="POST" action="edit.php?id=<?php echo $_GET['id'] ?>">
		<div id="mainBody" class="clearfix">
			<?php
				if(isset($error)){
					echo '<div class="alert alert-error">'.$error.'</div>';
				} elseif(isset($success)){
					echo '<div class="alert alert-success">'.$success.'</div>';
				}
				foreach($list_todo as $td) {
					$given_array = array("Inbox","Read Later","Important");
					$selected_array = array($td['label']);
					$array_remaining = array_diff($given_array, $selected_array);
			?>
			<div class="form_field" class="clearfix">
				<label for="Title">Title</label><br />
				<input type="text" name="title" id="title" value="<?php echo $td['title']; ?>" />
			</div>
			<div class="form_field" class="clearfix">
				<label for="Description">Description <small>(Optional)</small></label ><br />
				<textarea name="description" id="description" value="<?php echo $td['description']; ?>"></textarea>
			</div>
			<div class="form_field">
				<label for="Duedate">Due Date</label><br />
				<!--<input type="text" name="due_date" id="datepicker due_date" value="echo $td['due_date']; "/>-->
				<input type="text" name="due_date" id="datepicker" value="<?php echo $td['due_date']; ?>" />
			</div>
			<div class="form_field">
				<label for="Labelunder">Label Under</label><br />
				<select name="label_under" id="label_under">
					<?php
						echo '<option value="'.$td['label'].'">'.$td['label'].'</option>';
						foreach($array_remaining as $ar){
							echo '<option value="'.$ar.'">'.$ar.'</option>';
						}
					?>
				</select>
			</div>
			<div class="progress_bar form_field">
				<div id="seekbar"></div>
				<div id="progress"><?php echo $td['progress']; ?>%</div>
				<input type="hidden" name="progress_val" value="<?php echo $td['progress']; ?>" id="progress_val" />
				<!--<div id="slider"></div>-->
				<!--<p>
					<label for="amount">Minimum number of bedrooms:</label>
					<input type="text" id="amount" readonly style="border:0; color:#f6931f; font-weight:bold;">
				</p>
				<div id="slider-range-max"></div>-->
			</div>
			<div class="form_field">
				<br /><input type="submit" name="edit_todo" value="Edit" id="edit_todo" class="btn btn-info" />
			</div>
			<?php } ?>
		</div>
	</form>