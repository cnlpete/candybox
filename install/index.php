<?php

/**
 * Installation script.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @version 2.0
 * @since 1.0
 *
 */

namespace candyCMS;

use candyCMS\Core\Controllers\Index;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\SmartySingleton;
use candyCMS\Core\Helpers\I18n;
use PDO;

if (!defined('PATH_STANDARD'))
  define('PATH_STANDARD', dirname(__FILE__) . '/..');

require PATH_STANDARD . '/vendor/candycms/core/controllers/Index.controller.php';

class Install extends Index {

  /**
   * Set up setup ;)
   *
   * @access public
   * @param array $aRequest
   * @param array $aSession
   * @param array $aFile
   * @param array $aCookie
   *
   */
  public function __construct(&$aRequest, &$aSession = '', &$aFile = '', &$aCookie = '') {
    $this->_aRequest = & $aRequest;
    $this->_aSession = & $aSession;
    $this->_aFile    = & $aFile;
    $this->_aCookie  = & $aCookie;

    if (file_exists(PATH_STANDARD . '/app/config/Candy.inc.php'))
      require PATH_STANDARD . '/app/config/Candy.inc.php';

    require PATH_STANDARD . '/vendor/candycms/core/config.inc.php';

    $this->_defines();
    $this->getLanguage();

    # do the initial creation of smarty/cache and smarty/compile folders
    $aFolders = array( Helper::removeSlash(PATH_SMARTY) => array( 'cache', 'compile' ) );
    $aFolderChecks = array();
    $this->_createFoldersIfNotExistent($aFolders, 0777, '/');
    $this->_checkFoldersAndAssign($aFolders, $aFolderChecks, 0777, '/');

    if (!$aFolderChecks['/'.Helper::removeSlash(PATH_SMARTY).'/cache'] ||
        !$aFolderChecks['/'.Helper::removeSlash(PATH_SMARTY).'/compile']) {
      # @todo print a nice error message
      exit("Please make sure the folders '" . '/'.Helper::removeSlash(PATH_SMARTY).'/compile' . "' and '" . '/'.Helper::removeSlash(PATH_SMARTY).'/compile' ."' are writable.");
    }

    $this->oSmarty = SmartySingleton::getInstance();
    $this->oSmarty->setTemplateDir(array('dir' => PATH_STANDARD . '/install/views'));
    $this->oSmarty->setCaching(SmartySingleton::CACHING_OFF);
    $this->oSmarty->setCompileCheck(true);

    # Bugfix: We need this lines for creating an admin user.
    $this->_aRequest['controller'] = 'install';
    $this->oSmarty->assign('_REQUEST', $this->_aRequest);
    $this->_sController = $this->_aRequest['controller'];

    switch ($this->_aRequest['action']) {
      case 'install':
        echo $this->showInstallation();
        break;

      case 'migrate':
        echo $this->showMigration();
        break;

      default:
      case 'standard':

        $this->oSmarty->assign('title', 'Welcome!');
        $this->oSmarty->assign('content', $this->oSmarty->fetch(array('file' => 'index.tpl')));

        break;
    }

    echo $this->oSmarty->fetch(array('file' => 'layout.tpl'));
  }

  /**
   * Set constants.
   *
   * @access private
   *
   */
  private function _defines() {
    if (!defined('WEBSITE_URL'))
      define('WEBSITE_URL', 'http://' . $_SERVER['SERVER_NAME']);

    if (!defined('CACHE_DIR'))
      define('CACHE_DIR', 'app/cache');

    if (!defined('COMPILE_DIR'))
      define('COMPILE_DIR', 'app/compile');

    if (!defined('WEBSITE_MODE'))
      define('WEBSITE_MODE', 'development');

    define('CURRENT_URL', isset($_SERVER['REQUEST_URI']) ? WEBSITE_URL . $_SERVER['REQUEST_URI'] : WEBSITE_URL);
    define('EXTENSION_CHECK', false);
    define('MOBILE', false);
    define('MOBILE_DEVICE', false);
  }

