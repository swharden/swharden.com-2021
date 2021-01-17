<?php

// build the page from multiple articles
require('../blog/md2html/Page.php');
$page = new Page();
$page->addArticle('index.md');
echo $page->getHtml();
