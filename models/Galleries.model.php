<?php

/**
 * Handle all gallery SQL requests.
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
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\Pagination;
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
   * @param integer $iId album ID to load data from.
   * @param boolean $bUpdate prepare data for update?
   * @param boolean $bAdvancedImageInformation provide image with advanced information (MIME_TYPE etc.)?
   * @return array|boolean array on success, boolean on false
   *
   */
  public function getId($iId, $bUpdate = false, $bAdvancedImageInformation = false) {
    if (empty($iId) || $iId < 1)
      return false;

    try {
      $sWhere = isset($this->_aSession['user']['role']) && $this->_aSession['user']['role'] >= 3 ?
              'WHERE 1' :
              "WHERE published = '1'";

      $sOrder = (SORTING_GALLERY_FILES == 'ASC' || SORTING_GALLERY_FILES == 'DESC') ?
              SORTING_GALLERY_FILES :
              'ASC';

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
                                    " . $sWhere . "
                                    AND
                                      a.id = :id
                                    GROUP BY
                                      a.id
                                    ORDER BY
                                      a.id " . $sOrder);

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aRow = $oQuery->fetch(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    # Update a single entry.
    if ($bUpdate)
      $this->_aData = $this->_formatForUpdate($aRow);

    else {
      # Need to specify 'galleries' because this might be called for RSS feed generation
      $this->_aData = $this->_formatForOutput(
              $aRow,
              array('id', 'user_id', 'files_sum'),
              array('published'),
              'galleries');

      $this->_aData['files'] = $aRow['files_sum'] > 0 ?
              $this->_getThumbnails($aRow['id'], $bAdvancedImageInformation) :
              '';

      if ($this->_aSession['user']['role'] >= 3)
        $this->_aData['url_createfile'] = $this->_aData['url_clean'] . '/createfile';
    }

    return $this->_aData;
  }

  /**
   * Get album overview
   *
   * @access public
   * @param integer $iLimit blog post limit
   * @param boolean $bAdvancedImageInformation provide image with advanced information (MIME_TYPE etc.)
   * @return array data from _setData
   *
   */
  public function getOverview($iLimit = LIMIT_ALBUMS, $bAdvancedImageInformation = false) {
    $iResult = 0;

    $sWhere = isset($this->_aSession['user']['role']) && $this->_aSession['user']['role'] >= 3 ?
            'WHERE 1' :
            "WHERE published = '1'";

    try {
      $oQuery   = $this->_oDb->query("SELECT COUNT(*) FROM " . SQL_PREFIX . "gallery_albums");
      $iResult  = $oQuery->fetchColumn();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    require_once PATH_STANDARD . '/vendor/candycms/core/helpers/Pagination.helper.php';
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
                                    " . $sWhere . "
                                    GROUP BY
                                      a.id
                                    ORDER BY
                                      a.id DESC
                                    LIMIT
                                      :offset, :limit");

      $oQuery->bindValue('limit', $this->oPagination->getLimit(), PDO::PARAM_INT);
      $oQuery->bindValue('offset', $this->oPagination->getOffset(), PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    foreach ($aResult as $aRow) {
      $iId = $aRow['id'];

      # need to specify 'galleries' because this might be called for rss feed generation
      $this->_aData[$iId] = $this->_formatForOutput(
              $aRow,
              array('id', 'user_id', 'files_sum'),
              array('published'),
              'galleries');

      $this->_aData[$iId]['files'] = $aRow['files_sum'] > 0 ?
              $this->_getThumbnails($aRow['id'], $bAdvancedImageInformation) :
              '';

      $this->_aData[$iId]['url_createfile'] = $this->_aData[$iId]['url_clean'] . '/createfile';
    }

    return $this->_aData;
  }

  /**
   * Get thumbnail array.
   *
   * @access protected
   * @param integer $iId album id to fetch images from
   * @param boolean $bAdvancedImageInformation fetch additional information like width, height etc.
   * @return array|boolean array on success, boolean on false
   *
   */
  protected function _getThumbnails($iId, $bAdvancedImageInformation = false) {
    if (empty($iId) || $iId < 1)
      return false;

    # Clear existing array
    # Bugfix when we got no images at a gallery
    if (!empty($this->_aThumbs))
      unset($this->_aThumbs);

    try {
      $sOrder = (SORTING_GALLERY_FILES == 'ASC' || SORTING_GALLERY_FILES == 'DESC') ?
              SORTING_GALLERY_FILES :
              'ASC';

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
                                        f.date " . $sOrder);

      $oQuery->bindParam('album_id', $iId, PDO::PARAM_INT);
      $oQuery->execute();

      $aResult = $oQuery->fetchAll(PDO::FETCH_ASSOC);
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }

    $iLoop = 0;
    foreach ($aResult as $aRow) {
      $iId           = $aRow['id'];
      $sUrlUpload    = Helper::addSlash(PATH_UPLOAD . '/galleries/' . $aRow['album_id']);

      $this->_aThumbs[$iId]         = $this->_formatForOutput($aRow, array('id', 'album_id', 'author_id'));
      $this->_aThumbs[$iId]['url']  = '/galleries/' . $aRow['album_id'] . '/image/' . $iId;

      foreach (array('32', 'popup', 'original', 'thumb') as $sSize)
        $this->_aThumbs[$iId]['url_' . $sSize] = $sUrlUpload . '/' . $sSize . '/' . $aRow['file'];

      $this->_aThumbs[$iId]['url_upload'] = $sUrlUpload;
      $this->_aThumbs[$iId]['url_thumb']  = $sUrlUpload . '/thumbnail/' . $aRow['file'];

      # /{$_REQUEST.controller}/{$f.id}/updatefile
      # /{$_REQUEST.controller}/{$f.id}/destroyfile?album_id={$_REQUEST.id}
      if ($this->_aSession['user']['role'] >= 3) {
        $this->_aThumbs[$iId]['url_update']   = $this->_aThumbs[$iId]['url_update'] . 'file';
        $this->_aThumbs[$iId]['url_destroy']  = $this->_aThumbs[$iId]['url_destroy'] . 'file?album_id=' . $aRow['album_id'];
      }

      $this->_aThumbs[$iId]['thumb_width']  = THUMB_DEFAULT_X;
      $this->_aThumbs[$iId]['loop']         = $iLoop;

      # We want to get the image dimension of the original image.
      # This function is not set to default due its long processing time.
      if ($bAdvancedImageInformation) {
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
   * Get all file data.
   *
   * @static
   * @access public
   * @param integer $iId album ID
   * @return boolean|array false if we got no ID, file data if query was successful
   *
   */
  public static function getFileData($iId) {
    if (empty($iId) || $iId < 1)
      return false;

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
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }
  }

  /**
   * Create a new album.
   *
   * @access public
   * @param array $aOptions (only for E_STRICT)
   * @return boolean status of query
   *
   */
  public function create($aOptions = '') {
    $iPublished = isset($this->_aRequest[$this->_sController]['published']) &&
            $this->_aRequest[$this->_sController]['published'] == true ?
            1 :
            0;

    try {
      $oQuery = $this->_oDb->prepare("INSERT INTO
                                        " . SQL_PREFIX . "gallery_albums
                                        ( author_id,
                                          title,
                                          content,
                                          published,
                                          date)
                                      VALUES
                                        ( :author_id,
                                          :title,
                                          :content,
                                          :published,
                                          NOW() )");

      $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);
      $oQuery->bindParam('published', $iPublished, PDO::PARAM_INT);

      foreach (array('title', 'content') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false);
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
   * Update an album.
   *
   * @access public
   * @param integer $iId
   * @return array|boolean array on success, boolean on false
   *
   */
  public function update($iId) {
    if (empty($iId) || $iId < 1)
      return false;

    $iPublished = isset($this->_aRequest[$this->_sController]['published']) &&
            $this->_aRequest[$this->_sController]['published'] == true ?
            1 :
            0;

    try {
      $oQuery = $this->_oDb->prepare("UPDATE
                                        " . SQL_PREFIX . "gallery_albums
                                      SET
                                        title = :title,
                                        content = :content,
                                        published = :published
                                      WHERE
                                        id = :id");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);
      $oQuery->bindParam('published', $iPublished, PDO::PARAM_INT);

      foreach (array('title', 'content') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false);
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
   * Destroy a full album.
   *
   * @access public
   * @param integer $iId album ID
   * @param string $sController controller to use, obsolete and only for not giving E_STRICT warnings
   * @return array|boolean array on success, boolean on false
   *
   */
  public function destroy($iId, $sController = '') {
    if (empty($iId) || $iId < 1)
      return false;

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
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
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
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage(), false);

        try {
          $this->_oDb->rollBack();
        }
        catch (\Exception $e) {
          AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
        }
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
                                          :date,
                                          :position)");

      $iPosition = (int) Helper::getLastEntry('gallery_files');
      $oQuery->bindParam('album_id', $this->_aRequest['id'], PDO::PARAM_INT);
      $oQuery->bindParam('author_id', $this->_aSession['user']['id'], PDO::PARAM_INT);
      $oQuery->bindParam('file', $sFile, PDO::PARAM_STR);
      $oQuery->bindParam('extension', $sExtension, PDO::PARAM_STR);
      $oQuery->bindParam('position', $iPosition, PDO::PARAM_INT);

      $sContent = trim($this->_aRequest[$this->_sController]['content']);
      $iDate    = time();

      if (!ACTIVE_TEST && class_exists('\ImageMetadataParser') && \ImageMetadataParser::exifAvailable()) {
        $sLongFilename = PATH_UPLOAD . '/galleries/' . $this->_aRequest['id'] . '/original/' . $sFile;

        $oImageMetadataParser = new \ImageMetadataParser($sLongFilename);
        $oImageMetadataParser->parseExif();
        $oImageMetadataParser->parseIPTC();

        # update the image title, if it has one storred in exif or iptc
        if ($sContent != '' && $oImageMetadataParser->hasTitle())
          $sContent = $oImageMetadataParser->getTitle();

        # update the image date, if the exif date can be parsed
        if ($oImageMetadataParser->hasDateTime())
          $iDate = $oImageMetadataParser->getDateTime();
      }

      $sContent = Helper::formatInput($sContent, false);
      $sDate    = date('Y-m-d H:i:s', $iDate);
      $oQuery->bindParam('content', $sContent, PDO::PARAM_STR);
      $oQuery->bindParam('date', $sDate, PDO::PARAM_STR);

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
   * Update a file.
   *
   * @access public
   * @param integer $iId file ID
   * @return array|boolean array on success, boolean on false
   *
   */
  public function updateFile($iId) {
    if (empty($iId) || $iId < 1)
      return false;

    try {
      $oQuery = $this->_oDb->prepare("UPDATE
                                        " . SQL_PREFIX . "gallery_files
                                      SET
                                        content = :content
                                      WHERE
                                        id = :id");

      $oQuery->bindParam('id', $iId, PDO::PARAM_INT);

      foreach (array('content') as $sInput) {
        $sValue = Helper::formatInput($this->_aRequest[$this->_sController][$sInput], false);
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
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
      exit('SQL error.');
    }

    if ($bReturn) {
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

  /**
   * Update filepositions.
   *
   * @access public
   * @param integer $iId album ID
   * @return array|boolean array on success, boolean on false
   *
   */
  public function updateOrder($iId) {
    if (empty($iId) || $iId < 1)
      return false;

    $sSQL     = '';

    foreach ($this->_aRequest['file'] as $iKey => $iValue) {
      $iKey   = (int) $iKey;
      $iValue = (int) $iValue;

      $sSQL .= "UPDATE
                  " . SQL_PREFIX . "gallery_files
                SET
                  position = '" . $iKey . "'
                WHERE
                  id = '" . $iValue . "'
                AND
                  album_id = '" . $iId . "'
                LIMIT
                  1;";
    }

    try {
      $oQuery = $this->_oDb->prepare($sSQL);
      return $oQuery->execute();
    }
    catch (\PDOException $p) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $p->getMessage());
    }
  }

  /**
   * Get search information. We must overwrite the Main.model function to to different table syntax.
   *
   * @access public
   * @param string $sSearch query string to search
   * @param string $sController controller to use
   * @param string $sOrderBy how to order search
   * @return array $this->_aData search data
   *
   */
  public function search($sSearch, $sController = '', $sOrderBy = 't.date DESC') {
    $this->_aData = parent::search($sSearch, 'gallery_albums', $sOrderBy);

    $this->_aData['controller'] = $sController;
    $this->_aData['title'] = I18n::get('global.' . strtolower($sController));

    return $this->_aData;
  }
}