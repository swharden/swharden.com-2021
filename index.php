<?php
/*
require_once("blogGen/SingleBlogPost.php");
$post = new SingleBlogPost(__DIR__ . "/blog/2010-11-28-crystal-oven-experiments"); 
echo $post;
*/

/*
require_once("blogGen/PageOfPosts.php");
$post = new PageOfPosts(__DIR__ . "/blog/", 2);
echo $post;
*/

require_once("blogGen/ListOfPosts.php");
$post = new ListOfPosts(__DIR__ . "/blog/", 2);
echo $post;