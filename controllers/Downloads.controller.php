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

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\Upload;

class Downloads extends Main {

  public function __init() {
    parent::__init();

    $this->_aDependentCaches[] = 'searches';

    return $this->_oModel;
  }

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

    # Get mime type
    if (function_exists('finfo_open'))
      header('Content-type: ' . finfo_file(
              finfo_open(FILEINFO_MIME_TYPE),
              Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sController . '/' . $sFile)));

    # Send file directly
    header('Content-Disposition: attachment; filename="' . $sFile . '"');
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
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'overview');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'overview');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID))
      $this->oSmarty->assign('downloads', $this->_oModel->getOverview());

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Build form template to create or update a download entry.
   *
   * @access protected
   * @param string $sTemplateName name of form template, only for E_STRICT
   * @param string $sTitle title to show, only for E_STRICT
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
   * @param string $sRedirectURL specify the URL to redirect to after execution, only for E_STRICT
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _create($sRedirectURL = '') {
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
        $this->oSmarty->clearCacheForController($this->_sController);
        # i do not need to check, since i now about the existance of additional searches cache
        $this->_clearAdditionalCaches();

        $aIds   = $oUploadFile->getIds(false);
        $aExts  = $oUploadFile->getExtensions();

        # File is up so insert data into database
        if ($this->_oModel->create($aIds[0] . '.' . $aExts[0], $aExts[0]) === true) {
          Logs::insert( $this->_sController,
                        $this->_aRequest['action'],
                        $this->_oModel->getLastInsertId($this->_sController),
                        $this->_aSession['user']['id']);

          return Helper::successMessage(I18n::get('success.create'),
                  '/' . $this->_sController,
                  $this->_aFile);
        }
        else
          return Helper::errorMessage(I18n::get('error.sql'),
                  '/' . $this->_sController,
                  $this->_aFile);
      }
      else
        return Helper::errorMessage(I18n::get('error.missing.file'),
                '/' . $this->_sController,
                $this->_aFile);
    }
  }

  /**
   * Update a download entry.
   *
   * @access protected
   * @param string $sRedirectURL specify the URL to redirect to after execution, only for E_STRICT
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _update($sRedirectURL = '') {
    return parent::_update('/' . $this->_sController);
  }

  /**
   * Destroy a download entry.
   *
   * @access protected
   * @param string $sRedirectURL specify the URL to redirect to after execution, only for E_STRICT
   * @return boolean status of model action
   *
   */
  protected function _destroy($sRedirectURL = '') {
    return parent::_destroy();
  }
}
