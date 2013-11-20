<?php

/**
 * Handle all uploads.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 1.0
 *
 */

namespace candyCMS\Core\Helpers;

use candyCMS\Core\Helpers\Helper;

class Upload {

  /**
   * file information
   *
   * @var array
   * @access private
   *
   */
  private $_aFile;

  /**
   * @var array
   * @access private
   *
   */
  private $_sFileExtensions = array();

  /**
   * names of the files
   *
   * @var array
   * @access private
   *
   */
  private $_sFileNames = array();

  /**
   * File path for each file.
   *
   * @var array
   * @access public
   *
   */
  public $aFilePaths = array();

  /**
   * Name of the current controller.
   *
   * @var string
   * @access protected
   */
  protected $_sController;

  /**
   * Fetch the required information.
   *
   * @access public
   * @param array $aRequest alias for the combination of $_GET and $_POST
   * @param array $aFile alias for $_FILE
   * @param string $sRename new file name
   *
   */
  public function __construct(&$aRequest, &$aSession, &$aFile) {
    $this->_aRequest  = & $aRequest;
    $this->_aSession  = & $aSession;
    $this->_aFile     = & $aFile;

    $this->_sController = $this->_aRequest['controller'];

    require_once PATH_STANDARD . '/vendor/candycms/core/helpers/Image.helper.php';
  }

  /**
   * Get the maximum upload Limit allowed by php.ini.
   *
   * @static
   * @access public
   * @param boolean $bToBytes convert to bytes?
   * @return integer the upload limit in bytes
   *
   */
  public static function getUploadLimit($bToBytes = true) {
    $iMaxUpload   = (int)(ini_get('upload_max_filesize'));
    $iMaxPost     = (int)(ini_get('post_max_size'));
    $iMemoryLimit = (int)(ini_get('memory_limit'));

    # 1024 * 1024 = 1048576
    return (int) min($iMaxUpload, $iMaxPost, $iMemoryLimit) * ($bToBytes ? 1048576 : 1);
  }

  /**
   * Get the filesize of the about to be uploaded files.
   *
   * @static
   * @access public
   * @return integer the total file size
   *
   */
  public function getFileSize() {
    $sType = isset($this->_aFile['image']) ? 'image' : 'file';

    $iFileSize = 0;
    if (is_array($this->_aFile[$sType]['name']))
      foreach ($this->_aFile[$sType]['size'] as $iSize)
        $iFileSize += $iSize;
    else
      $iFileSize = $this->_aFile[$sType]['size'];

    return (int) $iFileSize > 0 ? $iFileSize : (self::getUploadLimit() + 1);
  }

  /**
   * Return file mime type for AJAX uploading purposes.
   *
   * @access public
   * @return array file information
   * @todo test
   *
   */
  public function getFileMimeType() {
    $sType = isset($this->_aFile['image']) ? 'image' : 'file';
    return $this->_aFile[$sType]['type'][0];
  }

  /**
   * Rename files (if chosen) and upload them afterwards to predefined folder.
   *
   * @access public
   * @param string $sFolder name of upload folder
   * @param boolean $bFilenameHashes whether a hash should be used as the filename
   * @see vendor/candycms/core/controllers/Medias.controller.php
   * @return array(boolean) status of uploads.
   *
   */
  public function uploadFiles($sFolder = 'medias', $bFilenameHashes = false) {
    $sType = isset($this->_aFile['image']) ? 'image' : 'file';

    if (isset($this->_aFile[$sType]) && !empty($this->_aFile[$sType]['name'][0])) {
      $bIsArray   = is_array($this->_aFile[$sType]['name']);
      $iFileCount = $bIsArray ? count($this->_aFile[$sType]['name']) : 1;

      # Stores the total size of files to upload in bytes
      if ($this->getFileSize() > self::getUploadLimit()) {
        throw new AdvancedException(I18n::get('error.file.size', self::getUploadLimit(false) . 'MB'));
      }

      $bReturn = array();
      for ($iI = 0; $iI < $iFileCount; $iI++) {
        $sFileName = $bIsArray ? $this->_aFile[$sType]['name'][$iI] : $this->_aFile[$sType]['name'];
        $sFileName = strtolower($sFileName);

        $this->_sFileNames[$iI] = Helper::replaceNonAlphachars($sFileName);
        $this->_sFileExtensions[$iI] = substr(strrchr($sFileName, '.'), 1);

        # Remove extension, if there is one
        $iPos = strrpos($this->_sFileNames[$iI], '.');
        if ($iPos)
          $this->_sFileNames[$iI] = substr($this->_sFileNames[$iI], 0, $iPos);

        # Rename the file, if a new name is specified
        if (isset($this->_aRequest[$this->_sController]['rename']) && !empty($this->_aRequest[$this->_sController]['rename']))
          $this->_sFileNames[$iI] = Helper::replaceNonAlphachars($this->_aRequest[$this->_sController]['rename']) .
                  ($iFileCount == 1 ? '' : '_' . $iI);

        # Generate hash, if wanted
        if ($bFilenameHashes)
          $this->_sFileNames[$iI] = md5($this->_sFileNames[$iI] . rand(000, 999));

        # Generate the new filename with its full path
        $this->aFilePaths[$iI] = Helper::removeSlash(PATH_UPLOAD . '/' .  $sFolder . '/' .
                                                    $this->_sFileNames[$iI] . '.' . $this->_sFileExtensions[$iI]);

        # Upload the file
        $sTempFileName  = $bIsArray ? $this->_aFile[$sType]['tmp_name'][$iI] : $this->_aFile[$sType]['tmp_name'];
        $bReturn[$iI]   = move_uploaded_file($sTempFileName, $this->aFilePaths[$iI]) ? true : false;
      }

      return $bReturn;
    }
  }

