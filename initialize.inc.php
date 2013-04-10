<?php

/**
 * Website entry.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @version 3.0
 * @since 3.0
 *
 */

namespace candyCMS;

# Override separator due to W3C compatibility.
ini_set('arg_separator.output', '&amp;');

# Compress output.
ini_set('zlib.output_compression', "On");
ini_set('zlib.output_compression_level', 9);

# Define a standard path
if (!defined('PATH_STANDARD'))
  define('PATH_STANDARD', dirname(__FILE__) . '/../../..');

# Initialize software
try {
  require PATH_STANDARD . '/app/config/Candy.inc.php';
  require PATH_STANDARD . '/vendor/candycms/core/config.inc.php';
  require PATH_STANDARD . '/vendor/candycms/core/controllers/Index.controller.php';
}
catch (Exception $e) {
  die($e->getMessage());
}

if (VALID_CONFIG) {
  die('The configuration seems invalid, please check that you defined all necessary values.');
}

# Are we executing tests?
if (!defined('ACTIVE_TEST'))
  define('ACTIVE_TEST', WEBSITE_MODE == 'test' ? true : false);

# Redirect to www.website.tld if set in config. We need this for update urls etc.
if('http://' . $_SERVER['HTTP_HOST'] !== WEBSITE_URL && 'https://' . $_SERVER['HTTP_HOST'] !== WEBSITE_URL)
  exit(header('Location:' . WEBSITE_URL));

# If we are on a productive enviroment, make sure that we can't override the system.
if (WEBSITE_MODE == 'production' && is_dir('install'))
  exit('Please install software via <strong>install/</strong> and delete the folder afterwards.');

# Disable tests on productive system.
if (WEBSITE_MODE == 'production' && is_dir('tests'))
  exit('Please delete the tests enviroment (/tests).');

# Disable the use of composer.
if (WEBSITE_MODE == 'production' && is_file('composer.phar'))
  exit('Please delete the composer.phar.');

# Override the system variables in development mode.
if (WEBSITE_MODE == 'test' || WEBSITE_MODE == 'production') {
  ini_set('display_errors', 0);
  ini_set('error_reporting', 0);
}
else {
  ini_set('display_errors', 1);
  ini_set('error_reporting', 1);
}

# Define current URL and server IP
if (!defined('CURRENT_URL'))
  define('CURRENT_URL', isset($_SERVER['REQUEST_URI']) ? WEBSITE_URL . $_SERVER['REQUEST_URI'] : WEBSITE_URL);
if (!defined('SERVER_IP'))
  define('SERVER_IP', $_SERVER['REMOTE_ADDR']);

# Set up REQUEST_METHOD and support old methods
if (!defined('REQUEST_METHOD'))
  define('REQUEST_METHOD', isset($_REQUEST['method']) ? (string) strtoupper($_SERVER['REQUEST_METHOD']) : $_SERVER['REQUEST_METHOD']);

# Reload page when redirected
if (preg_match('/\?reload=1/', CURRENT_URL))
  exit(header('Location:' . str_replace('?reload=1', '', CURRENT_URL)));

# Start user session.
@session_start();

# Do we have a mobile device?
# @todo - research alternative way
if(isset($_SERVER['HTTP_USER_AGENT']))
  $bMobile = preg_match('/' . MOBILES . '/i', $_SERVER['HTTP_USER_AGENT']) ? true : false;
else
  $bMobile = false;

# Allow mobile access
if(!isset($_REQUEST['mobile']))
  $_SESSION['mobile'] = isset($_SESSION['mobile']) ? $_SESSION['mobile'] : $bMobile;

# Override current session if there is a request.
else
  $_SESSION['mobile'] = (boolean) $_REQUEST['mobile'];

if (!defined('MOBILE'))
  define('MOBILE', $_SESSION['mobile']);
if (!defined('MOBILE_DEVICE'))
  define('MOBILE_DEVICE', $bMobile);

# Check for extensions?
if (!defined('EXTENSION_CHECK'))
  define('EXTENSION_CHECK', ALLOW_EXTENSIONS === true || WEBSITE_MODE == 'development' || WEBSITE_MODE == 'test');

# @todo extension check for Index.controller
$_aRequest = array_merge($_GET, $_POST);
$oIndex = new \candyCMS\Core\Controllers\Index($_aRequest, $_SESSION, $_FILES, $_COOKIE);

# Print out HTML
echo $oIndex->show();

?>
