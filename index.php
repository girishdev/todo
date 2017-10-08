<?php 
	include_once('static/header.php');
	include_once('libs/list_todo.php');
	include_once('libs/delete.php');
	
 ?>
			<div id="mainContent" class="clearfix">
				<div id="head" class="clearfix">
					<h2>Manage Todo</h2>
					<div id="add_more">
						<a href="add_new.php" class="btn btn-success"> + Add new </a>
					</div>
				</div>
				<div id="mainBody">				
					<?php
						if(isset($error)){
							echo '<div class="alert alert-error">'.$error.'</div>';
						} elseif(isset($success)){
							echo '<div class="alert alert-success">'.$success.'</div>';
						}
					?>
					<table class="table table-striped">
						<thead>
							<tr>
								<td> Title </td>
								<td> snippet </td>
								<td> Due Date </td>
								<td> Time Left </td>
								<td> Progress </td>
								<td> Actions </td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<?php
									if($list_todo !== 0){
										foreach($list_todo as $key => $value){
											$today = strtotime("now");
											$due_date = strtotime($value['due_date']);
											if($due_date > $today){
												$hours = $due_date - $today;
												$hours = $hours / 3600;
												$time_left = $hours / 24;
												if($time_left < 2){
													$time_left = 'Less then 1 day';
												} else {
													$time_left = round($time_left).' days remaining';
												}
											} else {
												$time_left = 'Expired.';
											}																																	
										?>
										<td><?php echo $value['title']; ?></td>
										<td><?php echo $value['description']; ?></td>
										<td><?php echo $value['due_date']; ?></td>
										<td><?php echo $time_left; ?></td>
										<td>
											<!--<div class="progress progress-striped active">
												<div class="bar" style="width: <?php echo $value['progress']; ?>%"></div>
											</div>-->
											<div class="progress">
												<div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar"
												aria-valuenow="40" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo $value['progress']; ?>%">
												</div>
											</div>
										</td>
										<td>
											<a href="edit.php?id=<?php echo $value['id']; ?>" title="<?php echo $value['title'] ?>">Edit</a> | 
											<a href="index.php?delete=<?php echo $value['id']; ?>" title="<?php echo $value['title'] ?>">Delete</a>
										</td>
							</tr>
										<?php
										}
									} else {
										echo '<td></td><td></td><td>Sorry no more todo under this section</td><td></td><td></td>';
									}
								
								
								?>

						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</body>

</html>