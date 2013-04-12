<?php

/**
 * Check if all necessary config entries are set
 * Also set entries that have valid default entries and are not specified by the user
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://hauke-schade.de>
 * @version 3.0
 * @since 3.0
 *
 */

namespace candyCMS;

# SQL values that have to be defined by user
if (!defined('SQL_DB') || !defined('SQL_PASSWORD') || !defined('SQL_USER'))
  define('ERROR_MISSING_CONFIG_VALUES_SQL', true);

# check for values that were not set by user and set default values
if (!defined('SQL_HOST'))
  define('SQL_HOST', 'localhost');

if (!defined('SQL_TYPE'))
  define('SQL_TYPE', 'mysql');

if (!defined('SQL_PREFIX'))
  define('SQL_PREFIX', '');

if (!defined('SQL_PORT'))
  define('SQL_PORT', '3306');

if (!defined('SQL_SINGLE_DB_MODE'))
  define('SQL_SINGLE_DB_MODE', false);

if (!defined('SMTP_ENABLE'))
  define('SMTP_ENABLE', false);

if (SMTP_ENABLE) {
  if (!defined('SMTP_USE_AUTH'))
    define('SMTP_USE_AUTH', true);

  # SMTP values that have to be defined by user
  if (!defined('SMTP_HOST') || !defined('SMTP_USER') || !defined('SMTP_PASSWORD') || !defined('SMTP_PORT'))
    define('ERROR_MISSING_CONFIG_VALUES_SMTP', true);
}

# Website values that have to be defined by user
if (!defined('WEBSITE_URL') || !defined('WEBSITE_NAME') || !defined('WEBSITE_MAIL') || !defined('WEBSITE_MAIL_NOREPLY') || !defined('RANDOM_HASH'))
  define('ERROR_MISSING_CONFIG_VALUES_WEBSITE', true);

if (!defined('WEBSITE_MODE'))
  define('WEBSITE_MODE', 'staging');

if (!defined('WEBSITE_CDN'))
  define('WEBSITE_CDN', '');

if (!defined('WEBSITE_COMPRESS_FILES'))
  define('WEBSITE_COMPRESS_FILES', false);

if (!defined('ALLOW_EXTENSIONS'))
  define('ALLOW_EXTENSIONS', false);

if (!defined('ALLOW_PLUGINS'))
  define('ALLOW_PLUGINS', '');

if (!defined('ALLOW_VERSION_CHECK'))
  define('ALLOW_VERSION_CHECK', true);

if (!defined('ALLOW_SMUSHIT'))
  define('ALLOW_SMUSHIT', true);

if (!defined('DEFAULT_LANGUAGE'))
  define('DEFAULT_LANGUAGE', 'en');

if (!defined('MEDIA_DEFAULT_X'))
  define('MEDIA_DEFAULT_X', '620');

if (!defined('MEDIA_DEFAULT_Y'))
  define('MEDIA_DEFAULT_Y', '393');

if (!defined('THUMB_DEFAULT_X'))
  define('THUMB_DEFAULT_X', '180');

if (!defined('THUMB_DEFAULT_Y'))
  define('THUMB_DEFAULT_Y', '180');

if (!defined('POPUP_DEFAULT_X'))
  define('POPUP_DEFAULT_X', '800');

if (!defined('POPUP_DEFAULT_Y'))
  define('POPUP_DEFAULT_Y', '640');

# Path values
if (!defined('PATH_SMARTY'))
  define('PATH_SMARTY', 'app');

if (!defined('PATH_UPLOAD'))
  define('PATH_UPLOAD', 'upload');

if (!defined('PATH_CACHE'))
  define('PATH_CACHE', 'app');

if (!defined('AUTOLOAD'))
  define('AUTOLOAD', true);

if (!defined('AUTOLOAD_TIMES'))
  define('AUTOLOAD_TIMES', 3);

if (!defined('DISABLE_COMMENTS'))
  define('DISABLE_COMMENTS', false);

if (!defined('LIMIT_ALBUMS'))
  define('LIMIT_ALBUMS', 10);

if (!defined('LIMIT_BLOG'))
  define('LIMIT_BLOG', 8);

if (!defined('LIMIT_COMMENTS'))
  define('LIMIT_COMMENTS', 10);

if (!defined('SORTING_COMMENTS'))
  define('SORTING_COMMENTS', 'ASC');

if (!defined('SORTING_GALLERY_FILES'))
  define('SORTING_GALLERY_FILES', 'DESC');

if (!defined('CRAWLERS'))
  define('CRAWLERS', 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|AcioRobot|' .
                    'ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|GeonaBot|Gigabot|' .
                    'Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby');

if (!defined('MOBILES'))
  define('MOBILES', 'Opera Mini|Symb|Windows CE|IEMobile|iPhone|iPod|Blackberry|Android|Mobile Safari');

if (!defined('MAILCHIMP_API_KEY'))
  define('MAILCHIMP_API_KEY', '');

if (!defined('MAILCHIMP_LIST_ID'))
  define('MAILCHIMP_LIST_ID', '');

if (!defined('USE_MAIL_QUEUE'))
  define('USE_MAIL_QUEUE', true);

# do we have a valid config?
define('VALID_CONFIG', (defined('ERROR_MISSING_CONFIG_VALUES_SQL') || defined('ERROR_MISSING_CONFIG_VALUES_SMTP') || defined('ERROR_MISSING_CONFIG_VALUES_WEBSITE')));

