<?php

/**
 * Handle all download SQL requests.
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
use PDO;

class Downloads extends Main {

  /**
   * Get all download data.
   *
   * @access public
   * @return array $aData
   *
   */
  public function getOverview() {
    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        d.*,
                                        UNIX_TIMESTAMP(d.date) as date,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email
                                      FROM
                                        " . SQL_PREFIX . "downloads d
                                      LEFT JOIN
                                        " . SQL_PREFIX . "users u
                                      ON
                                        d.author_id=u.id
                                      ORDER BY
                                        d.category ASC,
                                        d.title ASC");

      $oQuery->execute();
      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    foreach ($aResult as $aRow) {
      $iId = $aRow['id'];
      $sCategory = $aRow['category'];

      $iSize = Helper::getFileSize(PATH_UPLOAD . '/' . $this->_sController . '/' . $aRow['file']);

      if ($iSize != -1 || ACTIVE_TEST) {
        # Name category for overview
        $aData[$sCategory]['category'] = $sCategory;

        # Files
        $aData[$sCategory]['files'][$iId] = $this->_formatForOutput($aRow, array('id', 'author_id', 'downloads', 'uid'));
        $aData[$sCategory]['files'][$iId]['size'] = Helper::fileSizeToString($iSize);
      }
    }

    return $aData;
  }

  /**
   * Get download data by id.
   *
   * @access public
   * @param integer $iId ID to get data from.
   * @param boolean $bUpdate prepare data for update
   * @return array data
   *
   */
  public function getId($iId, $bUpdate = false) {
    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        d.*,
                                        UNIX_TIMESTAMP(d.date) as date,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email
                                      FROM
                                        " . SQL_PREFIX . "downloads d
                                      LEFT JOIN
                                        " . SQL_PREFIX . "users u
                                      ON
                                        d.author_id=u.id
                                      WHERE
                                        d.id = :id
                                      LIMIT
                                        1");

      $oQuery->bindParam('id', $iId);
      $oQuery->execute();
      $aRow = $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    $aData = $bUpdate ? $this->_formatForUpdate($aRow) : $aRow;
    return $aData;
  }

  /**
   * Return the name of a file.
   *
   * @static
   * @access public
   * @param integer $iId ID to get data from.
   * @return string $aResult['file'] file name.
   *
   */
  public static function getFileName($iId) {
    try {
      $oQuery = parent::$_oDbStatic->prepare("SELECT
                                                file
                                              FROM
                                                " . SQL_PREFIX . "downloads
                                              WHERE
                                                id = :id
                                              LIMIT
                                                1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetch(PDO::FETCH_ASSOC);
      return $aResult['file'];
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }
  }

  /**
   * Create new download.
   *
   * @access public
   * @param array $aOptions
   * @return boolean status of query
   *
   */
  public function create($aOptions) {
    try {
      $oQuery = $this->_oDb->prepare("INSERT INTO
                                        " . SQL_PREFIX . "downloads
                                        ( author_id,
                                          title,
                                          content,
                                          category,
                                          file,
                                          extension,
                                          date)
                                      VALUES
                                        ( :author_id,
                                          :title,
                                          :content,
                                          :category,
                                          :file,
                                          :extension,
                                          NOW() )");

      $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);

      # Preset
      $oQuery->bindParam('file', $aOptions['file'], PDO::PARAM_STR);
      $oQuery->bindParam('extension', $aOptions['extension'], PDO::PARAM_STR);

      foreach (array('title', 'content') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false);
        $oQuery->bindParam(
                $sInput,
                $sValue,
                PDO::PARAM_STR);

        unset($sValue);
      }

      foreach (array('category') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput]);
        $oQuery->bindParam(
                $sInput,
                $sValue,
                PDO::PARAM_STR);

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
   * Update a download.
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
                                        " . SQL_PREFIX . "downloads
                                      SET
                                        author_id = :author_id,
                                        title = :title,
                                        category = :category,
                                        content = :content,
                                        downloads = :downloads
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

      foreach (array('category', 'downloads') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput]);
        $oQuery->bindParam(
                $sInput,
                $sValue,
                PDO::PARAM_STR);

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

  /**
   * Destroy a download and its file.
   *
   * @access public
   * @param integer $iId ID to destroy
   * @param string $sController controller to use, obsolete and only for not giving E_STRICT warnings
   * @return boolean status of query
   *
   */
  public function destroy($iId, $sController = '') {
    if (empty($iId) || $iId < 1)
      return false;

    # Get file name
    $sFile    = $this->getFileName($iId);
    $bReturn  = parent::destroy($iId);

    if (is_file(Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sController . '/' . $sFile)))
      unlink(Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sController . '/' . $sFile));

    return $bReturn;
  }

  /**
   * Updates a download count +1.
   *
   * @static
   * @access public
   * @param integer $iId ID to update
   * @return boolean status of query
   *
   */
  public static function updateDownloadCount($iId) {
    if (empty(parent::$_oDbStatic))
      parent::connectToDatabase();

    try {
      $oQuery = parent::$_oDbStatic->prepare("UPDATE
                                                " . SQL_PREFIX . "downloads
                                              SET
                                                downloads = downloads + 1
                                              WHERE
                                                id = :id");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage(), false);

      try {
        parent::$_oDbStatic->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }
    }
  }
}