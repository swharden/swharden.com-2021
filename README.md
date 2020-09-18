# SWHarden.com

This repository is a collection of code and resources related to my personal website. It is intended as a way for me to maintain records of what I work on, and code here is not intended to be run by others.

### Running with Docker

Run run this website on http://localhost:8080

```bash
docker-compose up -d
```

### Markdown-to-HTML with PHP

My website now uses a variant of [md2html-php](https://github.com/swharden/md2html-php) I made just for serving my blog pages on my personal website. In making it I learned a lot, and I want to note a few of my thoughts here.

* Only serve `index.md` in a folder. The URL is the path to that folder. Having no filename (or extension) makes the URL implementation-agnostic.

* Don't support fancy features. If users want a table of contents, make them generate it in Markdown. If you really want to add a fancy feature, wrap it in its own class and call it with 1 additional line in your main parsing code.

* Avoid complex mod_rewrite statements because they are hard to integrate with existing .htaccess files. These three lines (1) identify folders which exist, (2) only operate on folders with `index.md`, (3) route the request to the blog generator PHP script.

```
RewriteCond %{REQUEST_FILENAME} -d 
RewriteCond %{REQUEST_FILENAME}/index.md -f
RewriteRule ^(.*)$ ../blogGen/route.php [L]
```

* Don't get fancy with serving .md.html file extensions. It was clever to re-route that request to a PHP script which parsed the markdown and served it as HTML, but the url is confusing to users and goes against the idea that using the folder path alone hides filename and extension (implementation details) which is desirable.

* Keep your business logic (markdown parsing) separate from your GUI code (template filling). I know this is clean architecture 101 stuff but it's easy to make exceptions to hack-in one little thing, and 100 hacks later it's unmanageable.

* It's possible to modify the php scripts to generate a static site by creating `index.html` for every `index.md` in the blog folder. Once I stop editing the website layout, I may do this. For now traffic is small enough that it can be parsed and served dynamically.

* Develop in a docker container. It's so easy, and being able to specify your version of Linux and PHP is fantastic.

Use a Dockerfile to enable mod_rewrite in Docker:
```
FROM php:7.4-apache
RUN a2enmod rewrite
```

On Windows build and run your container with:
```bat
docker build --tag md2html .
docker run -p 8080:80 -v %cd%/html:/var/www/html md2html
```