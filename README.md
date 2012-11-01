candyCMS Version 2.1.7
========================================================================================================================

What is candyCMS?
------------------------------------------------------------------------------------------------------------------------

candyCMS is a modern PHP CMS with its main focus on usability, speed and security. It is designed to create new
websites incredibly fast if you know HTML well. It is not designed to be installed by people who know web technologies
only barely.

It provides...

- a blog that supports tags, comments, RSS and full social media integration (as a plugin)
- content pages
- a gallery with multiple file upload (based on HTML5) and Media RSS
- a calendar with the option to download iCalendar events
- a download section
- easy and simple user management
- file management
- newsletter management (uses [Mailchimp API](http://mailchimp.com))
- a full log system
- Plugins to extend the functionality to your needs
- and many more!


Additional reasons, why candyCMS might be interesting for you
------------------------------------------------------------------------------------------------------------------------
- easy internationalization and localization via YAML and automatic language detection
- WYSIWYG-Editor ([TinyMCE](http://tinymce.moxiecode.com/)) and support of [BB-Code](https://github.com/marcoraddatz/candyCMS/wiki/BBCode) via Plugins. Also parses Markdown files direclty from the start.
- uses the [Smarty template engine](http://smarty.org) and lots of HTML5 to make life easier
- supports [reCAPTCHA](http://recaptcha.org) (as a Plugin)
- completely object oriented and use of MVC
- easy to extend
- supports templates
- clean URLs due to mod_rewrite
- full Facebook integration
- supports CDNs
- easy to update or migrate
- SEO optimized (sitemap.xml and basic stuff)
- 2click social share privacy and addThis to make sharing easy (as Plugins)
- Tests for the whole core functions
- many plugins and many other features


Requirements
------------------------------------------------------------------------------------------------------------------------
- at least PHP 5.3 & PDO supported database
- Imagemagick, GD2 and mod_rewrite
- an account at http://recaptcha.org to use captchas
- an account at http://mailchimp.com to use the newsletter management
- about 25MB webspace


Setup
------------------------------------------------------------------------------------------------------------------------
1. Download and install Composer ( http://getcomposer.org ): `curl -s http://getcomposer.org/installer | php`.
2. Install the vendor packages afterwards: `php composer.phar install`.
3. Configure your website settings at "app/config/Candy.inc.php" and start uploading all files.
4. Execute the "/install/index.php" file.
5. Follow the instructions and make sure, you delete the install folder after installation.

Update
------------------------------------------------------------------------------------------------------------------------
To upgrade candyCMS, read the release notes (if provided) first. If no specific information are given,
upload following folders and files after using the Composer (`php composer.phar update`):
"index.php", "composer.json" and "install". You might also need to update all javascript files under "public/js". Make
sure, the links in your views are still correct! Sorry for not providing more release information.


Credits
------------------------------------------------------------------------------------------------------------------------
Icons were created by [famfamfam.com](http://famfamfam.com). Big thanks to Hauke Schade who gave great feedback and
built many impressive features.


License
------------------------------------------------------------------------------------------------------------------------
candyCMS is licensed under MIT license. All of its components should be Open Source and free to use, too.
Note that [fancyBox](http://fancyapps.com/fancybox/) needs a license for commercial projects.