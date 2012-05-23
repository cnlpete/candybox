<?php

/**
 * Handle all medias model requests.
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
use CandyCMS\Core\Helpers\Image;
use CandyCMS\Core\Helpers\Upload;

class Medias extends Main {

  /**
   * Return ID of last inserted file.
   *
   * @var string
   * @access public
   *
   */
  static $sLastInsertId;

  /**
   * Get log overview data.
   *
   * @access public
   * @return array $this->_aData
   *
   */
  public function getOverview() {
    require PATH_STANDARD . '/vendor/candyCMS/core/helpers/Image.helper.php';

    $sOriginalPath = Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sController);
    $oDir = opendir($sOriginalPath);

    $aFiles = array();
    while ($sFile = readdir($oDir)) {
      $sPath = $sOriginalPath . '/' . $sFile;

      if (substr($sFile, 0, 1) == '.' || is_dir($sPath))
        continue;

      $sFileType  = strtolower(substr(strrchr($sPath, '.'), 1));
      $iNameLen   = strlen($sFile) - 4;

      if ($sFileType == 'jpeg')
        $iNameLen--;

      $sFileName = substr($sFile, 0, $iNameLen);

      if ($sFileType == 'jpg' || $sFileType == 'jpeg' || $sFileType == 'png' || $sFileType == 'gif') {
        $aImgDim = getImageSize($sPath);

        if (!file_exists(Helper::removeSlash(PATH_UPLOAD . '/temp/' . $this->_sController . '/' . $sFile))) {
          $oImage = new Image($sFileName, 'temp', $sPath, $sFileType);
          $oImage->resizeAndCut('32', $this->_sController);
        }
      }

      else
        $aImgDim = '';

      $aFiles[] = array(
        'name'  => $sFile,
        'date'  => Array(
            'raw' => filectime($sPath),
            'w3c' => date('Y-m-d\TH:i:sP', filectime($sPath))),
        'size'  => Helper::getFileSize($sPath),
        'type'  => $sFileType,
        'dim'   => $aImgDim,
        'url_destroy' => '/' . $this->_sController . '/' . $sFile . '/destroy'
      );
    }

    closedir($oDir);

    return $aFiles;
  }

  /**
   * Create a new medias entry.
   *
   * @access public
   * @return array status of action
   *
   */
  public function create() {
    require_once PATH_STANDARD . '/vendor/candyCMS/core/helpers/Upload.helper.php';

    $oUpload = new Upload($this->_aRequest, $this->_aSession, $this->_aFile);
    $sFolder = isset($this->_aRequest['folder']) ?
            Helper::formatInput($this->_aRequest['folder']) :
            $this->_sController;

    if (!is_dir($sFolder))
      mkdir(Helper::removeSlash(PATH_UPLOAD . '/' . $sFolder, 0777));

    $aReturn = $oUpload->uploadFiles($sFolder);
    $iCount   = count($aReturn);
    $bAllTrue = true;

    for ($iI = 0; $iI < $iCount; $iI++) {
      if ($aReturn[$iI] === false)
        $bAllTrue = false;
    }

    if ($bAllTrue) {
      self::$sLastInsertId = $oUpload->getIds();
      self::$sLastInsertId = self::$sLastInsertId[0];
    }
    return $bAllTrue;
  }

  /**
   * Destroy a file and delete from HDD.
   *
   * @access public
   * @return boolean status of query
   *
   */
  public function destroy() {
    $sPath = Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sController . '/' . $this->_aRequest['file']);

    if (is_file($sPath))
      return unlink($sPath);
  }

  /**
   * Return last inserted ID.
   *
   * @static
   * @access public
   * @return string self::$sLastInsertId last inserted ID.
   *
   */
  public static function getLastInsertId() {
    return self::$sLastInsertId;
  }
}
