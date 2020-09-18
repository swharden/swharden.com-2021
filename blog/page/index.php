<?php

// the last folder is the page number
$finalFolderName = basename(strtok($_SERVER["REQUEST_URI"], '?'));

/*
require_once("../../blogGen/views/PageOfPosts.php");
$page = new PageOfPosts(__DIR__."/../", intval($finalFolderName));
echo $page;
*/
require_once("../../blogGen/PageOfPosts.php");
$post = new PageOfPosts(__DIR__ . "/../", intval($finalFolderName)); 
echo $post;