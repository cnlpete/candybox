<?php

/**
 * CRUD action of content entries.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 *
 */

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;

class Contents extends Main {

  /**
   * Show content page.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    $sTemplateDir  = Helper::getTemplateDir($this->_sController, 'show');
    $sTemplateFile = Helper::getTemplateType($sTemplateDir, 'show');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    $aData = $this->_oModel->getId($this->_iId);

    if (!isset($aData) || !$aData['id'])
      return Helper::redirectTo('/errors/404');

    $this->setDescription($aData['teaser']);
    $this->setKeywords($aData['keywords']);
    $this->setTitle($this->_removeHighlight($aData['title']));

    $this->oSmarty->assign('contents', $aData);

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Show content overview.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _overview() {
    $sTemplateDir  = Helper::getTemplateDir($this->_sController, 'overview');
    $sTemplateFile = Helper::getTemplateType($sTemplateDir, 'overview');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    $this->setTitle(I18n::get('global.manager.content'));

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
      $this->oSmarty->assign('contents', $this->_oModel->getOverview());
      $this->oSmarty->assign('_pages_',
                $this->_oModel->oPagination->showPages('/' . $this->_sController));
    }

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Create a content entry.
   *
   * @access protected
   * @return string|boolean HTML content (string) or returned status of model action (boolean).
   *
   */
  protected function _create() {
    $this->_setError('content');

    return parent::_create(array('searches', 'sitemaps'));
  }

  /**
   * Update a content entry.
   *
   * @access protected
   * @return boolean status of model action
   *
   */
  protected function _update() {
    $this->_setError('content');

    return parent::_update(array('searches', 'sitemaps'));
  }

  /**
   * Destroy a content entry.
   *
   * @access protected
   * @return boolean status of model action
   *
   */
  protected function _destroy() {
    return parent::_destroy(array('searches', 'sitemaps'));
  }
}