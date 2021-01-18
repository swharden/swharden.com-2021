<?php

require_once(dirname(__file__) . "/md2html/misc.php");
require_once(dirname(__file__) . "/md2html/Page.php");
require_once(dirname(__file__) . "/md2html/ArticleInfo.php");

/** Tools for serving a complex multi-page website.
 *    - getPageHTML() returns a single page (optionally limited by category/tag)
 *    - getPostIndexHTML() returns a list of all posts organized by date
 *    - getCategoryIndexHTML() returns a list of all posts organized by category
 */
class Blog
{
    //private $BLOG_URL = 'https://swharden.com/blog';
    private $BLOG_URL = 'http://localhost:8080/blog';

    /** return all medical category pages with ads disabled and a custom title */
    public function getMedicalHTML(): string
    {
        $html = $this->getPageHTML(0, "med", 999, true);
        $html = str_replace('<title>Med - Page 1</title>', '<title>SWHarden.com/med - Medical updates for Scott W Harden</title>', $html);
        return $html;
    }

    /** return the first 5 pages with ads disabled and a custom title */
    public function getFrontPageHTML(): string
    {
        $html = $this->getPageHTML(0, "", 5, true);
        $html = str_replace('<title>Blog - Page 1</title>', '<title>SWHarden.com - The personal website of Scott W Harden</title>', $html);
        return $html;
    }

    /** Serve the Nth page of blog posts (starting at 0) */
    public function getPageHTML(int $pageIndex, string $tag = "", int $articlesPerPage = 5, bool $disableAds = false): string
    {
        // inventory available articles
        $articlePaths = $this->getBlogArticlePaths($tag);

        // determine which articles to show
        $pageIndex = max(0, $pageIndex);
        $pageCount = count($articlePaths) / $articlesPerPage;
        $firstIndex = $articlesPerPage * $pageIndex;
        $isValidPageIndex = ($pageIndex >= 0);
        $articlesToShow = $isValidPageIndex ? array_slice($articlePaths, $firstIndex, $articlesPerPage) : [];

        // add the articles to the page
        $page = new Page();
        if ($disableAds)
            $page->disableAds();

        $pageNumber = $pageIndex + 1;
        $titlePrimary = $tag == "" ? "Blog" : ucwords(str_replace("-", " ", $tag));
        $titleSecondary = "Page $pageNumber";
        $page->setTitle("$titlePrimary - $titleSecondary");

        if (isset($_GET['page']))
            $page->disableIndexing();
        $page->enablePermalink(true, $this->BLOG_URL);
        $page->addArticles($articlesToShow);

        // add pagination links for every page in the set
        $baseUrl = ($tag == "") ? $this->BLOG_URL : $this->BLOG_URL . "/category/$tag";
        for ($i = 0; $i < $pageCount; $i++) {
            $pageNumber = $i + 1;
            $pageIsActive = ($i == $pageIndex);
            $pageUrl = ($i == 0) ? $baseUrl : $baseUrl . "/?page=$pageNumber";
            $pageLabel = "$pageNumber";
            $pageIsEnabled = true;
            $page->pagination->addNumberedPage($pageLabel, $pageUrl, $pageIsActive, $pageIsEnabled);
        }

        return $page->getHtml();
    }

    /** Serve a page listing all blog posts */
    public function getPostIndexHTML(): string
    {
        $htmlLines = [];

        foreach ($this->getBlogArticlePaths() as $articlePath) {
            $info = new ArticleInfo($articlePath);
            $url = $this->BLOG_URL . "/" . basename(dirname($info->path));
            $htmlLines[] = $this->getArticleLi($info->title, $info->dateStringShort, $url, $info->tags);
        }

        foreach (include('BlogPostsLocked.php') as $lockedParts) {
            $htmlLines[] = $this->getArticleLi($lockedParts[1], $lockedParts[0], "", []);
        }

        rsort($htmlLines);

        $html = "<h1>All Blog Posts</h1>";
        $html .= "<ul>" . implode("", $htmlLines) . "</ul>";

        $page = new Page();
        $page->setTitle("All Blog Posts");
        $page->addHtml($html);
        return $page->getHtml();
    }

    /** Serve a page listing all blog posts grouped by category */
    function getCategoryIndexHTML(): string
    {
        $infos = [];
        $tags = [];
        foreach ($this->getBlogArticlePaths() as $articlePath) {
            $info = new ArticleInfo($articlePath);
            $infos[] = $info;
            foreach ($info->tags as $tag)
                $tags[] = $tag;
        }
        $tags = array_unique($tags);
        sort($tags);

        $html = "<h1>Blog Post Categories</h1>";
        foreach ($tags as $tag) {
            $html .= "<h2>" . ucwords($tag) . "</h2>";
            $sanTag = sanitizeLinkUrl($tag);
            $html .= "<ul>";
            foreach ($infos as $info) {
                if (in_array($sanTag, $info->tagsSanitized)) {
                    $url = $this->BLOG_URL . "/" . basename(dirname($info->path));
                    $html .= $this->getArticleLi($info->title, $info->dateStringShort, $url, $info->tags);
                }
            }
            $html .= "</ul>";
        }
        $page = new Page();
        $page->setTitle("Blog Post Categories");
        $page->addHtml($html);
        return $page->getHtml();
    }

