<?php

/**
 * Show blog entries or gallery album files as RSS feed.
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

class Rss extends Main {

  /**
   * Define the content type as RSS.
   *
   * @access public
   *
   */
  public function __init() {
    Header('Content-Type: application/rss+xml');
  }

  /**
   * Show RSS feed.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    exit($this->_aRequest['section'] == 'galleries' && $this->_iId > 0 ?
          $this->_showMedia() :
          $this->_showDefault());
  }

  /**
   * Show default RSS template.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _showDefault() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'default');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'default');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    $sModel = $this->__autoload('Blogs', true);
    $oModel = new $sModel($this->_aRequest, $this->_aSession);

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID))
      $this->oSmarty->assign('data', $oModel->getOverview());

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Show media RSS template
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _showMedia() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'media');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'media');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
      $sModel = $this->__autoload('Galleries', true);
      $oModel = new $sModel($this->_aRequest, $this->_aSession);

      $aData  = $oModel->getId($this->_iId, false, true);

      $this->oSmarty->assign('_copyright_', $aData[$this->_iId]['author']['full_name']);
      $this->oSmarty->assign('_title_', $aData[$this->_iId]['title']);
      $this->oSmarty->assign('_content_', $aData[$this->_iId]['content']);
      $this->oSmarty->assign('_locale_', WEBSITE_LOCALE);
      $this->oSmarty->assign('_link_', Helper::removeSlash($aData[$this->_iId]['url']));
      $sGalleryDate = $aData[$this->_iId]['date']['raw'];

      $aData = & $aData[$this->_iId]['files'];
      rsort($aData);

      $this->oSmarty->assign('_pubdate_', count($aData) > 0 ? $aData[0]['date']['raw'] : $sGalleryDate);
      $this->oSmarty->assign('data', $aData);
    }

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * There is no create action for the RSS controller.
   * This rule is obsolete since there is a route 'rss/(:alpha)' but that might change to 'rss/galleries'
   *
   * @access public
   *
   */
  public function create() {
    return Helper::redirectTo('/errors/404');
  }

  /**
   * There is no update action for the RSS controller.
   *
   * @access public
   *
   */
  public function update() {
    return Helper::redirectTo('/errors/404');
  }

  /**
   * There is no destroy action for the RSS controller.
   *
   * @access public
   *
   */
  public function destroy() {
    return Helper::redirectTo('/errors/404');
  }
}