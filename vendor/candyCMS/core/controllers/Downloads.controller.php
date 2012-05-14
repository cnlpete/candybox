<?php

/**
 * This class provides an overview about available downloads, counts them and gives
 * administrators and moderators the option to upload and manage files.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace CandyCMS\Core\Controllers;

use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\I18n;
use CandyCMS\Core\Helpers\Upload;

class Downloads extends Main {

  /**
   * Download entry or show download overview (depends on a given ID or not).
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    if ($this->_iId) {
      $sFile = $this->_oModel->getFileName($this->_iId);

      # if file not found, redirect user to overview
      if (!$sFile)
        return Helper::redirectTo ('/errors/404');

      # Update download count
      $this->_oModel->updateDownloadCount($this->_iId);

      # Get mime type
      if(function_exists('finfo_open')) {
        $sMimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE),
                Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sController . '/' . $sFile));
        header('Content-type: ' . $sMimeType);
      }

      # Send file directly
      header('Content-Disposition: attachment; filename="' . $sFile . '"');
      exit(readfile(Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sController . '/' . $sFile)));
    }
    else {
      $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'show');
      $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');

      if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID))
        $this->oSmarty->assign('downloads', $this->_oModel->getData($this->_iId));

      $this->oSmarty->setTemplateDir($sTemplateDir);
      return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
    }
  }

  /**
   * Build form template to create or update a download entry.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _showFormTemplate() {
    $this->oSmarty->assign('_categories_', $this->_oModel->getTypeaheadData('downloads', 'category'));

    return parent::_showFormTemplate();
  }

  /**
   * Create a download entry.
   *
   * Check if required data is given or throw an error instead.
   * If data is given, activate the model, insert them into the database and redirect afterwards.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _create() {
    $this->_setError('title');
    $this->_setError('category');
    $this->_setError('file');

    if (isset($this->_aError))
      return $this->_showFormTemplate();

    else {
      require_once PATH_STANDARD . '/vendor/candyCMS/core/helpers/Upload.helper.php';

      # Set up upload helper and rename file to title
      $oUploadFile = new Upload($this->_aRequest,
                                $this->_aSession,
                                $this->_aFile,
                                Helper::formatInput($this->_aRequest[$this->_sController]['title']));

      # File is up so insert data into database
      $aReturnValues = $oUploadFile->uploadFiles($this->_sController);
      if ($aReturnValues[0] === true) {
        $this->oSmarty->clearCacheForController($this->_sController, 'searches');

        $aIds   = $oUploadFile->getIds(false);
        $aExts  = $oUploadFile->getExtensions();

        # Create file(s)
        if ($this->_oModel->create($aIds[0] . '.' . $aExts[0], $aExts[0]) === true) {
          Logs::insert( $this->_sController,
                        $this->_aRequest['action'],
                        $this->_oModel->getLastInsertId($this->_sController),
                        $this->_aSession['user']['id']);

          return Helper::successMessage(I18n::get('success.create'), '/' . $this->_sController);
        }
        else
          return Helper::errorMessage(I18n::get('error.sql'), '/' . $this->_sController);
      }
      else
        return Helper::errorMessage(I18n::get('error.missing.file'), '/' . $this->_sController);
    }
  }

  /**
   * Update a download entry.
   *
   * @access protected
   * @return boolean status of model action
   *
   */
  protected function _update() {
    return parent::_update('searches');
  }

  /**
   * Destroy a download entry.
   *
   * @access protected
   * @return boolean status of model action
   *
   */
  protected function _destroy() {
    return parent::_destroy('searches');
  }
}