  /**
   * Create all folders specified in given array
   *
   * @access private
   * @param array $aFolders array of Folders to create, can also contain subarrays
   * @param integer $iPermissions the permissions to create the folders with, default: 0775
   *                  IMPORTANT: it needs to be an octal (put a 0 at beginning)
   * @param string $sPrefix prefix for folder creations, default: '/'
   *
   */
  private function _createFoldersIfNotExistent($aFolders, $iPermissions = 0775, $sPrefix = '/') {
    foreach ($aFolders as $sKey => $mFolder) {
      # create multiple folders
      if (is_array($mFolder))
        $this->_createFoldersIfNotExistent($mFolder, $iPermissions, $sPrefix . $sKey . '/');

      # create single Folder
      elseif (!is_dir(PATH_STANDARD . $sPrefix . $mFolder)) {
        $oldUMask = umask(0);
        @mkdir(PATH_STANDARD . $sPrefix . $mFolder, $iPermissions, true);
        umask($oldUMask);
      }
    }
  }

  /**
   * Check all Folders specified in given array and assign result to smarty
   *
   * @param array $aFolders array of Folders to check for, can also contain subarrays
   * @param array $aReturn array of bool return values for smarty
   * @param integer $iPermissions the permissions to create the folders with, default: 0775.
   *                  IMPORTANT: it needs to be an octal (put a 0 at beginning)
   * @param string $sPrefix prefix for assigns and checks, default: '/'
   * @return boolean status of folders
   *
   */
  private function _checkFoldersAndAssign($aFolders, &$aReturn, $iPermissions = 0775, $sPrefix = '/') {
    $bReturn = true;

    foreach ($aFolders as $sKey => $mFolder) {
      # Check multiple folders
      if (is_array($mFolder)) {

        # Check root folder
        $bReturnSub = $this->_checkFoldersAndAssign(array($sKey), $aReturn, $iPermissions, $sPrefix);

        # Check subfolders
        $bReturnRoot = $this->_checkFoldersAndAssign($mFolder, $aReturn, $iPermissions, $sPrefix . $sKey . '/');

        $bReturn = $bReturn && $bReturnRoot && $bReturnSub;
      }

      # check single Folder
      else {
        $aReturn[$sPrefix . $mFolder] = (bool) substr(decoct(fileperms(PATH_STANDARD . $sPrefix . $mFolder)), 2) == decoct($iPermissions);
        $bReturn = $bReturn && $aReturn[$sPrefix . $mFolder];
      }
    }

    return $bReturn;
  }

