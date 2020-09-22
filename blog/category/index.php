<?php

// the last folder is the page number
$req = strtok($_SERVER["REQUEST_URI"], '?');
$lastFolder = basename($req);
$secondToLastFolder = basename(dirname($req));

$category = "all";
$page = 1;
if ($lastFolder == "category") {
    // no category or number given, default vaues are OK
} else if ($secondToLastFolder == "category") {
    // category given but no page
    $category = $lastFolder;
} else {
    if (intval($lastFolder)) {
        // category and page given
        $category = $secondToLastFolder;
        $page = intval($lastFolder);
    } else {
        // category given but invalid page
        $category = $secondToLastFolder;
    }
}

/*
require_once("../../blogGen/views/PageOfPosts.php");
$page = new PageOfPosts(__DIR__."/../", intval($finalFolderName));
echo $page;


require_once("../../blogGen/PageOfPosts.php");
$post = new PageOfPosts(__DIR__ . "/../", intval($finalFolderName)); 
echo $post;

string $blog_post_folder, int $page_number, int $posts_per_page = 5, string $tag = "all"
*/

require_once("../../blogGen/PageOfPosts.php");
$post = new PageOfPosts(__DIR__ . "/../", $page, 5, $category); 
echo $post;