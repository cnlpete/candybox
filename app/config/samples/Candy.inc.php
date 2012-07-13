<?php

/*
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 */

# ------------------------------------------------------------------------------

# Set up your SQL preferences. If they are incorrect, the website won't work.

# SQL type
# DEFAULT: mysql
define('SQL_TYPE', 'mysql');

# SQL host
# DEFAULT: 'locahost'
define('SQL_HOST', 'localhost');

# SQL user
# DEFAULT: 'root'
define('SQL_USER', 'root');

# SQL password.
# DEFAULT: ''
define('SQL_PASSWORD', '');

# SQL database.
# DEFAULT: ''
define('SQL_DB', '');

# SQL prefixes. You can use them to avoid conflicts with non-candyCMS tables.
# DEFAULT: ''
define('SQL_PREFIX', '');

# SQL port. You don't have to change it unless the server needs a specific port.
# DEFAULT: '3306'
define('SQL_PORT', '3306');

# SQL single database mode. If your webserver doesn't support multiple databases
# (like databasename_production, databasename_development etc.) set this to true.
# OPTIONS: true / false
# DEFAULT: false
define('SQL_SINGLE_DB_MODE', false);

# ------------------------------------------------------------------------------

# Do you want to use SMTP to send your mails instead of mail()?

# OPTIONS: true / false
# DEFAULT: false
define('SMTP_ENABLE', false);
define('SMTP_HOST', '');
define('SMTP_USER', '');
define('SMTP_PASSWORD', '');
define('SMTP_PORT', '');
define('SMTP_USE_AUTH', true);

# ------------------------------------------------------------------------------

# Define the ABSOLUTE path of your website.
# EXAMPLE: http://www.google.com
define('WEBSITE_URL', 'http://domain.tld');

# ------------------------------------------------------------------------------

# Define the url to your cdn.
# EXAMPLE: http://www.google.com
# DEFAULT: '/public'
define('WEBSITE_CDN', '/public');

# ------------------------------------------------------------------------------

# Use compressed or non-compressed files. Note that compressed files must be
# updated every time you work on a non-compressed file!
# OPTIONS: true / false
# DEFAULT: false
define('WEBSITE_COMPRESS_FILES', false);
# ------------------------------------------------------------------------------

# Enter the full name of website. This is used for emails and RSS
# NOTE: Also edit your website title and slogan in your language file of choice.
define('WEBSITE_NAME', 'CandyCMS');

# ------------------------------------------------------------------------------

# Define an admin email for system responses
define('WEBSITE_MAIL', 'admin@domain.tld');

# ------------------------------------------------------------------------------

# Define a noreply email for spam etc.
# EXAMPLE: no-reply@mydomain.tld
define('WEBSITE_MAIL_NOREPLY', 'no-reply@domain.tld');

# ------------------------------------------------------------------------------

# What mode is this website running on?
# OPTIONS: production OR staging OR test OR development
# DEFAULT: staging
define('WEBSITE_MODE', 'development');

# ------------------------------------------------------------------------------

# Set true, if you want to build your own app. Also, this is always set to true
# in development or testing mode.
# DEFAULT: false
define('ALLOW_EXTENSIONS', false);

# ------------------------------------------------------------------------------

# Tell the allowed plugins seperated by comma
# DEFAULT: 'Bbcode,FormatTimestamp,Headlines,Archive,TinyMCE'
# OTHER OFFICIALLY SUPPORTED PLUGINS: Facebook, Cronjob, Piwik, Analytics,
# TagCloud, AddThis, SocialSharePrivacy
define('ALLOW_PLUGINS', 'Bbcode,FormatTimestamp,Headlines,Archive,TinyMCE');

# ------------------------------------------------------------------------------

# Allow this software to connect the CandyCMS website to check for an update.
# OPTIONS: true / false
# DEFAULT: true
define('ALLOW_VERSION_CHECK', true);

# ------------------------------------------------------------------------------

