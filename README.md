candyCMS Version 3 RC 1
========================================================================================================================

What is candyCMS?
------------------------------------------------------------------------------------------------------------------------

candyCMS is a modern PHP CMS with its main focus on usability, speed and security. It is designed to create new
websites incredibly fast if you know HTML well. It is not designed to be installed by people who know web technologies
only barely.

It provides...

- a blog that supports tags, comments, RSS and full social media integration (via plugin)
- basic content pages
- a gallery with multiple file upload (based on HTML5) and Media RSS
- a calendar with the option to download iCalendar events
- a download section
- file management
- a newsletter
- easy and simple user management
- full logs of all actions
- Plugins to extend the functionality to your needs

... and uses...
- [Twitter Bootstrap](http://twitter.github.com/bootstrap/) for its basic design
- [Smarty](http://smarty.org) as template engine
- [Mailchimp](http://mailchimp.com) for newsletter managing
- [reCAPTCHA](http://recaptcha.org) to protect spam (as plugin)
- [TinyMCE](http://tinymce.moxiecode.com/) as WYSIWYG editor (as plugin)
- [BB-Code](https://github.com/marcoraddatz/candyCMS/wiki/BBCode) and [Markdown](http://daringfireball.net/projects/markdown/) to make editing easy
- [SmushIt](http://www.smushit.com/ysmush.it/) to compress images on the fly
- [Facebook](http://facebook.com/) and [Gravatar](http://gravatar.com/) to display user images
- [Composer](http://getcomposer.org/) to keep the software up to date
- [2click social share privacy](http://www.heise.de/extras/socialshareprivacy/) and [AddThis](http://www.addthis.com/) for social communication (as plugins)
- [Google Analytics](http://www.google.com/analytics/) or / and [Piwik](http://de.piwik.org/) to track your website


Additional reasons, why candyCMS might be interesting for you
------------------------------------------------------------------------------------------------------------------------
- easy internationalization and localization via YAML and automatic language detection
- best use of HTML5 to make life easier
- completely object oriented and use of MVC
- easy to extend
- supports different templates
- clean URLs due to mod_rewrite
- supports CDNs
- easy to update or migrate
- SEO optimized (sitemap.xml and basic stuff)
- many plugins and many other features


Requirements
------------------------------------------------------------------------------------------------------------------------
- at least PHP 5.3 & MySQL database
- Imagemagick, GD2 and mod_rewrite
- an account at http://recaptcha.org to use captchas
- an account at http://mailchimp.com to use the newsletter management
- about 25MB webspace


Setup
------------------------------------------------------------------------------------------------------------------------
1. Download and install Composer ( http://getcomposer.org ): `curl -s http://getcomposer.org/installer | php`.
2. Install the vendor packages afterwards: `php composer.phar install` or `php composer.phar install --dev` if you would
like have the possibility to run tests.
3. Configure your website settings at "app/config/Candy.inc.php" and start uploading all files.
4. Execute the "/install/index.php" file. If an error occurs it might be, that you have to give CHMOD 777 to `app/cache`
and `app/compile`.
5. Follow the instructions and make sure, you delete the install folder after installation.
6. If you want to use compressed CSS and JS, make sure the template folders are writeable and start the compressor
via console: `php tools/minify/index.php`. This will compress the actual JS and CSS rendered by your DEVELOPMENT enviroment.


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