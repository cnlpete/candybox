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

namespace candyCMS\Core\Controllers;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Helper;

class Sitemaps extends Main {

  /**
   * Show the sitemap as XML.
   *
   * @access protected
   * @return string XML content
   *
   */
  protected function _overviewXML() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'overviewXML');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'overviewXML');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    Header('Content-Type: text/xml');

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID)) {
      $this->oSmarty->assign('_website_landing_page_', WEBSITE_URL . '/' . WEBSITE_LANDING_PAGE);
      $this->_getSitemapData();
    }

    return $this->oSmarty->display($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Show the sitemap as HTML.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _overview() {
    $sTemplateDir   = Helper::getTemplateDir($this->_sController, 'overview');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'overview');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    if (!$this->oSmarty->isCached($sTemplateFile, UNIQUE_ID))
      $this->_getSitemapData();

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   * Generate the sitemap. Query tables and build structure.
   *
   * @access protected
   *
   */
  protected function _getSitemapData() {
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
}