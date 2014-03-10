<?php

/**
 * The cronjob keeps your software backuped, fast and clean. Set up the execution
 * intervals in the "app/config/Plugins.inc.php" and lean back.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 4.1
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Controllers\Logs;
use candyCMS\Core\Controllers\Mails;
use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Models\Main;
use PDO;

final class Cronjob {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'Cronjob';

  /**
   * @var array
   * @access protected
   *
   */
  protected $_aRequest;

  /**
   * @var array
   * @access protected
   *
   */
  protected $_aSession;

  /**
   * holds the database connection
   *
   * @access private
   */
  private $_oDB;

  /**
   * holds the timestamp of the last execution
   *
   * @access private
   */
  private $_iLastRun = 0;

  /**
   * Initialize the software by adding input params.
   *
   * @access public
   * @param array $aRequest alias for the combination of $_GET and $_POST
   * @param array $aSession alias for $_SESSION
   * @param object $oPlugins the PluginManager
   *
   */
  public function __construct(&$aRequest, &$aSession, &$oPlugins) {
    $this->_aRequest  = & $aRequest;
    $this->_aSession  = & $aSession;

    // check for the file and read its timestamp
    $sBackupFile = PATH_STANDARD . '/app/backup/lastrun.timestamp';
    if (file_exists($sBackupFile)) {
      $oFile    = @fopen($sBackupFile, 'r');
      $sContent = @fgets($oFile);
      @fclose($oFile);

      $this->_iLastRun = (int) $sContent;
    }

    # now register some events with the pluginmanager
    $oPlugins->registerRepetitivePlugin($this);
  }

  /**
   * Return the status if we want to execute the cronjob.
   *
   * @access public
   * @param boolean $bForceExecution whether to force the execution
   * @return boolean update status
   *
   */
  public function needsExecution($bForceExecution = false) {
    # if the execution is forced, still do not run more than once every 60 seconds
    $iInterval = !$bForceExecution ? PLUGIN_CRONJOB_UPDATE_INTERVAL : 60;
    $iTimeStamp = $this->_iLastRun + $iInterval;

    return $this->_iLastRun == 0 ? true : $iTimeStamp < time();
  }

  /**
   * Execute the Cronjob
   *
   * @access public
   *
   */
  public function execute() {
    // write to the file, so there will be no duplicate execution
    $this->_writeTimestamp();

    $this->_oDB = Main::connectToDatabase();

    $this->cleanup(array('medias', 'bbcode'));
    $this->optimize();
    $this->backup();

    // update the timestamp, since this action might take a while
    $this->_writeTimestamp();
  }

  /**
   * write a timestamp to the backup file that indicates the last execution time,
   * when no timestamp is given, write the current time to the file
   *
   * @param int $iTimestamp the timestamp to write
   */
  private function _writeTimestamp($iTimestamp = 0) {
    // check for the file and read its timestamp
    $sBackupFile = PATH_STANDARD . '/app/backup/lastrun.timestamp';

    $oFile = @fopen($sBackupFile, 'w');
    if ($oFile) {
      @fwrite($oFile, $iTimestamp ? $iTimestamp : time());
      @fclose($oFile);
    }
  }

  /**
   * Cleanup our temp folders.
   *
   * @final
   * @access private
   * @param array $aFolders temp folders to clean
   *
   */
  private final function cleanup($aFolders) {
    foreach ($aFolders as $sFolder) {
      $sTempPath = Helper::removeSlash(PATH_UPLOAD . '/temp/' . $sFolder);
      $oDir = opendir($sTempPath);

      while ($sFile = readdir($oDir)) {
        if (substr($sFile, 0, 1) == '.' || filemtime($sTempPath . '/' . $sFile) > strtotime("-10 days"))
          continue;

        unlink($sTempPath . '/' . $sFile);
      }
    }
  }

  /**
   * Optimize tables and delete old sessions.
   *
   * @final
   * @access private
   *
   */
  private final function optimize() {
    try {
      $this->_oDB->query("OPTIMIZE TABLE
                          " . SQL_PREFIX . "blogs,
                          " . SQL_PREFIX . "comments,
                          " . SQL_PREFIX . "calendars,
                          " . SQL_PREFIX . "contents,
                          " . SQL_PREFIX . "downloads,
                          " . SQL_PREFIX . "gallery_albums,
                          " . SQL_PREFIX . "gallery_files,
                          " . SQL_PREFIX . "mails,
                          " . SQL_PREFIX . "migrations,
                          " . SQL_PREFIX . "logs,
                          " . SQL_PREFIX . "sessions,
                          " . SQL_PREFIX . "users");
    }
    catch (AdvancedException $e) {
      $this->_oDB->rollBack();
      AdvancedException::reportBoth(__METHOD__ . ':' . $e->getMessage());
      exit('SQL error.');
    }

    try {
      $oQuery = $this->_oDB->prepare("DELETE FROM
                                              " . SQL_PREFIX . "sessions
                                            WHERE
                                              date < DATE_SUB(NOW(), INTERVAL 6 MONTH)");

      return $oQuery->execute();
    }
    catch (AdvancedException $e) {
      $this->_oDB->rollBack();
      AdvancedException::reportBoth(__METHOD__ . ':' . $e->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Create a Backup of a Table and its data
   *
   * @final
   * @access private
   * @param string $sTable the Table Name
   * @param string $sFileText the string to write the backup to
   * @throws AdvancedException
   *
   */
  private final function _backupTableInfo($sTable, &$sFileText) {
    $oQuery = $this->_oDB->query('SHOW COLUMNS FROM ' . $sTable);
    $iColumns = $oQuery->columnCount();

    $oQuery = $this->_oDB->query('SELECT * FROM ' . $sTable);
    $iNumFields = $oQuery->columnCount();

    $sFileText .= <<<EOD
#---------------------------------------------------------------#
# Table: {$sTable}, Columns: {$iColumns}, Data Sets: {$iNumFields}
#---------------------------------------------------------------#

EOD;

    $sFileText .= 'DROP TABLE ' . $sTable . ';';
    $row2 = $this->_oDB->query('SHOW CREATE TABLE ' . $sTable)->fetch();
    $sFileText .= "\n\n" . $row2[1] . ";\n\n";

    for ($i = 0; $i < $iNumFields; $i++) {
      while($row = $oQuery->fetch()) {
        $sFileText .= 'INSERT INTO ' . $sTable . ' VALUES(';
        for ($j = 0; $j < $iNumFields; $j++) {
          $row[$j] = addslashes($row[$j]);
          $row[$j] = ereg_replace("\n", "\\n", $row[$j]);
          $row[$j] = ereg_replace("\r", "\\r", $row[$j]);
          if (isset($row[$j])) { 
            $sFileText .= '"' . $row[$j] . '"';
          } 
          else { 
            $sFileText .= '""';
          }
          if ($j < ($iNumFields - 1)) {
            $sFileText .= ',';
          }
        }
        $sFileText .= ");\n";
      }
    }
    $sFileText .= "\n\n\n";
  }



  /**
   * Create a SQL backup.
   *
   * @final
   * @access private
   *
   */
  private final function backup() {
    $sBackupName      = date('Y-m-d_H-i');
    $sBackupFolder    = PATH_STANDARD . '/app/backup';
    $sBackupPath      = $sBackupFolder . '/' . $sBackupName . '.sql';

    $this->_oDB->beginTransaction();

    $sFileText = "#---------------------------------------------------------------#\n";
    $sFileText .= '# Server OS: '.@php_uname()."\n";
    $sFileText .= "#\n";
    $sFileText .= '# MySQL-Version: '.@mysql_get_server_info()."\n";
    $sFileText .= "#\n";
    $sFileText .= '# PHP-Version: '.@phpversion()."\n";
    $sFileText .= "#\n";
    $sFileText .= '# Database: ' . SQL_DB."\r\n";
    $sFileText .= "#\n";
    $sFileText .= "# Time of backup: ".date('Y-m-d H:i')."\n";
    $sFileText .= "#---------------------------------------------------------------#\n";
    $sFileText .= "\n";
    $sFileText .= "#---------------------------------------------------------------#\n";
    $sFileText .= "# Backup includes following tables:\r\n";
    $sFileText .= "#---------------------------------------------------------------#\n";

    # Get all tables and name them
    try {
      $sDatabase = SQL_SINGLE_DB_MODE === true ?
                        SQL_DB :
                        SQL_DB . '_' . WEBSITE_MODE;
      $oQuery = $this->_oDB->query("SHOW TABLES
                                    FROM " . $sDatabase . "
                                    WHERE `Tables_in_" . $sDatabase . "` LIKE '". SQL_PREFIX . "%'");

      $aResult = $oQuery->fetchAll();

      # Show all tables
      foreach ($aResult as $aTable) {
        $sFileText .= '# ' . $aTable[0] . "\n";
      }
      $sFileText .= "\n\n";

      # Now back them up
      foreach ($aResult as $aTable) {
        try {
          $this->_backupTableInfo($aTable[0], $sFileText);
        }
        catch (AdvancedException $e) {
          AdvancedException::reportBoth(__METHOD__ . ':' . $e->getMessage());
          continue;
        }
      }
    }
    catch (AdvancedException $e) {
      AdvancedException::reportBoth(__METHOD__ . ':' . $e->getMessage());
      exit('SQL error.');
    }

    # Write into file
    $oFile = @fopen($sBackupPath, 'a+');
    @fwrite($oFile, $sFileText);
    @fclose($oFile);

    if (PLUGIN_CRONJOB_GZIP_BACKUP === true) {
      $oData = implode('', file($sBackupPath));
      $oCompress = gzencode($oData, 9);
      unlink($sBackupPath);

      $sBackupPath = $sBackupPath . '.gz';
      $oF = fopen($sBackupPath, 'w+');
      fwrite($oF, $oCompress);
      fclose($oF);
    }

    # Send the backup via mail
    if (PLUGIN_CRONJOB_SEND_PER_MAIL === true) {
      $sModel = \candyCMS\Core\Controllers\Main::__autoload('Mails', true);
      $oMails = new $sModel($this->_aRequest, $this->_aSession);

      $aData['subject']       = I18n::get('cronjob.mail.subject', $sBackupName);
      $aData['message']       = I18n::get('cronjob.mail.body');
      $aData['to_address']    = WEBSITE_MAIL;
      $aData['attachment']    = $sBackupPath;

      return $oMails->create($aData);
    }

    # Rollback, since we did only read statements
    $this->_oDB->rollBack();
  }
}
