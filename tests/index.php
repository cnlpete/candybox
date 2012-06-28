<?php

/**
 * System tests.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @version 2.0
 * @since 2.0
 *
 */

define('PATH_STANDARD', dirname(__FILE__) . '/..');

require_once PATH_STANDARD . '/vendor/autoload.php';
require_once PATH_STANDARD . '/vendor/vierbergenlars/simpletest/autorun.php';
require_once PATH_STANDARD . '/vendor/vierbergenlars/simpletest/web_tester.php';

require_once PATH_STANDARD . '/tests/candy/Candy.unit.php';
require_once PATH_STANDARD . '/tests/candy/Candy.web.php';

require_once PATH_STANDARD . '/app/config/Candy.inc.php';
require_once PATH_STANDARD . '/app/config/Plugins.inc.php';
require_once PATH_STANDARD . '/vendor/candyCMS/core/helpers/AdvancedException.helper.php';
require_once PATH_STANDARD . '/vendor/candyCMS/core/helpers/SmartySingleton.helper.php';
require_once PATH_STANDARD . '/vendor/candyCMS/core/helpers/I18n.helper.php';

define('CLEAR_CACHE', true);
define('CURRENT_URL', 'http://localhost/');
define('MOBILE', false);
define('MOBILE_DEVICE', false);
define('UNIQUE_ID', 'tests');
define('VERSION', '0');
define('TESTFILE', '/private/var/tmp/test'.md5(time()));
define('WEBSITE_LOCALE', 'en_US');
define('WEBSITE_LANGUAGE', 'en');
define('EXTENSION_CHECK', true);

setlocale(LC_ALL, WEBSITE_LOCALE);

class AllFileTests extends TestSuite {

	function __construct() {
		parent::__construct();
		$this->TestSuite('All tests');

    if (WEBSITE_MODE !== 'test')
      die('Not in testing mode.');

    elseif (DEFAULT_LANGUAGE !== 'en')
      die('Please change language to EN.');

    else {
      new \CandyCMS\Core\Helpers\I18n(WEBSITE_LANGUAGE, $_SESSION);

      # All Tests
      $aTests = array(
          # @todo AdvancedException
          # @todo Dispatcher
          'Helper.helper'   => PATH_STANDARD . '/tests/core/helpers/Helper.helper.php',
          'I18n.helper'     => PATH_STANDARD . '/tests/core/helpers/I18n.helper.php',

          'Image.helper'    => PATH_STANDARD . '/tests/core/helpers/Image.helper.php',
          # @todo pagination
          'SmartySingleton' => PATH_STANDARD . '/tests/core/helpers/SmartySingleton.helper.php',
          'Upload.helper'   => PATH_STANDARD . '/tests/core/helpers/Upload.helper.php',

          'blogs'     => array(
                          PATH_STANDARD . '/tests/core/models/Blogs.model.php',
                          PATH_STANDARD . '/tests/core/controllers/Blogs.controller.php'),

          'calendars' => array(
                          PATH_STANDARD . '/tests/core/models/Calendars.model.php',
                          PATH_STANDARD . '/tests/core/controllers/Calendars.controller.php'),

          'comments'  => array(
                          PATH_STANDARD . '/tests/core/models/Comments.model.php',
                          PATH_STANDARD . '/tests/core/controllers/Comments.controller.php'),

          'contents'  => array(
                          PATH_STANDARD . '/tests/core/models/Contents.model.php',
                          PATH_STANDARD . '/tests/core/controllers/Contents.controller.php'),

          'downloads' => array(
                          PATH_STANDARD . '/tests/core/models/Downloads.model.php',
                          PATH_STANDARD . '/tests/core/controllers/Downloads.controller.php'),

          'errors'    => PATH_STANDARD . '/tests/core/controllers/Errors.controller.php',

          'galleries' => array(
                          PATH_STANDARD . '/tests/core/models/Galleries.model.php',
                          PATH_STANDARD . '/tests/core/controllers/Galleries.controller.php'),

          'index'     =>  PATH_STANDARD . '/tests/core/controllers/Index.controller.php',


          'logs'      => array(
                          PATH_STANDARD . '/tests/core/models/Logs.model.php',
                          PATH_STANDARD . '/tests/core/controllers/Logs.controller.php'),

          'mails'     => array(
                          PATH_STANDARD . '/tests/core/controllers/Mails.controller.php',
                          PATH_STANDARD . '/tests/core/models/Mails.model.php'),

          'main'      => array(
                          PATH_STANDARD . '/tests/core/models/Main.model.php'),
          # @todo controller

          'medias'    => PATH_STANDARD . '/tests/core/controllers/Medias.controller.php',

          'newsletters' => PATH_STANDARD . '/tests/core/controllers/Newsletters.controller.php',

          'rss'       => PATH_STANDARD . '/tests/core/controllers/Rss.controller.php',

          'searches'  => array(
                          PATH_STANDARD . '/tests/core/models/Searches.model.php',
                          PATH_STANDARD . '/tests/core/controllers/Searches.controller.php'),

          'sessions'  => array(
                          PATH_STANDARD . '/tests/core/models/Sessions.model.php',
                          PATH_STANDARD . '/tests/core/controllers/Sessions.controller.php'),

          'sitemaps'  => PATH_STANDARD . '/tests/core/controllers/Sitemaps.controller.php',

          'sites'     => PATH_STANDARD . '/tests/core/controllers/Sites.controller.php',

          'users'     => array(
                          PATH_STANDARD . '/tests/core/models/Users.model.php',
                          PATH_STANDARD . '/tests/core/controllers/Users.controller.php'),
      );

      $argv = $_SERVER['argv'];
      $iNumberOfArgs = count($argv);
      # are there specific tests given?
      if ($iNumberOfArgs > 1) {
        array_shift($argv);
        foreach ($argv as $sArg)
          if ($aTests[$sArg]) {
            $this->_addFiles($aTests[$sArg]);
          }
          else
            die($sArg . ' not found');
      }

      # no specific test given, run all of them
      else {
        # add all tests
        $this->_addFiles($aTests);
      }
    }
	}

  private function _addFiles($mFile) {
    if (is_array($mFile))
      foreach ($mFile as $sTest)
        $this->_addFiles($sTest);
    else
      $this->addFile($mFile);
  }
}

?>