<?php

/**
 * Handle all log SQL requests.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace CandyCMS\Core\Models;

use CandyCMS\Core\Helpers\AdvancedException;
use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\Pagination;
use PDO;

require_once PATH_STANDARD . '/vendor/candyCMS/core/helpers/Pagination.helper.php';

class Logs extends Main {

  /**
   * Get log overview data.
   *
   * @access public
   * @param integer $iLimit page limit
   * @return array $this->_aData
   *
   */
  public function getOverview($iLimit = 50) {
    try {
      $oQuery = $this->_oDb->query("SELECT COUNT(*) FROM " . SQL_PREFIX . "logs");
      $iResult = $oQuery->fetchColumn();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('0105 - ' . $p->getMessage());
      exit('SQL error.');
    }

    $this->oPagination = new Pagination($this->_aRequest, $iResult, $iLimit);

    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        l.*,
                                        UNIX_TIMESTAMP(l.time_start) as time_start,
                                        UNIX_TIMESTAMP(l.time_end) as time_end,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email
                                      FROM
                                        " . SQL_PREFIX . "logs l
                                      LEFT JOIN
                                        " . SQL_PREFIX . "users u
                                      ON
                                        l.user_id=u.id
                                      ORDER BY
                                        l.time_end DESC
                                      LIMIT
                                        :offset, :limit");

      $oQuery->bindParam('limit', $this->oPagination->getLimit(), PDO::PARAM_INT);
      $oQuery->bindParam('offset', $this->oPagination->getOffset(), PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('0066 - ' . $p->getMessage());
      exit('SQL error.');
    }

    foreach ($aResult as $aRow) {
      $iId = $aRow['id'];

      $this->_aData[$iId] = $this->_formatForOutput(
              $aRow,
              array('id', 'uid', 'user_id', 'action_id'),
              array('result'));

      $this->_formatDates($this->_aData[$iId], 'time_start');
      $this->_formatDates($this->_aData[$iId], 'time_end');
    }

    return $this->_aData;
  }

  /**
   * Get log overview data.
   *
   * @static
   * @access public
   * @param string $sControllerName name of controller
   * @param string $sActionName name of action (CRUD)
   * @param integer $iActionId ID of the row that is affected
   * @param integer $iUserId ID of the acting user
   * @param integer $iTimeStart starting timestamp of the entry
   * @param integer $iTimeEnd ending timestamp of the entry
   * @param boolean $bResultFlag whether the execution was succesfull
   * @return boolean status of query
   *
   */
  public static function insert($sControllerName, $sActionName, $iActionId, $iUserId, $iTimeStart = '', $iTimeEnd = '', $bResultFlag = true) {
    if (empty(parent::$_oDbStatic))
      parent::connectToDatabase();

    $iTimeStart = empty($iTimeStart) ? time() : $iTimeStart;
    $iTimeEnd   = empty($iTimeEnd) ? time() : $iTimeEnd;

    try {
      $oQuery = parent::$_oDbStatic->prepare("INSERT INTO
                                                " . SQL_PREFIX . "logs
                                                ( controller_name,
                                                  action_name,
                                                  action_id,
                                                  time_start,
                                                  time_end,
                                                  user_id,
                                                  result)
                                              VALUES
                                                ( :controller_name,
                                                  :action_name,
                                                  :action_id,
                                                  :time_start,
                                                  :time_end,
                                                  :user_id,
                                                  :result_flag)");

      $oQuery->bindParam('controller_name', strtolower($sControllerName), PDO::PARAM_STR);
      $oQuery->bindParam('action_name', strtolower($sActionName), PDO::PARAM_STR);
      $oQuery->bindParam('action_id', $iActionId, PDO::PARAM_INT);
      $oQuery->bindParam('time_start', date('Y-m-d H:i:s', $iTimeStart), PDO::PARAM_STR);
      $oQuery->bindParam('time_end', date('Y-m-d H:i:s', $iTimeEnd), PDO::PARAM_STR);
      $oQuery->bindParam('user_id', $iUserId, PDO::PARAM_INT);
      $oQuery->bindParam('result_flag', $bResultFlag, PDO::PARAM_BOOL);

      $bReturn = $oQuery->execute();
      parent::$iLastInsertId = Helper::getLastEntry('logs');

      return $bReturn;
    }
    catch (\PDOException $p) {
      try {
        parent::$_oDbStatic->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('0067 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0068 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   *
   * Set the Endtime of some LogEntry
   *
   * @static
   * @param int $iId the id of the log-entry
   * @param int $iEndTime the timestamp to set the log-entrys endtime to
   * @return boolean status of query
   *
   */
  public static function setEndTime($iId, $iEndTime = null) {
    if (empty(parent::$_oDbStatic))
      parent::connectToDatabase();

    $iEndTime   = empty($iTimeEnd) ? time() : $iEndTime;

    try {
      $oQuery = parent::$_oDbStatic->prepare("UPDATE
                                                " . SQL_PREFIX . "logs
                                              SET
                                                time_end = :time_end
                                              WHERE
                                                id = :id
                                              LIMIT
                                                1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->bindParam('time_end', date('Y-m-d H:i:s', $iEndTime), PDO::PARAM_INT);

      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      try {
        parent::$_oDbStatic->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('0110 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0111 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   *
   * Set the ResultFlag of some LogEntry
   *
   * @static
   * @param int $iId the id of the log-entry
   * @param boolean $bResultFlag whether the log-entry should be marked as successfull or not
   * @return boolean status of query
   *
   */
  public static function setResultFlag($iId, $bResultFlag = false) {
    if (empty(parent::$_oDbStatic))
      parent::connectToDatabase();

    try {
      $oQuery = parent::$_oDbStatic->prepare("UPDATE
                                                " . SQL_PREFIX . "logs
                                              SET
                                                result = :result_flag
                                              WHERE
                                                id = :id
                                              LIMIT
                                                1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->bindParam('result_flag', $bResultFlag, PDO::PARAM_BOOL);
      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      try {
        parent::$_oDbStatic->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('0114 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0115 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }
}
