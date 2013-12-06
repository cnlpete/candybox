<?php

/**
 * CRUD actions of logs.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\SmartySingleton as Smarty;

/**
 * Class Logs
 * @package candyCMS\Core\Controllers
 *
 */
class Logs extends Main {

  /**
   * Show log overview if we have admin rights.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _overview() {
    if ($this->_aSession['user']['role'] < 4)
      return Helper::redirectTo('/errors/401');

    else {
      $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'overview');
      $this->oSmarty->setTemplateDir($oTemplate);

      if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
        $this->oSmarty->assign('logs', $this->_oModel->getOverview());
        $this->oSmarty->assign('_pagination_',
                $this->_oModel->oPagination->showPages('/' . $this->_sController));
      }

      $this->setTitle(I18n::get('global.logs'));
      return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
    }
  }

  /**
   * Create a new Log-Entry
   *
   * @static
   * @access public
   * @param string $sControllerName name of controller
   * @param string $sActionName name of action (CRUD)
   * @param integer $iActionId ID of the row that is affected
   * @param integer $iUserId ID of the acting user
   * @param integer $iTimeStart starting timestamp of the entry
   * @param integer $iTimeEnd ending timestamp of the entry
   * @param boolean $bResultFlag whether the execution was successfull
   * @return boolean status of query
   *
   */
  public static function insert($sControllerName, $sActionName, $iActionId = 0, $iUserId = 0, $iTimeStart = '', $iTimeEnd = '', $bResultFlag = true) {
    $sModel  = Main::__autoload('Logs', true);
    $bReturn = $sModel::insert( (string) $sControllerName,
                                (string) $sActionName,
                                (int) $iActionId,
                                (int) $iUserId,
                                (int) $iTimeStart,
                                (int) $iTimeEnd,
                                (bool) $bResultFlag);

    if ($bReturn)
      Smarty::getInstance()->clearControllerCache('logs');

    return $bReturn;
  }

  /**
   * Update the Endtime of some LogEntry
   *
   * @static
   * @access public
   * @param integer $iLogsId id of log entry to update
   * @param integer $iEndTime the new timestamp
   * @return boolean status of query
   *
   */
  public static function updateEndTime($iLogsId, $iEndTime = null) {
    if (!$iLogsId)
      return Helper::redirectTo('/errors/403');

    if ($iEndTime == null)
      $iEndTime = time();

    $sModel  = Main::__autoload('Logs', true);
    $bReturn = $sModel::setEndTime($iLogsId, $iEndTime);

    if ($bReturn)
      Smarty::getInstance()->clearControllerCache('logs');

    return $bReturn;
  }

  /**
   * Update the Result of some LogEntry
   *
   * @static
   * @access public
   * @param integer $iLogsId id of log entry to update
   * @param boolean $bResultFlag the new Timestamp
   * @return boolean status of query
   *
   */
  public static function updateResultFlag($iLogsId, $bResultFlag) {
    if (!$iLogsId)
      return Helper::redirectTo('/errors/403');

    require_once PATH_STANDARD . '/vendor/candycms/core/models/Logs.model.php';

    $sModel  = Main::__autoload('Logs', true);
    $bReturn = $sModel::setResultFlag($iLogsId, $bResultFlag);

    if ($bReturn)
      Smarty::getInstance()->clearControllerCache('logs');

    return $bReturn;
  }

  /**
   * Write information to text file
   *
   * @static
   * @access public
   * @param string $message
   *
   */
  public static function write($sMessage) {
    if (!$sMessage)
      return Helper::redirectTo('/errors/403');

    return \candyCMS\Core\Helpers\AdvancedException::writeLog($sMessage);
  }
}