<?php

// echo readfile("test.txt"); // No more options

$myfile = fopen("test.txt", "r") or die("Unable to open file!");

echo fread($myfile,filesize("test.txt"));

fclose($myfile);

?>