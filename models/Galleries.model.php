<?php

/**
 * Handle all gallery SQL requests.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 *
 */

namespace CandyCMS\Core\Models;

use CandyCMS\Core\Helpers\AdvancedException;
use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\Pagination;
use CandyCMS\Core\Helpers\Upload;
use PDO;

class Galleries extends Main {

  /**
   *
   * @access private
   * @var array
   *
   */
  private $_aThumbs;

  /**
   * Get gallery album files.
   *
   * @access public
   * @param integer $iId Album-ID to load data from.
   * @param boolean $bUpdate prepare data for update
   * @param boolean $bAdvancedImageInformation provide image with advanced information (MIME_TYPE etc.)
   * @return array data from _setData
   *
   */
  public function getId($iId, $bUpdate = false, $bAdvancedImageInformation = false) {
    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                      a.*,
                                      UNIX_TIMESTAMP(a.date) as date,
                                      u.id AS user_id,
                                      u.name AS user_name,
                                      u.surname AS user_surname,
                                      u.email AS user_email,
                                      COUNT(f.id) AS files_sum
                                    FROM
                                      " . SQL_PREFIX . "gallery_albums a
                                    LEFT JOIN
                                      " . SQL_PREFIX . "users u
                                    ON
                                      a.author_id=u.id
                                    LEFT JOIN
                                      " . SQL_PREFIX . "gallery_files f
                                    ON
                                      f.album_id=a.id
                                    WHERE
                                      a.id = :id
                                    GROUP BY
                                      a.id
                                    ORDER BY
                                      a.id DESC");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('0044 - ' . $p->getMessage());
      exit('SQL error.');
    }

    # Update a single entry. Fix it with 0 o
    if ($bUpdate === true)
      $this->_aData = $this->_formatForUpdate($aResult[0]);

    else {
      foreach ($aResult as $aRow) {
        $iId = $aRow['id'];

        # need to specify 'galleries' because this might be called for rss feed generation
        $this->_aData[$iId] = $this->_formatForOutput(
                $aRow,
                array('id', 'user_id', 'files_sum'),
                null,
                'galleries');

        $this->_aData[$iId]['files'] = $aRow['files_sum'] > 0 ?
                $this->getThumbnails($aRow['id'], $bAdvancedImageInformation) :
                '';

        $this->_aData[$iId]['url_createfile'] = $this->_aData[$iId]['url_clean'] . '/createfile';
      }
    }

