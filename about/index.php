<?php

// build the page from multiple articles
require('../blog/md2html/Page.php');
$page = new Page();
$page->disableAds();
$page->addArticle('index.md');
$page->setTitle("About Scott W Harden, DMD, PhD");
echo $page->getHtml();
