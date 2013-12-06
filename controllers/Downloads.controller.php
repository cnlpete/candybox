<?php

/**
 * This class provides an overview about available downloads, counts them and gives
 * administrators and moderators the option to upload and manage files.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\Upload;

/**
 * Class Downloads
 * @package candyCMS\Core\Controllers
 *
 */
class Downloads extends Main {

  /**
   * Provide download.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    $sFile = $this->_oModel->getFileName($this->_iId);

    # If file not found, redirect user to overview
    if (!$sFile)
      return Helper::redirectTo ('/errors/404');

    # Update download count
    $this->_oModel->updateDownloadCount($this->_iId);

    if (!ACTIVE_TEST) {
      # Get mime type
      if (function_exists('finfo_open'))
        header('Content-type: ' . finfo_file(
                finfo_open(FILEINFO_MIME_TYPE),
                Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sController . '/' . $sFile)));

      # Send file directly
      header('Content-Disposition: attachment; filename="' . $sFile . '"');
    }

    exit(readfile(Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sController . '/' . $sFile)));
  }

  /**
   * Show download overview.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _overview() {
    $oTemplate =  $this->oSmarty->getTemplate($this->_sController, 'overview');
    $this->oSmarty->setTemplateDir($oTemplate);

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID))
      $this->oSmarty->assign('downloads', $this->_oModel->getOverview());

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Build form template to create or update a download entry.
   *
   * @access protected
   * @param string $sTemplateName name of form template (only for E_STRICT)
   * @param string $sTitle title to show (only for E_STRICT)
   * @return string HTML content
   *
   */
  protected function _showFormTemplate($sTemplateName = '_form', $sTitle = '') {
    $this->oSmarty->assign('_categories_', $this->_oModel->getTypeaheadData($this->_sController, 'category'));

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

    # Always redirect to overview
    $this->_sRedirectURL = '/' . $this->_sController;

    if (isset($this->_aError))
      return $this->_showFormTemplate();

    else {
      require_once PATH_STANDARD . '/vendor/candycms/core/helpers/Upload.helper.php';

      # Set up upload helper
      $oUploadFile = new Upload($this->_aRequest,
                                $this->_aSession,
                                $this->_aFile);

      try {
        $aReturnValues = $oUploadFile->uploadFiles('downloads');
      }
      catch (\Exception $e) {
        return Helper::errorMessage($e->getMessage(),
                '/' . $this->_sController . '/create',
                $this->_aFile);
      }

      # Fileupload was successfull, so we can clear cache and insert into db.
      if ($aReturnValues[0] === true) {
        $this->oSmarty->clearControllerCache($this->_sController);
        # i do not need to check, since i now about the existance of additional searches cache
        $this->_clearAdditionalCaches();

        $aIds   = $oUploadFile->getIds(false);
        $aExts  = $oUploadFile->getExtensions();

        # File is up so insert data into database
        $aOptions = array('file' => $aIds[0] . '.' . $aExts[0], 'extension' => $aExts[0]);

        if ($this->_oModel->create($aOptions) === true) {
          Logs::insert( $this->_sController,
                        $this->_aRequest['action'],
                        $this->_oModel->getLastInsertId($this->_sController),
                        $this->_aSession['user']['id']);

          return Helper::successMessage( I18n::get('success.create'),
                                         $this->_sRedirectURL,
                                         $this->_aFile);
        }
        else
          return Helper::errorMessage( I18n::get('error.sql'),
                                       $this->_sRedirectURL,
                                       $this->_aFile);
      }
      else
        return Helper::errorMessage( I18n::get('error.missing.file'),
                                     $this->_sRedirectURL,
                                     $this->_aFile);
    }
  }

  /**
   * Update a download entry.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _update() {
    # Redirect to overview, since show will prompt the download
    $this->_sRedirectURL = '/' . $this->_sController;

    return parent::_update();
  }
}