    return $this->_aData;
  }

  /**
   * Get album overview
   *
   * @access public
   * @param boolean $bAdvancedImageInformation provide image with advanced information (MIME_TYPE etc.)
   * @param integer $iLimit blog post limit
   * @return array data from _setData
   *
   */
  public function getOverview($bAdvancedImageInformation = false, $iLimit = LIMIT_ALBUMS) {
    $iResult  = 0;

    try {
      $oQuery = $this->_oDb->query("SELECT COUNT(*) FROM " . SQL_PREFIX . "gallery_albums");
      $iResult = $oQuery->fetchColumn();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('0042 - ' . $p->getMessage());
      exit('SQL error.');
    }

    require_once PATH_STANDARD . '/vendor/candyCMS/core/helpers/Pagination.helper.php';
    $this->oPagination = new Pagination($this->_aRequest, (int) $iResult, $iLimit);

    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                      a.*,
                                      UNIX_TIMESTAMP(a.date) as date,
                                      u.id AS user_id,
                                      u.name AS user_name,
                                      u.surname AS user_surname,
                                      u.email AS user_email,
                                      COUNT(f.id) AS files_sum
                                    FROM
                                      " . SQL_PREFIX . "gallery_albums a
                                    LEFT JOIN
                                      " . SQL_PREFIX . "users u
                                    ON
                                      a.author_id=u.id
                                    LEFT JOIN
                                      " . SQL_PREFIX . "gallery_files f
                                    ON
                                      f.album_id=a.id
                                    GROUP BY
                                      a.id
                                    ORDER BY
                                      a.id DESC
                                    LIMIT
                                      :offset, :limit");

      $oQuery->bindParam('limit', $this->oPagination->getLimit(), PDO::PARAM_INT);
      $oQuery->bindParam('offset', $this->oPagination->getOffset(), PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('0044 - ' . $p->getMessage());
      exit('SQL error.');
    }

    foreach ($aResult as $aRow) {
      $iId = $aRow['id'];

      # need to specify 'galleries' because this might be called for rss feed generation
      $this->_aData[$iId] = $this->_formatForOutput(
              $aRow,
              array('id', 'user_id', 'files_sum'),
              null,
              'galleries');

      $this->_aData[$iId]['files'] = $aRow['files_sum'] > 0 ?
              $this->getThumbnails($aRow['id'], $bAdvancedImageInformation) :
              '';

      $this->_aData[$iId]['url_createfile'] = $this->_aData[$iId]['url_clean'] . '/createfile';
    }

    return $this->_aData;
  }

  /**
   * Get thumbnail array.
   *
   * @access public
   * @param integer $iId album id to fetch images from
   * @param boolean $bAdvancedImageInformation fetch additional information like width, height etc.
   * @return array $this->_aThumbs processed array with image information
   *
   */
  public function getThumbnails($iId, $bAdvancedImageInformation = false) {
    # Clear existing array (fix, when we got no images at a gallery
    if (!empty($this->_aThumbs))
      unset($this->_aThumbs);

    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        f.*,
                                        UNIX_TIMESTAMP(f.date) as date,
                                        u.id AS user_id,
                                        u.name AS user_name,
                                        u.surname AS user_surname,
                                        u.email AS user_email
                                      FROM
                                        " . SQL_PREFIX . "gallery_files f
                                      LEFT JOIN
                                        " . SQL_PREFIX . "users u
                                      ON
                                        f.author_id=u.id
                                      WHERE
                                        f.album_id= :album_id
                                      ORDER BY
                                        f.position ASC,
                                        f.date ASC");

      $oQuery->bindParam('album_id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('0045 - ' . $p->getMessage());
      exit('SQL error.');
    }

    $iLoop = 0;
    foreach ($aResult as $aRow) {
      $iId           = $aRow['id'];
      $sUrlUpload    = Helper::addSlash(PATH_UPLOAD . '/galleries/' . $aRow['album_id']);

      $this->_aThumbs[$iId]                 = $this->_formatForOutput($aRow, array('id', 'album_id', 'author_id'));
      $this->_aThumbs[$iId]['url']          = '/galleries/' . $aRow['album_id'] . '/image/' . $iId;

      foreach (array('32', 'popup', 'original', 'thumb') as $sSize)
        $this->_aThumbs[$iId]['url_' . $sSize] = $sUrlUpload . '/' . $sSize . '/' . $aRow['file'];

      $this->_aThumbs[$iId]['url_upload']   = $sUrlUpload;
      $this->_aThumbs[$iId]['url_thumb']    = $sUrlUpload . '/thumbnail/' . $aRow['file'];

      # /{$_REQUEST.controller}/{$f.id}/updatefile
      $this->_aThumbs[$iId]['url_update']   = $this->_aThumbs[$iId]['url_update'] . 'file';

      # /{$_REQUEST.controller}/{$f.id}/destroyfile?album_id={$_REQUEST.id}
      $this->_aThumbs[$iId]['url_destroy']  = $this->_aThumbs[$iId]['url_destroy'] . 'file?album_id=' . $aRow['album_id'];
      $this->_aThumbs[$iId]['thumb_width']  = THUMB_DEFAULT_X;
      $this->_aThumbs[$iId]['loop']         = $iLoop;

      # We want to get the image dimension of the original image.
      # This function is not set to default due its long processing time.
      if ($bAdvancedImageInformation === true) {
        $aPopupSize = getimagesize(Helper::removeSlash($this->_aThumbs[$iId]['url_popup']));
        $aThumbSize = getimagesize(Helper::removeSlash($this->_aThumbs[$iId]['url_thumb']));
        $iImageSize = filesize(Helper::removeSlash($this->_aThumbs[$iId]['url_popup']));

        $this->_aThumbs[$iId]['popup_width']  = $aPopupSize[0];
        $this->_aThumbs[$iId]['popup_height'] = $aPopupSize[1];
        $this->_aThumbs[$iId]['popup_size']   = $iImageSize;
        $this->_aThumbs[$iId]['popup_mime']   = $aPopupSize['mime'];
        $this->_aThumbs[$iId]['thumb_width']  = $aThumbSize[0];
        $this->_aThumbs[$iId]['thumb_height'] = $aThumbSize[1];
      }

      ++$iLoop;
    }

    return $this->_aThumbs;
  }

  /**
   * Return album name and album content.
   *
   * @static
   * @access public
   * @param integer $iId album ID
   * @param array $aRequest current request
   * @return string album name
   *
   */
  public static function getAlbumNameAndContent($iId, $aRequest = '') {
    if (empty(parent::$_oDbStatic))
      parent::connectToDatabase();

    try {
      $oQuery = parent::$_oDbStatic->prepare("SELECT
                                                title, content
                                              FROM
                                                " . SQL_PREFIX . "gallery_albums
                                              WHERE
                                                id = :album_id");

      $oQuery->bindParam('album_id', $iId, PDO::PARAM_INT);

      $bReturn = $oQuery->execute();
      $aResult = $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('0046 - ' . $p->getMessage());
      exit('SQL error.');
    }

    if ($bReturn === true) {
      foreach ($aResult as $sKey => $sValue)
        $aResult[$sKey] = Helper::formatOutput(
                        $sValue, isset($aRequest['highlight']) ? $aRequest['highlight'] : '');

      return $aResult;
    }
  }

  /**
   * Get all file data.
   *
   * @static
   * @access public
   * @param integer $iId album ID
   * @return array file data
   *
   */
  public static function getFileData($iId) {
    if (empty(parent::$_oDbStatic))
      parent::connectToDatabase();

    try {
      $oQuery = parent::$_oDbStatic->prepare("SELECT
                                                *,
                                                UNIX_TIMESTAMP(date) as date
                                              FROM
                                                " . SQL_PREFIX . "gallery_files
                                              WHERE
                                                id = :id");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aData = $oQuery->fetch(PDO::FETCH_ASSOC);

      foreach (array('id', 'album_id', 'author_id') as $sInt)
        $aData[$sInt] = (int) $aData[$sInt];

      return $aData;
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('0049 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Create a new album.
   *
   * @access public
   * @return boolean status of query
   *
   */
  public function create() {
    try {
      $oQuery = $this->_oDb->prepare("INSERT INTO
                                        " . SQL_PREFIX . "gallery_albums
                                        ( author_id,
                                          title,
                                          content,
                                          date)
                                      VALUES
                                        ( :author_id,
                                          :title,
                                          :content,
                                          NOW() )");

      $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);

      foreach (array('title', 'content') as $sInput)
        $oQuery->bindParam(
                $sInput,
                Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false),
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
        AdvancedException::reportBoth('0050 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0051 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Update an album.
   *
   * @access public
   * @param integer $iId
   * @return boolean status of query
   *
   */
  public function update($iId) {
    try {
      $oQuery = $this->_oDb->prepare("UPDATE
                                        " . SQL_PREFIX . "gallery_albums
                                      SET
                                        title = :title,
                                        content = :content
                                      WHERE
                                        id = :id");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);

      foreach (array('title', 'content') as $sInput)
        $oQuery->bindParam(
                $sInput,
                Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false),
                PDO::PARAM_STR);

      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('0052 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0053 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Destroy a full album.
   *
   * @access public
   * @param integer $iId album ID
   * @return type
   *
   */
  public function destroy($iId) {
    $sPath = Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sController . '/' . (int) $iId);

    # Fetch all images
    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        file
                                      FROM
                                        " . SQL_PREFIX . "gallery_files
                                      WHERE
                                        album_id = :album_id");

      $oQuery->bindParam('album_id', $iId);

      $bReturn = $oQuery->execute();
      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('0054 - ' . $p->getMessage());
      exit('SQL error.');
    }

    if ($bReturn === true) {
      # Destroy files from database
      try {
        $oQuery = $this->_oDb->prepare("DELETE FROM
                                          " . SQL_PREFIX . "gallery_files
                                        WHERE
                                          album_id = :album_id");

        $oQuery->bindParam('album_id', $iId);
        $oQuery->execute();

        foreach (array ('32', 'popup', 'original', 'thumbnail') as $sSize) {
          # Destroy files from disk
          foreach ($aResult as $aRow)
            @unlink($sPath . '/' . $sSize . '/' . $aRow['file']);

          # Destroy folders
          @rmdir($sPath . '/' . $sSize);
        }
      }
      catch (\PDOException $p) {
        try {
          $this->_oDb->rollBack();
        }
        catch (\Exception $e) {
          AdvancedException::reportBoth('0055 - ' . $e->getMessage());
        }

        AdvancedException::reportBoth('0056 - ' . $p->getMessage());
        exit('SQL error.');
      }

      # Destroy albums from database
      try {
        $oQuery = $this->_oDb->prepare("DELETE FROM
                                          " . SQL_PREFIX . "gallery_albums
                                        WHERE
                                          id = :album_id
                                        LIMIT
                                          1");

        $oQuery->bindParam('album_id', $iId);
        $bReturn = $oQuery->execute();

        # Remove album folder
        @rmdir($sPath);
        return $bReturn;
      }
      catch (\PDOException $p) {
        try {
          $this->_oDb->rollBack();
        }
        catch (\Exception $e) {
          AdvancedException::reportBoth('0057 - ' . $e->getMessage());
        }

        AdvancedException::reportBoth('0058 - ' . $p->getMessage());
        exit('SQL error.');
      }
    }
  }

  /**
   * Create a new file.
   *
   * @access public
   * @param string $sFile file name
   * @param string $sExtension file extension
   * @return boolean status of query
   *
   */
  public function createFile($sFile, $sExtension) {
    try {
      $oQuery = $this->_oDb->prepare("INSERT INTO
                                        " . SQL_PREFIX . "gallery_files
                                        ( album_id,
                                          author_id,
                                          file,
                                          extension,
                                          content,
                                          date,
                                          position)
                                      VALUES
                                        ( :album_id,
                                          :author_id,
                                          :file,
                                          :extension,
                                          :content,
                                          NOW(),
                                          :position)");

      $iPosition = (int) Helper::getLastEntry('gallery_files');
      $oQuery->bindParam('album_id', $this->_aRequest['id'], PDO::PARAM_INT);
      $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);
      $oQuery->bindParam('file', $sFile, PDO::PARAM_STR);
      $oQuery->bindParam('extension', $sExtension, PDO::PARAM_STR);
      $oQuery->bindParam('position', $iPosition, PDO::PARAM_INT);

      foreach (array('content') as $sInput)
        $oQuery->bindParam(
                $sInput,
                Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false),
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
        AdvancedException::reportBoth('0059 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0060 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Update a file.
   *
   * @access public
   * @param integer $iId file ID
   * @return boolean status of query
   *
   */
  public function updateFile($iId) {
    try {
      $oQuery = $this->_oDb->prepare("UPDATE
                                        " . SQL_PREFIX . "gallery_files
                                      SET
                                        content = :content
                                      WHERE
                                        id = :id");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);

      foreach (array('content') as $sInput)
        $oQuery->bindParam(
                $sInput,
                Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false),
                PDO::PARAM_STR);

      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      try {
        $this->_oDb->rollBack();
      }
      catch (\Exception $e) {
        AdvancedException::reportBoth('0061 - ' . $e->getMessage());
      }

      AdvancedException::reportBoth('0062 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Destroy a file and delete from HDD.
   *
   * @access public
   * @param integer $iId file ID
   * @return boolean status of query
   *
   */
  public function destroyFile($iId) {
    try {
      $oQuery = $this->_oDb->prepare("SELECT
                                        file,
                                        album_id
                                      FROM
                                        " . SQL_PREFIX . "gallery_files
                                      WHERE
                                        id = :id");

      $oQuery->bindParam('id', $iId);

      $bReturn = $oQuery->execute();
      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('0063 - ' . $p->getMessage());
      exit('SQL error.');
    }

    if ($bReturn === true) {
      try {
        $oQuery = $this->_oDb->prepare("DELETE FROM
                                          " . SQL_PREFIX . "gallery_files
                                        WHERE
                                          id = :id
                                        LIMIT
                                          1");

        $oQuery->bindParam('id', $iId);
        $bReturn = $oQuery->execute();

        if ($bReturn) {
          foreach ($aResult as $aRow) {
            $sPath = Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sController . '/' . $aRow['album_id']);

            foreach (array('32', 'popup', 'original', 'thumbnail') as $sSize)
              @unlink($sPath . '/' . $sSize . '/' . $aRow['file']);
          }
        }
        return $bReturn;
      }
      catch (\PDOException $p) {
        try {
          $this->_oDb->rollBack();
        }
        catch (\Exception $e) {
          AdvancedException::reportBoth('0064 - ' . $e->getMessage());
        }

        AdvancedException::reportBoth('0065 - ' . $p->getMessage());
        exit('SQL error.');
      }
    }
  }

  /**
   * Update filepositions.
   *
   * @access public
   * @param integer $iAlbumId album ID
   * @return boolean status of query
   *
   */
  public function updateFilePositions($iAlbumId) {
    $iAlbumId = (int)$iAlbumId;
    $sSQL = '';

    foreach ($this->_aRequest['galleryfiles'] as $iKey => $iValue) {
      $iKey   = (int)$iKey;
      $iValue = (int)$iValue;

      $sSQL .= "UPDATE
                  " . SQL_PREFIX . "gallery_files
                SET
                  position = '".$iKey."'
                WHERE
                  id = '".$iValue."'
                AND
                  album_id = '".$iAlbumId."'
                LIMIT 1;";
    }

    try {
      $oQuery = $this->_oDb->prepare($sSQL);
      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth('0063 - ' . $p->getMessage());
      exit('SQL error.');
    }
  }
}