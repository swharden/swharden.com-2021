<?php

require_once(__DIR__."/../blogGen/MarkdownPage.php");
$post = new MarkdownPage(__DIR__ . "/index.md");
echo $post;