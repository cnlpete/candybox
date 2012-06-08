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

namespace CandyCMS\Core\Controllers;

use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\I18n;

class Contents extends Main {

  /**
   * Show content entry or content overview (depends on a given ID or not).
   *
   * Caching is diabled due to needed title, keyword and description information.
   *
   * @access protected
   * @return string HTML content
   * @see /vendor/candyCMS/core/controllers/Main.controller.php for setTitle modifications.
   *
   */
  protected function _show() {
    if ($this->_iId) {
      $sTemplateDir  = Helper::getTemplateDir($this->_sController, 'show');
      $sTemplateFile = Helper::getTemplateType($sTemplateDir, 'show');
      $this->oSmarty->setTemplateDir($sTemplateDir);

      $aData = $this->_oModel->getId($this->_iId);

      if (!isset($aData) || !$aData[$this->_iId]['id'])
        return Helper::redirectTo('/errors/404');

      $this->setDescription($aData[$this->_iId]['teaser']);
      $this->setKeywords($aData[$this->_iId]['keywords']);
      $this->setTitle($this->_removeHighlight($aData[$this->_iId]['title']));

      $this->oSmarty->assign('contents', $aData);

      return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
    }
    else {
      $sTemplateDir  = Helper::getTemplateDir($this->_sController, 'overview');
      $sTemplateFile = Helper::getTemplateType($sTemplateDir, 'overview');
      $this->oSmarty->setTemplateDir($sTemplateDir);

      $this->setTitle(I18n::get('global.manager.content'));

      $this->oSmarty->assign('contents', $this->_oModel->getOverview());
        $this->oSmarty->assign('_pages_',
                  $this->_oModel->oPagination->showPages('/' . $this->_sController));

      return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
    }
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