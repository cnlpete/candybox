<?php

/*
 * This software is licensed under GPL <http://www.gnu.org/licenses/gpl.html>.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 */

# ------------------------------------------------------------------------------

# SQL Properties
define('SQL_HOST', 'localhost');
define('SQL_USER', 'root');
define('SQL_PASSWORD', '');
define('SQL_DB', 'cms_new');

# ------------------------------------------------------------------------------

# Do you wish to start the DEBUG-Mode and show all queries? Note: You should use
# it only in devlopment modus!
# DEFAULT: 0
define('SQL_DEBUG', '0');

# ------------------------------------------------------------------------------

# Define ABSOLUTE path of website
# EXAMPLE: http://www.google.com
define('WEBSITE_URL', 'http://phpcms.localhost');

# ------------------------------------------------------------------------------

# Enter full name of Website. This is, where the E-Mails are sent from.
# NOTE: Also edit your website title and slogan in your language file of choice
# in config/language/YOURLANG.php
define('WEBSITE_NAME', 'dev.planetk4.de');

# ------------------------------------------------------------------------------

# Define an email, where user responses for mails and newsletters are going to
# be sent to!
define('WEBSITE_MAIL', 'info@dev.planetk4.de');

# ------------------------------------------------------------------------------

# Define, where the MySQL Backups will be sent to
define('WEBSITE_MAIL_BACKUP', 'backup@dev.planetk4.de');

# ------------------------------------------------------------------------------

# Define a noreply email for spam etc.
define('WEBSITE_MAIL_NOREPLY', 'noreply@dev.planetk4.de');

# ------------------------------------------------------------------------------

# Is the website in development mode?
# DEFAULT: 0
define('WEBSITE_DEV', '1');

# ------------------------------------------------------------------------------

# If you use plugins (placed in "app/addons/"), turn true
# DEFAULT: 0
define('ALLOW_ADDONS', '0');

# ------------------------------------------------------------------------------
# Allow caching and compiling for better performance?
# DEFAULT: 1
define('ALLOW_CACHE', '0');

# ------------------------------------------------------------------------------

# Allow compressing of SQL Backups
# DEFAULT: 1
define('ALLOW_GZIP_BACKUP', '1');

# ------------------------------------------------------------------------------

# Set the standard language (file must be placed in "config/language")
# NOTE: lower cases required!
# DEFAULT: ger
define('DEFAULT_LANGUAGE', 'ger');

# ------------------------------------------------------------------------------

# Enter a random hash to higher the security of md5 hashs
# NOTE: AVOID THE CHANGE OF THIS HASH AFTER USERS HAVE REGISTERED OR YOU WILL DESTROY
# THEIR LOGINS!
define('RANDOM_HASH', 'h7da87@#asd0io08');

# ------------------------------------------------------------------------------

# To avoid spam, we use ReCaptcha (http://recaptcha.org). Get there, register
# yourself and get an account
# Enter given private key here:
define('RECAPTCHA_PRIVATE', '242');

# Enter given public key here:
define('RECAPTCHA_PUBLIC', '24234');

# ------------------------------------------------------------------------------

# Set maximum image/video width (MEDIA_DEFAULT_X) and height (MEDIA_DEFAULT_Y) in px.
# Larger images and videos will be reseized or scaled down!
# DEFAULT: 720
define('MEDIA_DEFAULT_X', '720');

# DEFAULT: 405
define('MEDIA_DEFAULT_Y', '405');

# Set thumb width
# DEFAULT: 200
define('THUMB_DEFAULT_X', '200');

# Set popup width
# DEFAULT: 800
define('POPUP_DEFAULT_X', '800');
define('POPUP_DEFAULT_Y', '800');

# ------------------------------------------------------------------------------

# If you want to use skins, enter name of skin-folder here
# They are placed at skins/<SkinName>/...
# DEFAULT: ''
define('SKIN_CSS', '');

# DEFAULT: default
define('SKIN_IMAGES', '');

# DEFAULT: default
define('SKIN_TPL', '');

# ------------------------------------------------------------------------------

# Enter _absolute_ path of your public folder or CDN
# DEFAULT: http://domain.tld/public
define('PATH_PUBLIC', 'http://phpcms.localhost/public');

# Also do so on images folder
# DEFAULT: http://domain.tld//images
define('PATH_IMAGES', 'http://phpcms.localhost/images');

# Define, where to search for additional templates
# DEFAULT: '', FOLDER: 'public/skins/SKINNAME/view/addon'
define('PATH_TPL_ADDON', '');

# Define, where to find static HTML-Templates
# DEFAULT: 'public/skins/default/view/_static'
define('PATH_TPL_STATIC', PATH_PUBLIC.  '/skins/default/view/_static');

# Define, where files are uploaded to
# DEFAULT: upload
define('PATH_UPLOAD', 'upload');

# ------------------------------------------------------------------------------

# Define limit for pictures per page (3 in a row)
# DEFAULT: 9
define('LIMIT_ALBUM_IMAGES', 9);
define('LIMIT_ALBUM_THUMBS', 16);

# Limit of blog entries per page
# DEFAULT: 5
define('LIMIT_BLOG', 5);

# Limit of comments per page
# DEFAULT: 25
define('LIMIT_COMMENTS', 5);

# ------------------------------------------------------------------------------

?>