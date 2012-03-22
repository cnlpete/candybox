<?php

/*
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 */

# ------------------------------------------------------------------------------

# Set up your SQL preferences. If they are incorrect, the website won't work.
define('SQL_HOST', 'localhost');
define('SQL_USER', 'root');
define('SQL_PASSWORD', '');
define('SQL_DB', '');
define('SQL_PREFIX', 'candy_');
define('SQL_PORT', '3306');

# ------------------------------------------------------------------------------

# Do you want to use SMTP to send your mails instead of mail()?

# OPTIONS: true / false
# DEFAULT: false
define('SMTP_ENABLE', false);
define('SMTP_HOST', '');
define('SMTP_USER', '');
define('SMTP_PASSWORD', '');
define('SMTP_PORT', '');

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
# DEFAULT: 'true'
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

# Number of seconds between cronjob execution (if enabled at ALLOWED_PLUGINS)
# DEFAULT: '86400'
define('CRONJOB_UPDATE_INTERVAL', '86400');

# ------------------------------------------------------------------------------

# Allow compressing of SQL backups
# DEFAULT: true
define('CRONJOB_GZIP_BACKUP', true);

# ------------------------------------------------------------------------------

# Do you want to receive a mail with the backup after it's created?
# DEFAULT: false
define('CRONJOB_SEND_PER_MAIL', false);

# ------------------------------------------------------------------------------

# If you want to override existing classes (placed in "addons/controllers/"), turn true
# DEFAULT: false
define('ALLOW_ADDONS', false);

# ------------------------------------------------------------------------------

# Tell the allowed plugins seperated by comma
# DEFAULT: 'Bbcode,FormatTimestamp,Headlines,Archive,Analytics'
# OTHER OFFICIALLY SUPPORTED PLUGINS: Facebook, Cronjob, Piwik
define('ALLOW_PLUGINS', 'Bbcode,FormatTimestamp');

# ------------------------------------------------------------------------------

# Allow this software to connect the CandyCMS website to check for an update
# DEFAULT: true
define('ALLOW_VERSION_CHECK', true);

# ------------------------------------------------------------------------------

# Set the standard language (file must be placed in "languages")
# NOTE: lower cases required!
# DEFAULT: 'en'
define('DEFAULT_LANGUAGE', 'en');

# ------------------------------------------------------------------------------

# Set the standard date format (http://php.net/strftime)
# DEFAULT: '%d.%m.%Y'
define('DEFAULT_DATE_FORMAT', '%d.%m.%Y');

# ------------------------------------------------------------------------------

# Set the standard time format (with seperator - if wanted)
# (http://php.net/strftime)
# DEFAULT: ', %H:%M %p'
define('DEFAULT_TIME_FORMAT', '%H:%M %p');

# ------------------------------------------------------------------------------

# Enter a random hash to higher the security of md5 hashs
# DEFAULT: None. Create one before you install this software
# NOTE: AVOID THE CHANGE OF THIS HASH AFTER USERS HAVE REGISTERED OR YOU WILL
# DESTROY THEIR LOGINS!
define('RANDOM_HASH', 'funky but secure md5 hash');

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
# They are placed at templates/<SkinName>/...
# DEFAULT: ''
define('PATH_TEMPLATE', '');

# Define, where to find static HTML-Templates
# DEFAULT: 'public/skins/_static'
define('PATH_STATIC_TEMPLATES', 'public/_static');

# Define, where files are uploaded to
# DEFAULT: upload
define('PATH_UPLOAD', '/upload');

# Some SMARTY settings
define('CACHE_DIR', 'cache');
define('COMPILE_DIR', 'compile');

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

# ------------------------------------------------------------------------------


# List of Webcrawlers
# used for sending 404 instead of showing the 404 page
define('CRAWLERS', 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|AcioRobot|' .
                    'ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|GeonaBot|Gigabot|' .
                    'Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby');

# ------------------------------------------------------------------------------

?>