# Smush.it can reduce your image size. Do not allow if you want maximum quality
# for your images.
# OPTIONS: true / false
# DEFAULT: true
define('ALLOW_SMUSHIT', true);

# ------------------------------------------------------------------------------

# Set the standard language (file must be placed in "languages")
# NOTE: lower cases required!
# DEFAULT: 'en'
define('DEFAULT_LANGUAGE', 'en');

# ------------------------------------------------------------------------------

# Enter a random hash to higher the security of md5 hashs
# DEFAULT: None. Create one before you install this software
# NOTE: AVOID THE CHANGE OF THIS HASH AFTER USERS HAVE REGISTERED OR YOU WILL
# DESTROY THEIR LOGINS!
define('RANDOM_HASH', '');

# ------------------------------------------------------------------------------

# Set maximum image/video width (MEDIA_DEFAULT_X) and height (MEDIA_DEFAULT_Y) in px.
# Larger images and videos will be reseized or scaled down!
# DEFAULT: 660
define('MEDIA_DEFAULT_X', '620');

# DEFAULT: 371
define('MEDIA_DEFAULT_Y', '393');

# Set thumb width
# DEFAULT: 180
define('THUMB_DEFAULT_X', '180');
define('THUMB_DEFAULT_Y', '180');

# Set maximum popup width
# DEFAULT: 800 / 640
define('POPUP_DEFAULT_X', '800');
define('POPUP_DEFAULT_Y', '640');

# ------------------------------------------------------------------------------

# If you want to use templates, enter name of template-folder here
# They are placed at templates/<TemplateName>/...
# DEFAULT: ''
define('PATH_TEMPLATE', '');

# Define, where to find static HTML-Templates
# DEFAULT: 'public/skins/_static'
define('PATH_STATIC_TEMPLATES', 'public/_static');

# Define, where files are uploaded to
# DEFAULT: upload
define('PATH_UPLOAD', 'upload');

# Smarty cache folder
# DEFAULT: app/cache
define('CACHE_DIR', 'app/cache');

# Smarty compile folder
# DEFAULT: app/compile
define('COMPILE_DIR', 'app/compile');

# ------------------------------------------------------------------------------

# Limit of blog entries per page
# DEFAULT: 8
define('LIMIT_BLOG', 8);

# Limit of comments per page
# DEFAULT: 10
define('LIMIT_COMMENTS', 10);

# Limit of gallery albums per page
# DEFAULT: 10
define('LIMIT_ALBUMS', 10);

# Sorting Order of Comments
# OPTIONS: ASC,DESC
# DEFAULT: ASC
define('COMMENTS_SORTING', 'ASC');

# Automatically load next Page when scrolling down
# OPTIONS: true,false
# DEFAULT: true
define('AUTOLOAD', true);

# Stop Loading of next pages after x Times
# DEFAULT: 3
define('AUTOLOAD_TIMES', 3);

# ------------------------------------------------------------------------------

# List of Webcrawlers
# used for sending 404 instead of showing the 404 page
define('CRAWLERS', 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|AcioRobot|' .
                    'ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|GeonaBot|Gigabot|' .
                    'Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby');

# List of Mobile User Agents
# used to show the mobile Version of the Page
define('MOBILES', 'Opera Mini|Symb|Windows CE|IEMobile|iPhone|iPod|Blackberry|Android|Mobile Safari');

# ------------------------------------------------------------------------------

# grab an API Key from https://us4.admin.mailchimp.com/account/api
define('MAILCHIMP_API_KEY', '');

# grab your List's Unique Id by going to http://admin.mailchimp.com/lists/
# Click the "settings" link for the list - the Unique Id is at the bottom of that page.
define('MAILCHIMP_LIST_ID', '');

# should the user be redirected, if he tries to access old links ('/blog, /gallery, ...)
# DEFAULT: false
define('CHECK_DEPRECATED_LINKS', false);

# should mails, that failed to get send, be stored in a mail queue
# Note that those mails can be seen by Administrators until they are send
# DEFAULT: true
define('USE_MAIL_QUEUE', true);

?>
