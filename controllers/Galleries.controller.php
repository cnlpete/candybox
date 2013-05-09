<?php

/**
 * CRUD actions for gallery overview and gallery albums.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 *
 */

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\Upload;
use candyCMS\Core\Helpers\SmartySingleton as Smarty;

class Galleries extends Main {

  /**
   * Show image, gallery album or overview (depends on a given ID and album_id).
   *
   * @access public
   * @return string HTML content
   *
   */
  public function show() {
    $sType    = isset($this->_aRequest['type']) ? strtoupper($this->_aRequest['type']) : '';
    $sMethod  = $this->_iId && !isset($this->_aRequest['album_id']) ?
            '_show' . $sType :
            '_overview' . $sType;

    return $this->$sMethod();
  }

  /**
   * Show overview of albums.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _overview() {
    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'overview');
    $this->oSmarty->setTemplateDir($oTemplate);

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
      $this->oSmarty->assign('albums', $this->_oModel->getOverview());

      # Limit to maximum pages
      if (isset($this->_aRequest['page']) && (int) $this->_aRequest['page'] > $this->_oModel->oPagination->getPages())
        return Helper::redirectTo('/errors/404');

      else
        $this->oSmarty->assign('_pagination_', $this->_oModel->oPagination->showPages('/' . $this->_sController));
    }

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Show overview of images in one album.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'show');
    $this->oSmarty->setTemplateDir($oTemplate);

    # Collect data array
    $aData = $this->_oModel->getId($this->_iId, false, true);

    $sTitle = $this->_removeHighlight($aData['title']) . ' - ' . I18n::get('global.gallery');
    $this->setTitle($sTitle);
    $this->setDescription($this->_removeHighlight($aData['content']));

    # add rss info
    $this->_aRSSInfo[] = array(
                            'url' => WEBSITE_URL . '/galleries/' . $this->_aRequest['id'] . '.rss',
                            'title' => $sTitle);

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID))
      $this->oSmarty->assign('album', $aData);

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Show gallery album as RSS.
   *
   * @access protected
   * @return string XML
   *
   */
  protected function _showRSS() {
    if (!$this->_iId)
      Helper::redirectTo('/errors/404');

    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'showRSS');
    $this->oSmarty->setTemplateDir($oTemplate);

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
      $aData = $this->_oModel->getId($this->_iId, false, true);
      $this->oSmarty->assign('data', $aData);
    }

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Build form template to create or update a gallery album.
   *
   * @access protected
   * @param string $sTemplateName name of form template
   * @param string $sTitle title to show
   * @return string HTML content
   *
   */
  protected function _showFormTemplate($sTemplateName = '_form_overview', $sTitle = 'galleries.albums.title') {
    return parent::_showFormTemplate($sTemplateName, $sTitle);
  }

  /**
   * Create a gallery album.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _create() {
    $this->_setError('title');

    if ($this->_aError)
      return $this->_showFormTemplate();

    else {
      if ($this->_oModel->create() === true) {
        $this->oSmarty->clearControllerCache($this->_sController);
        $this->_clearAdditionalCaches();

        $iId    = $this->_oModel->getLastInsertId('gallery_albums');
        $sPath  = Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sController . '/' . $iId);

        # Create missing thumb folders.
        foreach (array('32', 'thumbnail', 'popup', 'original') as $sFolder) {
          if (!is_dir($sPath . '/' . $sFolder))
            mkdir($sPath . '/' . $sFolder, 0755, true);
        }

        # Bugfix: Logs must be down here, because $iId isn't set otherwise.
        Logs::insert( $this->_sController,
                      $this->_aRequest['action'],
                      $iId,
                      $this->_aSession['user']['id'],
                      '',
                      '',
                      1);

        return Helper::successMessage(I18n::get('success.create'), '/' . $this->_sController . '/' . $iId);
      }
      else
        return Helper::errorMessage(I18n::get('error.sql'), '/' . $this->_sController);
    }
  }

  /**
   * Build form template to upload or update a file.
   * NOTE: We need to get the request action because we already have an gallery album ID.
   *
   * @access protected
   * @return string HTML content
   * @see vendor/candycms/core/helper/Image.helper.php
   *
   */
  protected function _showFormFileTemplate() {
    $oTemplate = $this->oSmarty->getTemplate($this->_sController, '_form_show');
    $this->oSmarty->setTemplateDir($oTemplate);

    if ($this->_iId && $this->_aRequest['action'] == 'updatefile') {
      $aData = $this->_oModel->getFileData($this->_iId);

      foreach ($aData as $sColumn => $sData)
        $this->oSmarty->assign($sColumn, $sData);

      $this->setTitle(vsprintf(I18n::get($this->_sController . '.files.title.update'), $aData['title']));
    }

    else {
      foreach ($this->_aRequest[$this->_sController] as $sInput => $sData)
        $this->oSmarty->assign($sInput, $sData);

      $this->setTitle(I18n::get($this->_sController . '.title.create'));
    }

    if ($this->_aError)
      $this->oSmarty->assign('error', $this->_aError);

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Create a gallery entry.
   *
   * Create entry or show form template if we have enough rights.
   *
   * @access public
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  public function createFile() {
    $this->setTitle(I18n::get('galleries.files.title.create'));

    if ($this->_aSession['user']['role'] < 3)
      return Helper::redirectTo('/errors/401');

    return isset($this->_aRequest[$this->_sController]) ||
            isset($this->_aRequest['type']) && 'json' == $this->_aRequest['type'] ?
            $this->_createFile() :
            $this->_showFormFileTemplate();
  }

  /**
   * Create a gallery entry.
   *
   * Check if required data is given or throw an error instead.
   * If data is given, upload each selected file, insert them into the database and redirect afterwards.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _createFile() {
    $this->_setError('cut');
    $this->_setError('file');

    require_once PATH_STANDARD . '/vendor/candycms/core/helpers/Upload.helper.php';

    if ($this->_aError)
      return $this->_showFormFileTemplate();

    else {
      $oUploadFile = new Upload($this->_aRequest, $this->_aSession, $this->_aFile);

      try {
        $aReturnValues = $oUploadFile->uploadGalleryFiles();

        $aIds   = $oUploadFile->getIds(false);
        $aExts  = $oUploadFile->getExtensions();

        $iFileCount = count($aReturnValues);
        $bReturnValue = true;

        for ($iI = 0; $iI < $iFileCount; $iI++)
          $bReturnValue = $aReturnValues[$iI] === true ?
                  $bReturnValue && $this->_oModel->createFile($aIds[$iI] . '.' . $aExts[$iI], $aExts[$iI]) :
                  false;

        # Log uploaded image. Request ID = album id
        Logs::insert( $this->_sController,
                      'createfile',
                      (int) $this->_aRequest[$this->_sController]['id'],
                      $this->_aSession['user']['id'],
                      '', '', $bReturnValue);

        if ($bReturnValue) {
          $this->oSmarty->clearControllerCache($this->_sController);
          $this->oSmarty->clearControllerCache('rss');

          return Helper::successMessage(I18n::get('success.file.upload'),
                  '/' . $this->_sController . '/' . $this->_iId,
                  $this->_aRequest);
        }
        else
          return Helper::errorMessage(I18n::get('error.file.upload'),
                  '/' . $this->_sController . '/' . $this->_iId . '/createfile',
                  $this->_aRequest);
      }
      catch (AdvancedException $e) {
        AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
        return Helper::errorMessage($e->getMessage(),
                '/' . $this->_sController . '/' . $this->_iId . '/createfile',
                $this->_aRequest);
      }
    }
  }

  /**
   * Update a gallery entry.
   *
   * Calls _updateFile if data is given, otherwise calls _showFormFileTemplate()
   *
   * @access public
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  public function updateFile() {
    $this->setTitle(I18n::get('galleries.files.title.update'));

    if ($this->_aSession['user']['role'] < 3)
      return Helper::redirectTo('/errors/401');

    else
      return isset($this->_aRequest[$this->_sController]) ?
              $this->_updateFile() :
              $this->_showFormFileTemplate();
  }

  /**
   * Update a gallery entry.
   *
   * Activate model, Update data in the database and redirect afterwards.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _updateFile() {
    if ($this->_aError)
      return $this->_showFormFileTemplate();

    $aDetails = $this->_oModel->getFileData($this->_iId);
    $sRedirectPath = '/' . $this->_sController . '/' . $aDetails['album_id'];

    $bReturn = $this->_oModel->updateFile($this->_iId) === true;

    Logs::insert( $this->_sController,
                  $this->_aRequest['action'],
                  (int) $this->_aRequest['id'],
                  $this->_aSession['user']['id'],
                  '', '', $bReturn);

    if ($bReturn) {
      $this->oSmarty->clearControllerCache($this->_sController);
      $this->oSmarty->clearControllerCache('rss');

      return Helper::successMessage(I18n::get('success.update'), $sRedirectPath);
    }
    else
      return Helper::errorMessage(I18n::get('error.sql'), $sRedirectPath);
  }

  /**
   * Update the positions of all files of a gallery.
   *
   * Calls _updateOrder if data is given, otherwise returns false.
   *
   * @access public
   * @return boolean returned status of model action (boolean).
   * @todo test
   *
   */
  public function updateOrder() {
    header('Content-Type: application/json');
    return $this->_aSession['user']['role'] < 3 ?
            json_encode(array(
                'success' => false,
                'error'   => '',
                'data'    => ''
            )) :
            json_encode(array(
                'success' => true,
                'error'   => '',
                'data'    => $this->_updateOrder()
            ));
  }

  /**
   * Update a gallery entry.
   *
   * Activate model, update data in the database and redirect afterwards.
   *
   * @access protected
   * @return boolean status of action
   *
   */
  protected function _updateOrder() {
    if (!$this->_aRequest['file'])
      return false;

    $bReturn = $this->_oModel->updateOrder($this->_iId) == true;

    Logs::insert( $this->_sController,
                  $this->_aRequest['action'],
                  (int) $this->_aRequest['id'],
                  $this->_aSession['user']['id'],
                  '', '', $bReturn);

    if ($bReturn) {
      $this->oSmarty->clearControllerCache($this->_sController);
      return true;
    }
    else
      return false;
  }

  /**
   * Destroy a gallery entry.
   *
   * @access public
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  public function destroyFile() {
    return $this->_aSession['user']['role'] < 3 ?
            Helper::redirectTo('/errors/401') :
            $this->_destroyFile();
  }

  /**
   * Destroy a gallery entry.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _destroyFile() {
    $aDetails = $this->_oModel->getFileData($this->_iId);
    $bReturn  = $this->_oModel->destroyFile($this->_iId) === true;

    Logs::insert( $this->_sController,
                  $this->_aRequest['action'],
                  (int) $this->_iId,
                  $this->_aSession['user']['id'],
                  '', '', $bReturn);

    if ($bReturn) {
      $this->oSmarty->clearControllerCache($this->_sController);
      $this->oSmarty->clearControllerCache('rss');

      unset($this->_iId);
      return Helper::successMessage(I18n::get('success.destroy'), '/' . $this->_sController . '/' .
              $aDetails['album_id']);
    }
    else
      return Helper::errorMessage(I18n::get('error.sql'), '/' . $this->_sController . '/' .
              $aDetails['album_id']);
  }
}
