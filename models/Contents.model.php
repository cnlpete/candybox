<?php

/**
 * Handle all content SQL requests.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 1.0
 *
 */

namespace candyCMS\Core\Models;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\Pagination;
use PDO;

class Contents extends Main {

  /**
   * Get content overview data.
   *
   * @access public
   * @param integer $iLimit blog post limit
   * @return array $this->_aData
   *
   */
  public function getOverview($iLimit = 50) {
    $iPublished = isset($this->_aSession['user']['role']) && $this->_aSession['user']['role'] >= 3 ? 0 : 1;

    try {
      $oQuery = $this->_oDb->prepare("SELECT COUNT(*) FROM " . SQL_PREFIX . "contents WHERE published >= :published");
      $oQuery->bindParam('published', $iPublished, PDO::PARAM_INT);
      $oQuery->execute();
      $iResult = $oQuery->fetchColumn();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
      exit('SQL error.');
    }

    require_once PATH_STANDARD . '/vendor/candycms/core/helpers/Pagination.helper.php';
    $this->oPagination = new Pagination($this->_aRequest, $iResult, $iLimit);

    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        c.*,
                                        UNIX_TIMESTAMP(c.date) as date,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email
                                      FROM
                                        " . SQL_PREFIX . "contents c
                                      LEFT JOIN
                                        " . SQL_PREFIX . "users u
                                      ON
                                        c.author_id=u.id
                                      WHERE
                                        published >= :published
                                      ORDER BY
                                        c.title ASC
                                      LIMIT
                                        :offset, :limit");

      $oQuery->bindParam('published', $iPublished, PDO::PARAM_INT);
      $oQuery->bindValue('limit', $this->oPagination->getLimit(), PDO::PARAM_INT);
      $oQuery->bindValue('offset', $this->oPagination->getOffset(), PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
      exit('SQL error.');
    }

    foreach ($aResult as $aRow) {
      $iId = $aRow['id'];

      $this->_aData[$iId] = $this->_formatForOutput(
              $aRow,
              array('id', 'uid', 'author_id'),
              array('published'),
              'contents');
    }

    return $this->_aData;
  }

  /**
   * Get content entry data.
   *
   * @access public
   * @param integer $iId ID to load data from.
   * @param boolean $bUpdate prepare data for update
   * @return array $this->_aData
   *
   */
  public function getId($iId, $bUpdate = false) {
    $iPublished = isset($this->_aSession['user']['role']) && $this->_aSession['user']['role'] >= 3 ? 0 : 1;

    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        c.*,
                                        UNIX_TIMESTAMP(c.date) as date,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email
                                      FROM
                                        " . SQL_PREFIX . "contents c
                                      LEFT JOIN
                                        " . SQL_PREFIX . "users u
                                      ON
                                        c.author_id=u.id
                                      WHERE
                                        c.id = :id
                                      AND
                                        published >= :published
                                      LIMIT
                                        1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->bindParam('published', $iPublished, PDO::PARAM_INT);
      $oQuery->execute();

      $aRow = $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
      exit('SQL error.');
    }

    if ($bUpdate === true)
      $this->_aData = $this->_formatForUpdate($aRow);

    else {
      $this->_aData = $this->_formatForOutput(
              $aRow,
              array('id', 'uid', 'author_id'),
              array('published'),
              'contents');
    }

    return $this->_aData;
  }

  /**
   * Create a content entry.
   *
   * @access public
   * @param array $aOptions (only for E_STRICT)
   * @return boolean status of query
   *
   */
  public function create($aOptions) {
    $iPublished = isset($this->_aRequest[$this->_sController]['published']) &&
            $this->_aRequest[$this->_sController]['published'] == true ?
            1 :
            0;

    try {
      $oQuery = $this->_oDb->prepare("INSERT INTO
                                        " . SQL_PREFIX . "contents
                                        ( author_id,
                                          title,
                                          teaser,
                                          keywords,
                                          content,
                                          date,
                                          published)
                                      VALUES
                                        ( :author_id,
                                          :title,
                                          :teaser,
                                          :keywords,
                                          :content,
                                          NOW(),
                                          :published)");

      $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);
      $oQuery->bindParam('published', $iPublished, PDO::PARAM_INT);

      foreach (array('title', 'teaser', 'content') as $sInput)
        $oQuery->bindParam(
                $sInput,
                Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false),
                PDO::PARAM_STR);

      foreach (array('keywords') as $sInput)
        $oQuery->bindParam(
                $sInput,
                Helper::formatInput($this->_aRequest[$this->_sController][$sInput]),
                PDO::PARAM_STR);

      $bReturn = $oQuery->execute();
      parent::$iLastInsertId = parent::$_oDbStatic->lastInsertId();

      return $bReturn;
    }
    catch (\PDOException $p) {
      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }

      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Update a content entry.
   *
   * @access public
   * @param integer $iId ID to update
   * @return boolean status of query
   *
   */
  public function update($iId) {
    $iPublished = isset($this->_aRequest[$this->_sController]['published']) &&
            $this->_aRequest[$this->_sController]['published'] == true ?
            1 :
            0;

    try {
      $oQuery = $this->_oDb->prepare("UPDATE
                                        " . SQL_PREFIX . "contents
                                      SET
                                        title = :title,
                                        teaser = :teaser,
                                        keywords = :keywords,
                                        content = :content,
                                        date = NOW(),
                                        author_id = :author_id,
                                        published = :published
                                      WHERE
                                        id = :id");

      $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);
      $oQuery->bindParam('published', $iPublished, PDO::PARAM_INT);
      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);

      foreach (array('title', 'teaser', 'content') as $sInput)
        $oQuery->bindParam(
                $sInput,
                Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false),
                PDO::PARAM_STR);

      foreach (array('keywords') as $sInput)
        $oQuery->bindParam(
                $sInput,
                Helper::formatInput($this->_aRequest[$this->_sController][$sInput]),
                PDO::PARAM_STR);

      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }

      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
      exit('SQL error.');
    }
  }
}
