<?php

	include_once('static/header.php');
	include_once('libs/create_todo.php');

?>

<div id="mainContent" class="clearfix">
	<div id="head" class="clearfix">
		<h2> Create Todo </h2>
	</div>
	<form method="POST" action="add_new.php">
		<div id="mainBody" class="clearfix">
			<?php
				if(isset($error)){
					echo '<div class="alert alert-error">'.$error.'</div>';
				} elseif(isset($success)){
					echo '<div class="alert alert-success">'.$success.'</div>';
				}
			?>
			<div class="form_field" class="clearfix">
				<label for="Title">Title</label><br />
				<input type="text" name="title" id="title" />
			</div>
			<div class="form_field" class="clearfix">
				<label for="Description">Description <small>(Optional)</small></label ><br />
				<textarea name="description" id="desc"></textarea>
			</div>
			<div class="form_field">
				<label for="Duedate">Due Date</label><br />
				<!--<input type="text" name="due_date" id="datepicker due_date"/>-->
				<input type="text" name="due_date" id="datepicker" />
			</div>
			<div class="form_field">
				<label for="Labelunder">Label Under</label><br />
				<select name="label_under" id="label_under">
					<option value="">Select</option>
					<option value="Inbox">Inbox</option>
					<option value="Read Later">Read Later</option>
					<option value="Important">Important</option>
				</select>
			</div>
			<div class="form_field">
				<br /><input type="submit" name="create_todo" value="Create" id="create_todo" class="btn btn-info" />
			</div>
		</div>
	</form>