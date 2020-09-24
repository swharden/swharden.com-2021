<?php

require_once __DIR__ . "/Page.php";
require_once __DIR__ . "/models/BlogPost.php";
require_once __DIR__ . "/models/BlogPosts.php";

class PageOfPosts extends Page
{
    function __construct(string $blog_post_folder, int $page_number, int $posts_per_page = 5, string $tag = "all")
    {
        if ($page_number == 1) {
            $this->allowAds = false;
        }

        $articles = [];
        $allPosts = new BlogPosts($blog_post_folder);
        foreach ($allPosts->newestFirst as $article) {
            if ($tag == "all" || in_array(str_replace("-", " ", $tag), $article->tags)) {
                $articles[] = $article;
            }
        }

        $totalPages = count($articles) / $posts_per_page;
        $page_index = $page_number - 1;
        $articles = array_slice($articles, $page_index * $posts_per_page, $posts_per_page);

        $html = "";
        foreach ($articles as $article) {
            $post = new BlogPost($article->markdown_file_path, false, true, true);
            $postHTML = $post->html;

            // spare traditional URLs
            $postHTML = str_replace('href="http', '{{HREF_HTTP}}', $postHTML);

            // add folder path to relative URLs
            $postHTML = str_replace('href="', 'href="' . $post->url_folder . '/', $postHTML);

            // restore traditional URLs
            $postHTML = str_replace('{{HREF_HTTP}}', 'href="http', $postHTML);
            $html .= $postHTML;
        }

        $pageLinks = [];
        for ($i = 1; $i < $totalPages + 1; $i++) {
            $pageUrl = ($tag == "all") ? "/blog/page" : "/blog/category/" . str_replace(" ", "-", $tag);
            $link = "<a href='$pageUrl/$i'>page $i</a>";
            if ($i == $page_number)
                $link = "<b>$link</b>";
            $pageLinks[] = $link;
        }
        $nav = "<div>" . join(", ", $pageLinks) . "</div>";
        $nav .= "<div><a href='/blog/posts'>All Blog Posts</a></div>";
        $this->lowerNav = $nav;

        $titlePrefix = ($tag == "all") ? "All Posts" : ucwords(str_replace("-", " ", $tag));
        $this->title = "$titlePrefix  - Page {$page_number}";

        $this->content = $html;
    }
}
