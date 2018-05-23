<?php 

require_once 'classes/class.ManageUsers.php';

$GetUserInfo = new ManageUsers();

$user = $GetUserInfo->GetUserInfo('Test','Raju');
// echo '<pre>';
// print_r($user);

for($i=0;$i<=100;$i++){
    echo "$i -";
}


?>