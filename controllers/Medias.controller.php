<?php

/**
 * Upload and show media files.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 *
*/

namespace CandyCMS\Core\Controllers;

use CandyCMS\Core\Helpers\AdvancedException;
use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\I18n;
use CandyCMS\Core\Helpers\SmartySingleton;
use CandyCMS\Core\Helpers\Upload;

class Medias extends Main {

  /**
   * Upload media file.
   * We must override the main method due to a file upload.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _create() {
    $this->_setError('file');

    require_once PATH_STANDARD . '/vendor/candyCMS/core/helpers/Upload.helper.php';
    $oUpload = new Upload($this->_aRequest, $this->_aSession, $this->_aFile);

    $sFolder = isset($this->_aRequest['folder']) ?
            Helper::formatInput($this->_aRequest['folder']) :
            $this->_sController;
    try {
      if (!is_dir($sFolder))
        mkdir(Helper::removeSlash(PATH_UPLOAD . '/' . $sFolder, 0775));

      $aReturn = $oUpload->uploadFiles($sFolder);
    }
    catch (\Exception $e) {
      return Helper::errorMessage($e->getMessage(), '/' . $this->_sController . '/create');
    }

    $iCount   = count($aReturn);
    $bAllTrue = true;

    for ($iI = 0; $iI < $iCount; $iI++) {
      if ($aReturn[$iI] === false)
        $bAllTrue = false;
    }

    Logs::insert( $this->_sController,
                  $this->_aRequest['action'],
                  0,
                  $this->_aSession['user']['id'],
                  '', '', $bAllTrue);

    # Return to website
    if ($bAllTrue) {
      $this->oSmarty->clearCacheForController($this->_sController);

      return Helper::successMessage(I18n::get('success.file.upload'),
              '/' . $this->_sController, $this->_aFile);
    }
    else
      return Helper::errorMessage(I18n::get('error.file.upload'),
              '/' . $this->_sController, $this->_aFile);
  }

  /**
   * Build form template to create an upload.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _showFormTemplate() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'create');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'create');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    $this->setTitle(I18n::get('medias.title.create'));

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Show an Overview of all Files
   * Needs to be custom since we want a different user right
   *
   * @access public
   * @return type
   *
   */
  public function show() {
    $this->oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    return $this->_aSession['user']['role'] < 3 ?
            Helper::errorMessage(I18n::get('error.missing.permission'), '/') :
            $this->_show();
  }

  /**
   * Show media files overview.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _show() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    $this->setTitle(I18n::get('global.manager.media'));

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID))
      $this->oSmarty->assign('files', $this->_oModel->getOverview());

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * There is no update Action for the medias Controller
   *
   * @access public
   *
   */
  public function update() {
    AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->update()');
    return Helper::redirectTo('/errors/404');
  }
}