    /** Serve the latest N posts in RSS format */
    public function getRSS(int $postCount): string
    {
        $articlePaths = array_slice($this->getBlogArticlePaths(), 0, $postCount);
        $rss = "<?xml version=\"1.0\"?>\n<rss version=\"2.0\">\n    <channel>\n";
        $rss .= "        <title>SWHarden.com</title>\n";
        $rss .= "        <link>https://swharden.com/blog</link>\n";
        $rss .= "        <description>The personal website of Scott W Harden</description>\n";
        foreach ($articlePaths as $articlePath) {
            $info = new ArticleInfo($articlePath);
            $url = "https://swharden.com/blog/" . basename(dirname($info->path));
            $date = date("r", $info->dateTime);
            $rss .= "\n";
            $rss .= "        <item>\n";
            $rss .= "            <title>$info->title</title>\n";
            $rss .= "            <description>$info->description</description>\n";
            $rss .= "            <link>$url</link>\n";
            $rss .= "            <pubDate>$date</pubDate>\n";
            foreach ($info->tags as $tag) {
                $rss .= "            <category>$tag</category>\n";
            }
            $rss .= "        </item>\n";
        }
        $rss .= "    </channel>\n</rss>";
        return $rss;
    }

    /** Serve a XML sitemap of all blog posts */
    public function getSitemap()
    {
        $baseUrl = "https://swharden.com/blog";
        $articlePaths = $this->getBlogArticlePaths();

        $xml = "";
        $xml .= "<?xml version='1.0' encoding='UTF-8'?>";
        $xml .= "<urlset xmlns='http://www.sitemaps.org/schemas/sitemap/0.9'>";

        $xml .= "   <url>";
        $xml .= "      <loc>$baseUrl</loc>";
        $xml .= "      <changefreq>always</changefreq>";
        $xml .= "      <priority>1.0</priority>";
        $xml .= "   </url>";

        foreach ($articlePaths as $articlePath) {
            $info = new ArticleInfo($articlePath);

            // prioritize based on age
            $ageDays = (time() - $info->dateTime) / (60 * 60 * 24);
            $priority = 1.0;
            $changeFreq = "always";
            if ($ageDays > 30) {
                $changeFreq = "monthly";
                $priority = 0.7;
            }
            if ($ageDays > 365) {
                $priority = 0.3;
                $changeFreq = "yearly";
            }

            $xml .= "   <url>";
            $xml .= "      <loc>$baseUrl/$info->folderName</loc>";
            $xml .= "      <changefreq>$changeFreq</changefreq>";
            $xml .= "      <priority>$priority</priority>";
            $xml .= "   </url>";
        }
        $xml .= "</urlset>";
        return $xml;
    }

    /** Return an array of paths to markdown files in reverse lexicographical order */
    private function getBlogArticlePaths(string $tag = ""): array
    {
        $parentFolder = realpath(dirname(__file__));
        foreach (glob("$parentFolder/*/index.md") as $mdPath) {
            if ($tag == "") {
                $mdPaths[] = $mdPath;
            } else {
                $info = new ArticleInfo($mdPath);
                if (in_array($tag, $info->tagsSanitized))
                    $mdPaths[] = $mdPath;
            }
        }
        rsort($mdPaths);
        return $mdPaths;
    }

    /** Create a hex color code from a hue value (0-360) */
    private function hexColorFromHSV(float $hue, float $saturation = .1, float $value = 1): string
    {
        $hue %= 360;
        $hi = (floor($hue / 60)) % 6;
        $f = $hue / 60 - floor($hue / 60);

        $value *= 255;
        $v = $value;
        $p = $value * (1 - $saturation);
        $q = $value * (1 - $f * $saturation);
        $t = $value * (1 - (1 - $f) * $saturation);

        if ($hi == 0)
            return $this->hexColor($v, $t, $p);
        else if ($hi == 1)
            return $this->hexColor($q, $v, $p);
        else if ($hi == 2)
            return $this->hexColor($p, $v, $t);
        else if ($hi == 3)
            return $this->hexColor($p, $q, $v);
        else if ($hi == 4)
            return $this->hexColor($t, $p, $v);
        else
            return $this->hexColor($v, $p, $q);
    }

    /** Return a color unique to the text used as input */
    private function colorHash(string $text): string
    {
        $hex = md5($text);
        $hashValue = hexdec(substr($hex, 0, 6));
        return $this->hexColorFromHSV($hashValue);
    }

    /** Return a color unique to the text used as input */
    private function hexColor(float $r, float $g, float $b): string
    {
        $new_hex = '#';
        $new_hex .= str_pad(dechex($r), 2, 0, STR_PAD_LEFT);
        $new_hex .= str_pad(dechex($g), 2, 0, STR_PAD_LEFT);
        $new_hex .= str_pad(dechex($b), 2, 0, STR_PAD_LEFT);
        return $new_hex;
    }

    /** Return <li>info</li> about the given article */
    private function getArticleLi(string $title, string $date, string $url, array $tags): string
    {
        $lockIcon = "<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='#AAAAAA' class='bi bi-lock-fill' " . 
                    "viewBox='0 0 16 16'><path d='M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2" . 
                    " 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z' /></svg>";

        if ($url == "") {
            return "<li class='my-1'>$date <span title='this post is locked' style='opacity: 50%'>$title $lockIcon</span></li>";
        }

        $html = "<li class='my-1'>$date <a href='$url'><strong>$title</strong></a>";
        foreach ($tags as $tag) {
            $bgColor = $this->colorHash($tag);
            $tagUrl = $this->BLOG_URL . "/category/" . sanitizeLinkUrl($tag);
            $html .= "<span class='badge rounded-pill border fw-normal ms-1' style='background-color: $bgColor'>" .
                "<a href='$tagUrl' style='color: #00000066'>$tag</a></span>";
        }
        $html .= "</li>";
        return $html;
    }
}
