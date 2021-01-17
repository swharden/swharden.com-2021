<?
require_once("../blogGen/PageOfPosts.php");
$post = new PageOfPosts(__DIR__ . "/../blog/", 1, 999, "med");
$post = str_replace("<title>Med  - Page 1</title>", "<title>Scott W Harden - Medical Updates</title>", $post);
echo $post;