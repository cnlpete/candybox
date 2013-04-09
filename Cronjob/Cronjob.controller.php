<?php

/**
 * The cronjob keeps your software backuped, fast and clean. Set up the execution
 * intervals in the "app/config/Candy.inc.php" and lean back.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.5
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
    $iInterval = $bForceExecution ? PLUGIN_CRONJOB_UPDATE_INTERVAL : 60;

    return !$this->_iLastRun ? true : $this->_iLastRun + $iInterval < time();
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
   * Create a Backup of one Table
   *
   * @final
   * @access private
   * @param string $sTable the Table Name
   * @param string $sFileText the string to write the backup to
   * @return integer number of Columns
   * @throws AdvancedException
   *
   */
  private final function _backupTableInfo($sTable, &$sFileText) {
    $oQuery = $this->_oDB->query('SHOW COLUMNS FROM ' . $sTable);
    $aColumns = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    $iColumns = count($aColumns);

    $sFileText .= <<<EOD
#---------------------------------------------------------------#
# Table: {$sTable}, Columns: {$iColumns}
#---------------------------------------------------------------#

EOD;

    $sFileText .= 'DROP TABLE IF EXISTS `' . $sTable . '`;';
    $sFileText .= "\r\n";
    $sFileText .= 'CREATE TABLE `' . $sTable . '` (';

    foreach ($aColumns as $aColumn) {
      $sFileText .= "\r\n`" . $aColumn['Field'] . '` ' . $aColumn['Type'];

      if (!empty($aColumn['Default']))
        $sFileText .= " NOT NULL default '" . $aColumn['Default'] . "'";

      elseif ($aColumn['Null'] !== 'YES')
        $sFileText .= ' NOT NULL';

      elseif ($aColumn['Null'] == 'YES')
        $sFileText .= ' default NULL';

      $sFileText .= ',';
    }
    $sFileText .= "\r\n";

    # Show extras like auto_increment etc
    try {
      $oQuery = $this->_oDB->query('SHOW KEYS FROM ' . $sTable);
      $aKeys = $oQuery->fetchAll(PDO::FETCH_ASSOC);

      $iKey = 1;
      foreach ($aKeys as $aKey) {
        $sKey = & $aKey['Key_name'];

        if (($sKey != 'PRIMARY') && ($sKey['Non_unique'] == 0))
          $sKey = 'UNIQUE|' . $sKey;

        # Do we have keys?
        if ($sKey == 'PRIMARY')
          $sFileText .= ' PRIMARY KEY (`' . $aKey['Column_name'] . '`)';

        elseif (substr($sKey, 0, 6) == "UNIQUE")
          $sFileText .= ' UNIQUE ' . substr($sKey, 7) . ' (`' . $aKey['Column_name'] . '`)';

        else
          $sFileText .= ' FULLTEXT KEY ' . $sKey . ' (`' . $aKey['Column_name'] . '`)';

        if (count($aKeys) !== $iKey)
          $sFileText .= ",\n";
        ++$iKey;
      }
    }
    catch (AdvancedException $e) {
      AdvancedException::reportBoth(__METHOD__ . ':' . $e->getMessage());
      exit('SQL error.');
    }

    # Closing bracket
    $sFileText .= "\n)";

    try {
      # select last id
      $oQuery = $this->_oDB->query('SELECT
                                            id
                                          FROM
                                            ' . $sTable . '
                                          ORDER BY
                                            id DESC
                                          LIMIT
                                            1');

      $aRow = $oQuery->fetch(PDO::FETCH_ASSOC);
      $iRows = (int) $aRow['id'];
      $sFileText .= ' AUTO_INCREMENT=';
      $sFileText .= $iRows + 1;
    }
    catch (AdvancedException $e) {
      AdvancedException::reportBoth(__METHOD__ . ':' . $e->getMessage());
      exit('SQL error.');
    }

    # We also use this as count for data entries
    $sFileText .= ' DEFAULT CHARSET=utf8;';
    $sFileText .= "\r\n\r\n";

    return $iColumns;
  }

  /**
   * Get the table data and write it to $sFileText
   *
   * @final
   * @access public
   * @param string $sTable the table to get the data from
   * @param string $sFileText the result string
   * @return int number of rows backed up
   * @throws AdvancedException
   *
   */
  private final function _backupTableData($sTable, &$sFileText, $iColumns) {
    # fetch content
    $oQuery = $this->_oDB->query('SELECT * FROM ' . $sTable);
    $aRows = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    $iRows = count($aRows);

    $sFileText .= <<<EOD
#---------------------------------------------------------------#
# Data: {$sTable}, Rows: {$iRows}
#---------------------------------------------------------------#

EOD;

    foreach ($aRows as $aRow) {
      $sFileText .= 'INSERT INTO `' . $sTable . '` VALUES (';

      $iEntries = 1;
      foreach ($aRow as $sEntry) {
        $sFileText .= "'" . addslashes($sEntry) . "'";

        if ($iEntries !== $iColumns)
          $sFileText .= ',';

        $iEntries++;
      }

      $sFileText .= ");\r\n";
    }
    $sFileText .= "\r\n";
    return $iRows;
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

    $sFileText = "#---------------------------------------------------------------#\r\n";
    $sFileText .= '# Server OS: '.@php_uname()."\r\n";
    $sFileText .= "#\r\n";
    $sFileText .= '# MySQL-Version: '.@mysql_get_server_info()."\r\n";
    $sFileText .= "#\r\n";
    $sFileText .= '# PHP-Version: '.@phpversion()."\r\n";
    $sFileText .= "#\r\n";
    $sFileText .= '# Database: ' . SQL_DB."\r\n";
    $sFileText .= "#\r\n";
    $sFileText .= "# Time of backup: ".date('Y-m-d H:i')."\r\n";
    $sFileText .= "#---------------------------------------------------------------#\r\n";
    $sFileText .= "\r\n";
    $sFileText .= "#---------------------------------------------------------------#\r\n";
    $sFileText .= "# Backup includes following tables:\r\n";
    $sFileText .= "#---------------------------------------------------------------#\r\n";

    # Get all tables and name them
    try {
      $sDatabase = defined('SQL_SINGLE_DB_MODE') && SQL_SINGLE_DB_MODE === true ?
											SQL_DB :
											SQL_DB . '_' . WEBSITE_MODE;
      $oQuery = $this->_oDB->query("SHOW TABLES
                                    FROM " . $sDatabase . "
                                    WHERE `Tables_in_" . $sDatabase . "` LIKE '". SQL_PREFIX . "%'");

			$aResult = $oQuery->fetchAll();

      # Show all tables
      foreach ($aResult as $aTable) {
        $sFileText .= '# ' . $aTable[0] . "\r\n";
      }
      $sFileText .= "\r\n\r\n";

      # Now back them up
      foreach ($aResult as $aTable) {
        try {
          $iColumns = $this->_backupTableInfo($aTable[0], $sFileText);
          $this->_backupTableData($aTable[0], $sFileText, $iColumns);
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
