<?php

/*
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 */

# ------------------------------------------------------------------------------

# Set up your SQL preferences. If they are incorrect, the website won't work.

# SQL type.
# SUPPORTED: mysql / pgsql
# DEFAULT: mysql
define('SQL_TYPE', 'mysql');

# SQL host
# DEFAULT: 'localhost'
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

# Define the ABSOLUTE path of your website without slash at the end.
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
define('WEBSITE_NAME', 'candyCMS');

# ------------------------------------------------------------------------------

# Define an admin email for system responses
define('WEBSITE_MAIL', 'admin@domain.tld');

# ------------------------------------------------------------------------------

# Define an exception mail address. This is where exceptions go to.
define('WEBSITE_MAIL_EXCEPTION', 'exception@domain.tld');

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
# in development or testing mode. Setting to false will speed the page up.
# DEFAULT: false
define('ALLOW_EXTENSIONS', false);

# ------------------------------------------------------------------------------

# Tell the allowed plugins seperated by comma.
# If you disable TinyMCE, Markdown is used.
# DEFAULT: 'Bbcode,FormatTimestamp,Headlines,Archive,TinyMCE'
# OTHER OFFICIALLY SUPPORTED PLUGINS: Facebook, Cronjob, Piwik, Analytics,
# TagCloud, AddThis, SocialSharePrivacy, LanguageChooser, Replace, Markdown, Disqus
define('ALLOW_PLUGINS', 'Bbcode,FormatTimestamp,Headlines,Archive,TinyMCE,LanguageChooser');

# ------------------------------------------------------------------------------

# Allow this software to connect to the candyCMS website to check for an update.
# OPTIONS: true / false
# DEFAULT: true
define('ALLOW_VERSION_CHECK', true);

# ------------------------------------------------------------------------------

# Smush.it can reduce your image size. Do not allow if you want maximum quality
# for your images or uploading causes timeouts.
# OPTIONS: true / false
# DEFAULT: true
define('ALLOW_SMUSHIT', true);

# ------------------------------------------------------------------------------

# Decide whether candyCMS shall use the internal less compiler or an external
# one like codeKit etc.
# OPTIONS: true / false
# DEFAULT: true
define('ALLOW_INTERNAL_LESS', true);

# ------------------------------------------------------------------------------

# Set the standard language (file must be placed in "languages"). Note that the
# language will be determined by the browsers settings. If there is no
# translation available and the user didn't choose a language by himself, we fall
# back to the system language.
# DEFAULT: 'en'
define('DEFAULT_LANGUAGE', 'en');

# ------------------------------------------------------------------------------

# Enter a random hash to higher the security of md5 hashs. This hash is used by
# several methods and as md5 salt, too.
# DEFAULT: None. Create one before you install this software
# NOTE: AVOID THE CHANGE OF THIS HASH AFTER USERS HAVE REGISTERED OR YOU WILL
# DESTROY THEIR LOGINS!
define('RANDOM_HASH', '');

# ------------------------------------------------------------------------------

# Set maximum image/video width (MEDIA_DEFAULT_X) and height (MEDIA_DEFAULT_Y) in
# px.
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

# Set Smarty folder for compiling and caching
# DEFAULT: app
define('PATH_SMARTY', 'app');

# Define, where files are uploaded to
# DEFAULT: upload
define('PATH_UPLOAD', 'upload');

# ------------------------------------------------------------------------------

# Automatically load next page when scrolling down.
# OPTIONS: true,false
# DEFAULT: true
define('AUTOLOAD', true);

# Stop loading of next pages after x times.
# DEFAULT: 3
define('AUTOLOAD_TIMES', 3);

# Should comments be disabled?
# DEFAULT: false
define('DISABLE_COMMENTS', false);

# Limit of gallery albums per page
# DEFAULT: 10
define('LIMIT_ALBUMS', 10);

# Limit of blog entries per page
# DEFAULT: 8
define('LIMIT_BLOG', 8);

# Limit of comments per page
# DEFAULT: 10
define('LIMIT_COMMENTS', 10);

# Sorting order of comments.
# OPTIONS: ASC,DESC
# DEFAULT: ASC (oldest last)
define('SORTING_COMMENTS', 'ASC');

# Sorting order of album images.
# OPTIONS: ASC,DESC
# DEFAULT: ASC (oldest last)
define('SORTING_GALLERY_FILES', 'DESC');

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

# Grab an API Key from https://us4.admin.mailchimp.com/account/api
define('MAILCHIMP_API_KEY', '');

# Grab your list's unique ID by going to http://admin.mailchimp.com/lists/
# Click the "Settings" link for the list - the unique ID is at the bottom of that page.
define('MAILCHIMP_LIST_ID', '');

# Should mails, that failed to get send, be stored in a mail queue?
# Note that those mails can be seen and must be sent by admins.
# DEFAULT: true
define('USE_MAIL_QUEUE', true);

?>
