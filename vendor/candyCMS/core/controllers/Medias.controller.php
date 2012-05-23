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

use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\I18n;
use CandyCMS\Core\Helpers\SmartySingleton;

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
    if (isset($this->_aRequest[$this->_sController])) {
      $bReturn  = $this->_oModel->create();

      Logs::insert( $this->_sController,
                    $this->_aRequest['action'],
                    $this->_oModel->getLastInsertId($this->_sController),
                    $this->_aSession['user']['id'],
                    '', '', $bReturn);

      if ($bReturn) {
        # Clear the cache
        $this->oSmarty->clearCacheForController($this->_sController);

        Helper::successMessage(I18n::get('success.file.upload'), '/' . $this->_sController);
      }
      else
        Helper::errorMessage(I18n::get('error.file.upload'), '/' . $this->_sController);

    }
    else
      return $this->_showFormTemplate();
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
    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Show an Overview of all Files
   * Needs to be custom since we want a different user right
   *
   * @return type
   */
  public function show() {
    $this->oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    if ($this->_aSession['user']['role'] < 3)
      return Helper::errorMessage(I18n::get('error.missing.permission'), '/');

    else
      return $this->_show();
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

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
      $this->oSmarty->assign('files', $this->_oModel->getOverview());
    }

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * There is no update Action for the medias Controller
   *
   * @access public
   *
   */
  public function update() {
    return Helper::redirectTo('/errors/404');
  }
}