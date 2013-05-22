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
use candyCMS\Core\Helpers\Upload;
use PDO;

class Downloads extends Main {

  /**
   * Get all download data.
   *
   * @access public
   * @return array data
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
      exit('SQL error.');
    }

    foreach ($aResult as $aRow) {
      $iId = $aRow['id'];
      $sCategory = $aRow['category'];

      $iSize = Helper::getFileSize(PATH_UPLOAD . '/' . $this->_sController . '/' . $aRow['file']);

      if ($iSize != -1 || ACTIVE_TEST) {
        # Name category for overview
        $this->_aData[$sCategory]['category'] = $sCategory;

        # Files
        $this->_aData[$sCategory]['files'][$iId] = $this->_formatForOutput($aRow, array('id', 'author_id', 'downloads', 'uid'));
        $this->_aData[$sCategory]['files'][$iId]['size'] = Helper::fileSizeToString($iSize);
      }
    }

    return $this->_aData;
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
  public function getId($iId = '', $bUpdate = false) {
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
      exit('SQL error.');
    }

    $this->_aData = $bUpdate === true ? $this->_formatForUpdate($aRow) : $aRow;
    return $this->_aData;
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
    if (empty(parent::$_oDbStatic))
      parent::connectToDatabase();

    try {
      $oQuery = parent::$_oDbStatic->prepare("SELECT
                                                file
                                              FROM
                                                " . SQL_PREFIX . "downloads
                                              WHERE
                                                id = :id
                                              LIMIT 1");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetch(PDO::FETCH_ASSOC);
      return $aResult['file'];
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Create new download.
   *
   * @access public
   * @param string $sFile file name
   * @param string $sExtension file extension
   * @return boolean status of query
   * @todo make the model::create() action compilant with the main::create() action
   *
   */
  public function create($sFile, $sExtension) {
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
      $oQuery->bindParam('file', $sFile, PDO::PARAM_STR);
      $oQuery->bindParam('extension', $sExtension, PDO::PARAM_STR);

      foreach (array('title', 'content') as $sInput)
        $oQuery->bindParam(
                $sInput,
                Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false),
                PDO::PARAM_STR);

      foreach (array('category') as $sInput)
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
   * Update a download.
   *
   * @access public
   * @param integer $iId ID to update
   * @return boolean status of query
   *
   */
  public function update($iId) {
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

      foreach (array('title', 'content') as $sInput)
        $oQuery->bindParam(
                $sInput,
                Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false),
                PDO::PARAM_STR);

      foreach (array('category', 'downloads') as $sInput)
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
    # Get file name
    $sFile = $this->getFileName($iId);

    $bReturn = parent::destroy($iId);

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
      try {
        parent::$_oDbStatic->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      }

      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
      exit('SQL error.');
    }
  }
}