  /**
   * Show installation steps.
   *
   * @access public
   *
   */
  public function showInstallation() {
    switch ($this->_aRequest['step']) {

      default:
      case '1':

        $aHasConfigFiles = array(
            'main'    => file_exists(PATH_STANDARD . '/app/config/Candy.inc.php'),
            'plugins' => file_exists(PATH_STANDARD . '/app/config/Plugins.inc.php'));

        $bRandomHashChanged = defined('RANDOM_HASH') && RANDOM_HASH !== '';
        $this->oSmarty->assign('_hash_changed_', $bRandomHashChanged);
        $this->oSmarty->assign('_configs_exist_', $aHasConfigFiles);

        $bHasNoErrors = $bRandomHashChanged;

        foreach ($aHasConfigFiles as $bConfigFileExists)
          $bHasNoErrors = $bHasNoErrors && $bConfigFileExists;

        $this->oSmarty->assign('_has_errors_', !$bHasNoErrors);

        $this->oSmarty->assign('title', 'Installation - Step 1 - Preparation');
        $this->oSmarty->assign('content', $this->oSmarty->fetch(array('file' => 'install/step1.tpl')));

        break;

      case '2':

        # Try to create folders (if not avaiable)
        $sUpload = Helper::removeSlash(PATH_UPLOAD);
        $aFolders = array(
            'app/backup',
            'app/logs',
            Helper::removeSlash(CACHE_DIR),
            $sUpload => array(
                'downloads',
                'galleries',
                'medias',
                'temp' => array(
                    'medias', 'bbcode'),
                'users' => array(
                    '32', '64', '100', 'thumbnail', 'popup', 'original')
            )
        );

        $aFolderChecks = array();

        $this->_createFoldersIfNotExistent($aFolders);
        $this->_checkFoldersAndAssign($aFolders, $aFolderChecks);

        $this->oSmarty->assign('folders', $aFolderChecks);
        $this->oSmarty->assign('title', 'Installation - Step 2 - Folder rights');
        $this->oSmarty->assign('content', $this->oSmarty->fetch(array('file' => 'install/step2.tpl')));

        break;

      case '3':

        $sUrl = PATH_STANDARD . '/install/installation/tables.sql';
        $bHasErrors = true;

        if (file_exists($sUrl)) {
          $oFo = fopen($sUrl, 'r');
          $sData = str_replace('%SQL_PREFIX%', SQL_PREFIX, stream_get_contents($oFo));
          fclose($oFo);
          $bHasErrors = false;

          # Create tables
          try {
            $oDb = \candyCMS\Core\Models\Main::connectToDatabase();
            $oDb->query($sData);
          }
          catch (\AdvancedException $e) {
            die($e->getMessage());
          }
        }

        $this->oSmarty->assign('_has_errors_', $bHasErrors);
        $this->oSmarty->assign('title', 'Installation - Step 3 - Create database');
        $this->oSmarty->assign('content', $this->oSmarty->fetch(array('file' => 'install/step3.tpl')));

        break;

      case '4':

        if (isset($this->_aRequest[$this->_sController])) {
          $this->_setError('name')->_setError('surname')->_setError('email')->_setError('password');

          if ($this->_aRequest[$this->_sController]['password'] !== $this->_aRequest[$this->_sController]['password2'])
            $this->_aError['password'] = I18n::get('error.passwords');

          if ($this->_aError) {
            foreach ($this->_aRequest[$this->_sController] as $sInput => $sData)
              $this->oSmarty->assign($sInput, $sData);

            $this->oSmarty->assign('error', $this->_aError);
          }

          else {
            $sUsers = \candyCMS\Core\Models\Main::__autoload('Users');
            $oUsers = new $sUsers($this->_aRequest, $this->_aSession);

            $bResult = $oUsers->create(array('verification_code' => '', 'role' => 4));
            Helper::redirectTo('/install/?action=install&step=5&result=' . ($bResult ? '1' : '0'));
          }
        }

        $this->oSmarty->assign('title', 'Installation - Step 4 - Create admin');
        $this->oSmarty->assign('content', $this->oSmarty->fetch(array('file' => 'install/step4.tpl')));

        break;

      case '5':

        $this->oSmarty->assign('_result_', $this->_aRequest['result'] ? true : false);
        $this->oSmarty->assign('title', 'Installation finished');
        $this->oSmarty->assign('content', $this->oSmarty->fetch(array('file' => 'install/step5.tpl')));

        break;
    }
  }

  /**
   * @access private
   *
   */
  private function _showMigrations() {
    $sFolder = isset($this->_aRequest['show']) && 'version' == $this->_aRequest['show'] ? 'current' : 'previous';

    $sDir = PATH_STANDARD . '/install/migrations/' . $sFolder;
    $oDir = opendir($sDir);

    # Get all migrations
    $oDb = \candyCMS\Core\Models\Main::connectToDatabase();
    try {
      $oQuery   = $oDb->prepare('SELECT file, date FROM ' . SQL_PREFIX . 'migrations');
      $bReturn  = $oQuery->execute();

      if ($bReturn == true)
        $aResults = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\AdvancedException $e) {
      die($e->getMessage());
    }

    $aAlreadyMigrated = Array();
    foreach ($aResults as $aResult) {
      $aAlreadyMigrated[$aResult['file']] = $aResult['date'];
    }

    $iI = 0;
    $aFiles = array();
    while ($sFile = readdir($oDir)) {
      $bAlreadyMigrated = isset($aAlreadyMigrated[$sFile]);

      if (substr($sFile, 0, 1) == '.' || $bAlreadyMigrated == true)
        continue;

      else {
        if (pathinfo($sFile, PATHINFO_EXTENSION) == 'sql') {
          $oFo = fopen($sDir . '/' . $sFile, 'r');
          $sQuery = str_replace('%SQL_PREFIX%', SQL_PREFIX, fread($oFo, filesize($sDir . '/' . $sFile)));
          fclose($oFo);
        }
        else
          $sQuery = 'PHP Script';

        $aFiles[$iI]['name'] = $sFile;
        $aFiles[$iI]['query'] = $sQuery;
        $iI++;
      }

      unset($bAlreadyMigrated);
    }

    sort($aFiles);

    $this->oSmarty->assign('files', $aFiles);

    if(isset($this->_aRequest['show']) && 'all' == $this->_aRequest['show'])
      $this->oSmarty->assign('title', 'Migrations for all versions');

    else
      $this->oSmarty->assign('title', 'Migrations for this version');

    $this->oSmarty->assign('content', $this->oSmarty->fetch(array('file' => 'migrate/index.tpl')));
  }

