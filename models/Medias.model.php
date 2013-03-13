<?php

/**
 * Handle all medias model requests.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade
 * @license MIT
 * @since 2.1
 *
 */

namespace candyCMS\Core\Models;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\Image;

class Medias extends Main {

  /**
   * Get log overview data.
   *
   * @access public
   * @return array $this->_aData
   *
   */
  public function getOverview() {
    require_once PATH_STANDARD . '/vendor/candyCMS/core/helpers/Image.helper.php';

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
        'size'  => Helper::fileSizeToString(Helper::getFileSize($sPath)),
        'type'  => $sFileType,
        'dim'   => $aImgDim,
        'url'   => Helper::addSlash(PATH_UPLOAD) . '/' . $this->_sController . '/' . $sFile,
        'url_destroy' => '/' . $this->_sController . '/' . $sFile . '/destroy'
      );
    }

    closedir($oDir);

    return $aFiles;
  }

  /**
   * Destroy a file and delete from HDD.
   *
   * @access public
   * @param mixed $sId the unique name of the file
   * @param string $sController controller to use, obsolete and only for not giving E_STRICT warnings
   * @return boolean status of query
   *
   */
  public function destroy($sId, $sController = '') {
    # We get the image information via GET
    $sPath = Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sController . '/' . $sId);

    if (ACTIVE_TEST)
      return true;

    elseif (is_file($sPath))
      return unlink($sPath);
  }
}