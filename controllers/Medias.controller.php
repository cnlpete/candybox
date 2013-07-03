<?php

/**
 * Upload and show media files.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 1.0
 *
*/

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\SmartySingleton as Smarty;
use candyCMS\Core\Helpers\Upload;

class Medias extends Main {

  /**
   * Initialize the controller by adding input params, set default id and start template engine.
   *
   * Overwrite the main::__construct since we want to set a custom iId
   *
   * @access public
   * @param array $aRequest alias for the combination of $_GET and $_POST
   * @param array $aSession alias for $_SESSION
   * @param array $aFile alias for $_FILE
   * @param array $aCookie alias for $_COOKIE
   * @todo $_iId is not an integer. We should change that variable.
   *
   */
  public function __construct(&$aRequest, &$aSession, &$aFile = '', &$aCookie = '') {
    parent::__construct($aRequest, $aSession, $aFile, $aCookie);

    $this->_iId = isset($this->_aRequest['file']) ? $this->_aRequest['file'] : '';
  }

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

    require_once PATH_STANDARD . '/vendor/candycms/core/helpers/Upload.helper.php';
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
      return Helper::errorMessage($e->getMessage(),
              '/' . $this->_sController . '/create',
              $this->_aFile);
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
      $this->oSmarty->clearControllerCache($this->_sController);

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
   * @param string $sTemplateName name of form template (only for E_STRICT)
   * @param string $sTitle title to show (only for E_STRICT)
   * @return string HTML content
   *
   */
  protected function _showFormTemplate($sTemplateName = '_form', $sTitle = '') {
    # We don't support JSON
    # @todo put this into a seperated method
    if (isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'])
      return json_encode(array(
                  'success' => false,
                  'error'   => 'There is no JSON handling method called ' . __FUNCTION__ . ' for this controller.'
              ));

    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'create');
    $this->oSmarty->setTemplateDir($oTemplate);

    $this->setTitle(I18n::get('medias.title.create'));

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
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
    $this->oSmarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);

    return $this->_aSession['user']['role'] < 3 ?
            Helper::redirectTo('/errors/401') :
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
    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'show');
    $this->oSmarty->setTemplateDir($oTemplate);

    $this->setTitle(I18n::get('global.manager.media'));

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID))
      $this->oSmarty->assign('files', $this->_oModel->getOverview());

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }
}
