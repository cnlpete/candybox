<?php

/**
 * CRUD action of content entries.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
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
    $oTemplate =  $this->oSmarty->getTemplate($this->_sController, 'show');
    $this->oSmarty->setTemplateDir($oTemplate);

    $aData = $this->_oModel->getId($this->_iId);

    # Entry does not exist or is unpublished
    if (!isset($aData) || !$aData['id'])
      return Helper::redirectTo('/errors/404');

    $this->setDescription($aData['teaser']);
    $this->setKeywords($aData['keywords']);
    $this->setTitle($this->_removeHighlight($aData['title']));

    $this->oSmarty->assign('contents', $aData);

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Show content overview.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _overview() {
    $oTemplate =  $this->oSmarty->getTemplate($this->_sController, 'overview');
    $this->oSmarty->setTemplateDir($oTemplate);

    $this->setTitle(I18n::get('global.manager.content'));

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
      $this->oSmarty->assign('contents', $this->_oModel->getOverview());
      $this->oSmarty->assign('_pagination_',
                $this->_oModel->oPagination->showPages('/' . $this->_sController));
    }

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
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

    return parent::_create();
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

    return parent::_update();
  }
}