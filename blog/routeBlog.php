<?php

// special routing rules for blog pages
error_reporting(E_ALL);

// determine paths and URLs based on the request and the location of this file
$requestedFolder = rtrim($_SERVER['REQUEST_URI'], '/') . '/';
$markdownFilePath = $_SERVER['DOCUMENT_ROOT'] . $requestedFolder . 'index.md';

//$BLOG_URL = 'https://swharden.com/blog';
$BLOG_URL = 'http://localhost:8080/blog';

// analyze the article's header
require('md2html/ArticleInfo.php');
$info = new ArticleInfo($markdownFilePath);

// build the page from a single article
require('md2html/Page.php');
$page = new Page();
$page->addArticle($markdownFilePath);
$page->enablePermalink(true, $BLOG_URL);
$page->pagination->setNextPrevious($markdownFilePath);
$page->setTitle($info->title);
$page->setDescription($info->description);
echo $page->getHtml();
