<?php

require_once("../blogGen/PageOfPosts.php");
$post = new PageOfPosts(__DIR__, 1); 
echo $post;