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

namespace CandyCMS\Core\Controllers;

use CandyCMS\Core\Helpers\AdvancedException;
use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\I18n;
use CandyCMS\Core\Helpers\Upload;
use CandyCMS\Core\Helpers\SmartySingleton;

class Galleries extends Main {

  /**
   * Route to right action.
   *
   * @access public
   * @return string HTML
   *
   */
  public function show() {
    switch ($this->_aRequest['action']) {
      case 'createfile':

        $this->setTitle(I18n::get('gallery.files.title.create'));
        return $this->createFile();

        break;

      case 'updatefile':

        $this->setTitle(I18n::get('gallery.files.title.update'));
        return $this->updateFile();

        break;

      case 'updatefilepositions':

        exit(json_encode($this->updateFilePositions()));

        break;

      case 'destroyfile':

        $this->setTitle(I18n::get('gallery.files.title.destroy'));
        return $this->destroyFile();

        break;

      default:
      case '':

        $this->oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);
        return $this->_show();

        break;
    }
  }

  /**
   * Show image, gallery album or overview (depends on a given ID and album_id).
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
		return	$this->_iId && !isset($this->_aRequest['album_id']) ?
						$this->_showAlbum() :
						$this->_showOverview();
	}

  /**
   * Show overview of albums.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _showOverview() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'albums');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'albums');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
      $this->oSmarty->assign('albums', $this->_oModel->getOverview());
      $this->oSmarty->assign('_pages_', $this->_oModel->oPagination->showPages('/' . $this->_sController));
    }

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Show overview of images in one album.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _showAlbum() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'files');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'files');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    # Collect data array
    $sAlbumData = $this->_oModel->getAlbumNameAndContent($this->_iId, $this->_aRequest);

    $this->setTitle($this->_removeHighlight($sAlbumData['title']) . ' - ' . I18n::get('global.gallery'));
    $this->setDescription($this->_removeHighlight($sAlbumData['content']));

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
      $aData = $this->_oModel->getThumbs($this->_iId);

      $this->oSmarty->assign('files', $aData);
      $this->oSmarty->assign('file_no', count($aData));
      $this->oSmarty->assign('gallery_name', $sAlbumData['title']);
      $this->oSmarty->assign('gallery_content', $sAlbumData['content']);
    }

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Build form template to create or update a gallery album.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _showFormTemplate() {
    return parent::_showFormTemplate('_form_album', 'galleries.albums.title');
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
        $this->oSmarty->clearCacheForController(array($this->_sController, 'searches', 'rss', 'sitemaps'));

        $iId    = $this->_oModel->getLastInsertId('gallery_albums');
        $sPath  = Helper::removeSlash(PATH_UPLOAD . '/' . $this->_sController . '/' . $iId);

        # Create missing thumb folders.
        foreach(array('32', 'thumbnail', 'popup', 'original') as $sFolder) {
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
   * @see vendor/candyCMS/core/helper/Image.helper.php
   *
   */
    protected function _showFormFileTemplate() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, '_form_file');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, '_form_file');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if ($this->_iId) {
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

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
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
    if ($this->_aSession['user']['role'] < 3)
      return Helper::errorMessage(I18n::get('error.missing.permission'));

    return isset($this->_aRequest[$this->_sController]) ?
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
    require_once PATH_STANDARD . '/vendor/candyCMS/core/helpers/Upload.helper.php';

    $this->_setError('cut');
    $this->_setError('file');

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
          $this->oSmarty->clearCacheForController($this->_sController);
          $this->oSmarty->clearCacheForController('rss');

          return Helper::successMessage(I18n::get('success.file.upload'), '/' . $this->_sController .
                          '/' . $this->_iId);
        }
        else
          return Helper::errorMessage(I18n::get('error.file.upload'), '/' . $this->_sController .
                        '/' . $this->_iId . '/createfile');
      }
      catch (AdvancedException $e) {
        AdvancedException::reportBoth($e->getMessage());
        return Helper::errorMessage($e->getMessage(), '/' . $this->_sController .
                      '/' . $this->_iId . '/createfile');
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
    if ($this->_aSession['user']['role'] < 3)
      return Helper::errorMessage(I18n::get('error.missing.permission'), '/' . $this->_sController);

    else
      return isset($this->_aRequest[$this->_sController]) ?
              $this->_updateFile() :
              $this->_showFormFileTemplate();
  }

  /**
   * Update the positions of all files of a gallery.
   *
   * Calls _updateFilePositions if data is given, otherwise returns false.
   *
   * @access public
   * @return boolean returned status of model action (boolean).
   *
   */
  public function updateFilePositions() {
    return $this->_aSession['user']['role'] < 3 ?
            false :
            $this->_updateFilePositions();
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
      $this->oSmarty->clearCacheForController($this->_sController);
      $this->oSmarty->clearCacheForController('rss');

      return Helper::successMessage(I18n::get('success.update'), $sRedirectPath);
    }
    else
      return Helper::errorMessage(I18n::get('error.sql'), $sRedirectPath);
  }

  /**
   * Update a gallery entry.
   *
   * Activate model, Update data in the database and redirect afterwards.
   *
   * @access protected
   * @return boolean status of action
   *
   */
  protected function _updateFilePositions() {
    if (!$this->_aRequest['galleryfiles'])
      return false;

    $bReturn = $this->_oModel->updateFilePositions($this->_iId) == true;

    Logs::insert( $this->_sController,
                  $this->_aRequest['action'],
                  (int) $this->_aRequest['id'],
                  $this->_aSession['user']['id'],
                  '', '', $bReturn);

    if ($bReturn) {
      $this->oSmarty->clearCacheForController($this->_sController);
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
    $aDetails = $this->_oModel->getFileData($this->_iId);

		return $this->_aSession['user']['role'] < 3 ?
						Helper::errorMessage(I18n::get('error.missing.permission'), '/' .
										$this->_sController . '/' . $aDetails['album_id']) :
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
      $this->oSmarty->clearCacheForController($this->_sController);
      $this->oSmarty->clearCacheForController('rss');

      unset($this->_iId);
      return Helper::successMessage(I18n::get('success.destroy'), '/' . $this->_sController . '/' .
              $aDetails['album_id']);
    }
    else
      return Helper::errorMessage(I18n::get('error.sql'), '/' . $this->_sController . '/' .
              $aDetails['album_id']);
}

  /**
   * Update an album.
   *
   * @access protected
   * @return boolean status of model action
   *
   */
  protected function _update() {
    return parent::_update(array('searches', 'rss', 'sitemaps'));
  }

  /**
   * Destroy an album.
   *
   * @access protected
   * @return boolean status of model action
   *
   */
  protected function _destroy() {
    return parent::_destroy(array('searches', 'rss', 'sitemaps'));
  }
}