  /**
   *
   * @access private
   *
   */
  private function _doSQLMigration($sFileName) {
    $oFo = fopen($sFileName, 'rb');

    try {
      $oDb = \candyCMS\Core\Models\Main::connectToDatabase();
      $bResult = $oDb->query(str_replace('%SQL_PREFIX%', SQL_PREFIX, @stream_get_contents($oFo)));
      fclose($oFo);
    }
    catch (\AdvancedException $e) {
      \Core\Helpers\AdvancedException::reportBoth(__METHOD__ . ':' . $e->getMessage());
    }

    return $bResult ? true : false;
  }

  /**
   *
   * @access private
   *
   */
  private function _doPHPMigration($sFileName) {
    require $sFileName;

    try {
      $bResult = MigrationScript::run(\candyCMS\Core\Models\Main::connectToDatabase());
    }
    catch (\AdvancedException $e) {
      \Core\Helpers\AdvancedException::reportBoth(__METHOD__ . ':' . $e->getMessage());
    }

    return $bResult ? true : false;
  }

  /**
   *
   * @access private
   *
   */
  private function _doMigration() {
    $sFile    = trim($_REQUEST['file']);
    $sPath    = isset($this->_aRequest['path']) && 'version' == $this->_aRequest['path'] ? 'current' : 'previous';
    $sPath    = PATH_STANDARD . '/install/migrations/' . $sPath . '/';

    $sExt = pathinfo($sFile, PATHINFO_EXTENSION);
    $bResult = false;

    if ($sExt == 'sql') {
      $bResult = $this->_doSQLMigration($sPath . $sFile);
    }
    elseif ($sExt == 'php') {
      $bResult = $this->_doPHPMigration($sPath . $sFile);
    }
    else
      exit('Unknown file type.');

    # Write migration into table
    if($bResult) {
      try {
        $oDb = \candyCMS\Core\Models\Main::connectToDatabase();
        $oQuery = $oDb->prepare(" INSERT INTO
                                    " . SQL_PREFIX . "migrations (file, date)
                                  VALUES
                                    ( :file, :date )");

        $oQuery->bindParam('file', $_REQUEST['file']);
        $oQuery->bindParam('date', time());
        $oQuery->execute();

        // clear all caches
        $this->oSmarty->clearCache(null, WEBSITE_MODE);
      }
      catch (\AdvancedException $e) {
        die($e->getMessage());
        #$oDb->rollBack();
      }
    }

    exit(json_encode($bResult));
  }

  /**
   *
   * @access public
   *
   */
  public function showMigration() {
    return isset($this->_aRequest['file']) ?
            $this->_doMigration($this->_aRequest['file']) :
            $this->_showMigrations();
  }

  /**
   *
   * @param string $sField
   * @param string $sMessage
   * @return object \candyCMS\Install
   * @see /vendor/candycms/core/controllers/Main.controller.php - taken from there
   *
   */
  protected function _setError($sField, $sMessage = '') {
    if (!isset($this->_aRequest[$this->_sController][$sField]) || empty($this->_aRequest[$this->_sController][$sField]))
      $sError = I18n::get('error.form.missing.' . strtolower($sField)) ?
              I18n::get('error.form.missing.' . strtolower($sField)) :
              I18n::get('error.form.missing.standard');

    if ('email' == $sField && !Helper::checkEmailAddress($this->_aRequest[$this->_sController]['email']))
      $sError = $sError ? $sError : I18n::get('error.mail.format');

    if ($sError)
      $this->_aError[$sField] = !$sMessage ? $sError : $sMessage;

    return $this;
  }
}

ini_set('display_errors', 1);
ini_set('error_reporting', 1);
ini_set('log_errors', 1);

$oInstall = new Install(array_merge($_GET, $_POST));

?>