  /**
   * Upload files into an album. Resize and / or cut the files.
   *
   * @access public
   * @param string $sFolder folder that we want to use
   * @param string $sResize cut or resize the images?!
   * @return array boolean status of each upload
   * @see vendor/candycms/core/controllers/Galleries.controller.php
   *
   */
  public function uploadGalleryFiles($sFolder = 'galleries', $sResize = '') {
    $this->_aRequest[$this->_sController]['cut'] = !empty($sResize) ?
            $sResize :
            $this->_aRequest[$this->_sController]['cut'];

    if ($this->getFileSize() > self::getUploadLimit())
      throw new AdvancedException(I18n::get('error.file.size', self::getUploadLimit(false) . 'MB'));

    else {
      $sUploadFolder = $sFolder . '/' . (int) $this->_aRequest['id'];
      $aUploads = $this->uploadFiles($sUploadFolder . '/original', true);

      # Do cuts and or resizes
      $iFileCount = count($aUploads);
      for ($iI = 0; $iI < $iFileCount; $iI++) {
        if ($aUploads[$iI] === true) {
          $oImage = new Image($this->_sFileNames[$iI],
                  $sUploadFolder,
                  $this->aFilePaths[$iI],
                  $this->_sFileExtensions[$iI]);

          if (isset($this->_aRequest[$this->_sController]['cut']) && 'c' == $this->_aRequest[$this->_sController]['cut'])
            $oImage->resizeAndCut(THUMB_DEFAULT_X, 'thumbnail');

          elseif (isset($this->_aRequest[$this->_sController]['cut']) && 'r' == $this->_aRequest[$this->_sController]['cut'])
            $oImage->resizeDefault(THUMB_DEFAULT_X, THUMB_DEFAULT_Y, 'thumbnail');

          else
            throw new AdvancedException('No resizing information!');

          $oImage->resizeDefault(POPUP_DEFAULT_X, POPUP_DEFAULT_Y, 'popup');
          $oImage->resizeAndCut('32');
        }
      }

      return $aUploads;
    }
  }

  /**
   * Upload a user avatar.
   *
   * @access public
   * @param boolean $bReturnPath return path information?!
   * @see vendor/candycms/core/controllers/Users.controller.php
   * @return string|boolean user avatar path or boolean status of upload.
   *
   */
  public function uploadAvatarFile($bReturnPath = true) {
    $this->_aRequest[$this->_sController]['rename'] = isset($this->_aRequest['id']) && $this->_aSession['user']['role'] == 4 ?
            (int) $this->_aRequest['id'] :
            $this->_aSession['user']['id'];

    if ($this->getFileSize() > self::getUploadLimit())
      throw new \Exception(I18n::get('error.file.size', self::getUploadLimit(false) . 'MB'));

    else {
      $this->destroyAvatarFiles($this->_aRequest[$this->_sController]['rename']);

      $sUploadFolder = 'users';
      $aUploads = $this->uploadFiles($sUploadFolder . '/original');

      # upload might have failed
      if ($aUploads[0] === false)
        return false;

      # upload was successfull
      $oImage = new Image($this->_sFileNames[0], $sUploadFolder, $this->aFilePaths[0], $this->_sFileExtensions[0]);

      $oImage->resizeDefault(POPUP_DEFAULT_X, POPUP_DEFAULT_Y, 'popup');
      $oImage->resizeDefault(THUMB_DEFAULT_X, THUMB_DEFAULT_Y, 'thumbnail');
      $oImage->resizeDefault(100);
      $oImage->resizeAndCut(64);
      $oImage->resizeAndCut(32);

      return $bReturnPath ? $this->aFilePaths[0] : $aUploads[0];
    }
  }

  /**
   * Delete user avatars.
   *
   * @static
   * @access public
   * @param string $sFileName name of the file
   *
   */
  public static function destroyAvatarFiles($sFileName) {
    $aFileTypes = array('jpg', 'png', 'gif');
    $aFolders   = array('original', 'popup', 'thumbnail', '100', '64', '32');

    foreach ($aFileTypes as &$sExtension) {
      foreach ($aFolders as &$sFolder) {
        if (is_file(Helper::removeSlash(PATH_UPLOAD . '/users/' . $sFolder . '/' . $sFileName . '.' . $sExtension)))
          unlink(Helper::removeSlash(PATH_UPLOAD . '/users/' . $sFolder . '/' . $sFileName . '.' . $sExtension));
      }
    }
  }

  /**
   * Return the files extensions.
   *
   * @return string $this->_sFileExtension file extension.
   *
   */
  public function getExtensions() {
    return $this->_sFileExtensions;
  }

  /**
   * Return the current file.
   *
   * @param boolean $bWithExtension
   * @return array with all filenames
   *
   */
  public function getIds($bWithExtension = true) {
    for ($iI = 0; $iI < count($this->_sFileNames); $iI++)
      if ($bWithExtension)
        $aReturn[$iI] = $this->_sFileNames[$iI] . '.' . $this->_sFileExtensions[$iI];

      else
        $aReturn[$iI] = $this->_sFileNames[$iI];

    return isset($aReturn) ? $aReturn : array();
  }
}
