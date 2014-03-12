<?php

/**
 * Handle all calendar SQL requests.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Core\Models;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use PDO;

/**
 * Class Calendars
 * @package candyCMS\Core\Models
 *
 */
class Calendars extends Main {

  /**
   * Build the PDO-Statement for getting entries for the specified year
   *
   * @access protected
   * @return object PDOStatement the PDOStatement to execute
   *
   */
  protected function _getPreparedArchiveStatement() {
    $iYear = isset($this->_aRequest['id']) && !empty($this->_aRequest['id']) ?
            (int) $this->_aRequest['id'] :
            date('Y');

    $oQuery = $this->_oDb->prepare("SELECT
                                      c.*,
                                      UNIX_TIMESTAMP(c.date) as date,
                                      MONTH(c.start_date) AS start_month,
                                      YEAR(c.start_date) AS start_year,
                                      UNIX_TIMESTAMP(c.start_date) AS start_date,
                                      UNIX_TIMESTAMP(c.end_date) AS end_date,
                                      u.id AS user_id,
                                      u.name AS user_name,
                                      u.surname AS user_surname,
                                      u.email AS user_email
                                    FROM
                                      " . SQL_PREFIX . "calendars c
                                    LEFT JOIN
                                      " . SQL_PREFIX . "users u
                                    ON
                                      c.author_id=u.id
                                    WHERE
                                      YEAR(c.start_date) = :year
                                    ORDER BY
                                      c.start_date ASC,
                                      c.title ASC");

    $oQuery->bindParam('year', $iYear, PDO::PARAM_INT);
    return $oQuery;
  }

  /**
   * Build the PDO-Statement for getting all future entries
   *
   * @access protected
   * @return object PDOStatement the PDOStatement to execute
   *
   */
  protected function _getPreparedOverviewStatement() {
    return $this->_oDb->prepare("SELECT
                                    c.*,
                                    UNIX_TIMESTAMP(c.date) as date,
                                    MONTH(c.start_date) AS start_month,
                                    YEAR(c.start_date) AS start_year,
                                    UNIX_TIMESTAMP(c.start_date) AS start_date,
                                    UNIX_TIMESTAMP(c.end_date) AS end_date,
                                    u.id AS user_id,
                                    u.name AS user_name,
                                    u.surname AS user_surname,
                                    u.email AS user_email
                                  FROM
                                    " . SQL_PREFIX . "calendars c
                                  LEFT JOIN
                                    " . SQL_PREFIX . "users u
                                  ON
                                    c.author_id=u.id
                                  WHERE
                                    c.start_date >= NOW()
                                  OR
                                    c.end_date >= NOW()
                                  ORDER BY
                                    c.start_date ASC,
                                    c.title ASC");
  }

  /**
   * Build the PDO-Statement for getting all entries
   *
   * @access protected
   * @return object PDOStatement the PDOStatement to execute
   *
   */
  protected function _getPreparedIcalFeedStatement() {
    return $this->_oDb->prepare("SELECT
                                    c.*,
                                    UNIX_TIMESTAMP(c.date) as date,
                                    MONTH(c.start_date) AS start_month,
                                    YEAR(c.start_date) AS start_year,
                                    UNIX_TIMESTAMP(c.start_date) AS start_date,
                                    UNIX_TIMESTAMP(c.end_date) AS end_date,
                                    u.id AS user_id,
                                    u.name AS user_name,
                                    u.surname AS user_surname,
                                    u.email AS user_email
                                  FROM
                                    " . SQL_PREFIX . "calendars c
                                  LEFT JOIN
                                    " . SQL_PREFIX . "users u
                                  ON
                                    c.author_id=u.id
                                  ORDER BY
                                    c.start_date ASC,
                                    c.title ASC");
  }

  /**
   * Get calendar overview data.
   *
   * @access public
   * @param integer $iId Id to work with
   * @return array data
   *
   */
  public function getOverview($iId = '') {
    try {
      if (isset($this->_aRequest['action']) && $this->_aRequest['action'] == 'archive')
        $oQuery = $this->_getPreparedArchiveStatement();

      elseif (isset($this->_aRequest['action']) && $this->_aRequest['action'] == 'icalfeed')
        $oQuery = $this->_getPreparedIcalFeedStatement();

      else
        $oQuery = $this->_getPreparedOverviewStatement();

      $oQuery->execute();
      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    $aData = array();
    foreach ($aResult as $aRow) {
      $iId    = $aRow['id'];
      $sMonth = I18n::get('global.months.' . $aRow['start_month']);
      $sYear  = $aRow['start_year'];
      $sDate  = $sMonth . $sYear;

      $aData[$sDate]['month']  = $sMonth;
      $aData[$sDate]['year']   = $sYear;

      $aData[$sDate]['dates'][$iId] = $this->_formatForOutput(
              $aRow,
              array('id', 'author_id')
      );

      $this->_formatDates($aData[$sDate]['dates'][$iId], 'start_date');
      $this->_formatDates($aData[$sDate]['dates'][$iId], 'end_date');
    }

    return $aData;
  }

  /**
   * Get calendar data.
   *
   * @access public
   * @param integer $iId Id to work with
   * @param boolean $bUpdate prepare data for update?
   * @return array|boolean array on success, boolean on false
   *
   */
  public function getId($iId = '', $bUpdate = false) {
    if (empty($iId) || $iId < 1)
      return false;

    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        c.*,
                                        UNIX_TIMESTAMP(c.date) as date,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email,
                                        UNIX_TIMESTAMP(c.start_date) as start_date,
                                        UNIX_TIMESTAMP(c.end_date) as end_date
                                      FROM
                                        " . SQL_PREFIX . "calendars c
                                      LEFT JOIN
                                        " . SQL_PREFIX . "users u
                                      ON
                                        c.author_id=u.id
                                      WHERE
                                        c.id = :id");

      $oQuery->bindParam('id', $iId);
      $oQuery->execute();

      $aRow = $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    if ($bUpdate) {
      $aData = $this->_formatForUpdate($aRow);
      $aData['start_date'] = date('Y-m-d', $aData['start_date']);

      if ($aData['end_date'])
        $aData['end_date'] = date('Y-m-d', $aData['end_date']);
    }
    else {
      $aData = $this->_formatForOutput(
              $aRow,
              array('id', 'author_id', 'start_date', 'end_date')
      );

      $this->_formatDates($aData, 'start_date');
      $this->_formatDates($aData, 'end_date');
    }

    return $aData;
  }

  /**
   * Create new calendar entry.
   *
   * @access public
   * @param array $aOptions (only for E_STRICT)
   * @return boolean status of query
   *
   */
  public function create($aOptions = array()) {
    try {
      $oQuery = $this->_oDb->prepare("INSERT INTO
                                        " . SQL_PREFIX . "calendars
                                        ( author_id,
                                          title,
                                          content,
                                          date,
                                          start_date,
                                          end_date)
                                      VALUES
                                        ( :author_id,
                                          :title,
                                          :content,
                                          NOW(),
                                          :start_date,
                                          :end_date)");

      $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);

      foreach (array('title', 'content') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false);
        $oQuery->bindParam(
                $sInput,
                $sValue,
                PDO::PARAM_STR);

        unset($sValue);
      }

      foreach (array('start_date', 'end_date') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput]);
        $oQuery->bindParam(
                $sInput, $sValue, PDO::PARAM_INT);

        unset($sValue);
      }

      $bReturn = $oQuery->execute();
      parent::$iLastInsertId = parent::$_oDbStatic->lastInsertId();

      return $bReturn;
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage(), false);

      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }
    }
  }

  /**
   * Update a calendar entry.
   *
   * @access public
   * @param integer $iId ID to update
   * @return boolean status of query
   *
   */
  public function update($iId) {
    if (empty($iId) || $iId < 1)
      return false;

    try {
      $oQuery = $this->_oDb->prepare("UPDATE
                                        " . SQL_PREFIX . "calendars
                                      SET
                                        author_id = :author_id,
                                        title = :title,
                                        content = :content,
                                        start_date = :start_date,
                                        end_date = :end_date
                                      WHERE
                                        id = :id");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);

      foreach (array('title', 'content') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false);
        $oQuery->bindParam(
                $sInput,
                $sValue,
                PDO::PARAM_STR);

        unset($sValue);
      }

      foreach (array('start_date', 'end_date') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput]);
        $oQuery->bindParam(
                $sInput,
                $sValue,
                PDO::PARAM_INT);

        unset($sValue);
      }

      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage(), false);

      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }
    }
  }
}