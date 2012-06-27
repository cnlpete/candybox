<?php

/**
 * Print out sitemap as HTML or XML.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace CandyCMS\Core\Controllers;

use CandyCMS\Core\Helpers\AdvancedException;
use CandyCMS\Core\Helpers\Helper;

class Sitemaps extends Main {

  /**
   * Show the sitemap as XML.
   *
   * @access public
   * @return string XML content
   *
   */
  public function xml() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'xml');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'xml');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    Header('Content-Type: text/xml');

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
      $this->oSmarty->assign('_website_landing_page_', WEBSITE_URL . '/' . WEBSITE_LANDING_PAGE);
      $this->_getSitemap();
    }

    exit($this->oSmarty->display($sTemplateFile, UNIQUE_ID));
  }

  /**
   * Show the sitemap as HTML.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _show() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID))
      $this->_getSitemap();

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Generate the sitemap. Query tables and build structure.
   *
   * @access protected
   *
   */
  protected function _getSitemap() {
    $sModel     = $this->__autoload('Blogs', true);
    $oBlogs     = new $sModel($this->_aRequest, $this->_aSession);

    $sModel     = $this->__autoload('Contents', true);
    $oContents  = new $sModel($this->_aRequest, $this->_aSession);

    $sModel     = $this->__autoload('Galleries', true);
    $oGalleries = new $sModel($this->_aRequest, $this->_aSession);

    $this->oSmarty->assign('blogs', $oBlogs->getOverview(1000));
    $this->oSmarty->assign('contents', $oContents->getOverview(1000));
    $this->oSmarty->assign('galleries', $oGalleries->getOverview(false, 1000));
  }

  /**
   * There is no create action for the sitemaps controller
   *
   * @access public
   *
   */
  public function create() {
    AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->create()');
    return Helper::redirectTo('/errors/404');
  }

  /**
   * There is no update action for the sitemaps controller
   *
   * @access public
   *
   */
  public function update() {
    AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->update()');
    return Helper::redirectTo('/errors/404');
  }

  /**
   * There is no destroy action for the sitemaps controller
   *
   * @access public
   *
   */
  public function destroy() {
    AdvancedException::writeLog('404: Trying to access ' . ucfirst($this->_sController) . '->destroy()');
    return Helper::redirectTo('/errors/404');
  }
}