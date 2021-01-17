<?php

// index.php - serve the first page of blog posts

require('blog/Blog.php');
$blog = new Blog();
echo $blog->getPageHTML(0, "");