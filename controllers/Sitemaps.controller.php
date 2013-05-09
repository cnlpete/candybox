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
use candyCMS\Core\Helpers\SmartySingleton as Smarty;

class Sitemaps extends Main {

  /**
   * Show the sitemap as XML.
   *
   * @access protected
   * @return string XML content
   *
   */
  protected function _overviewXML() {
    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'overviewXML');
    $this->oSmarty->setTemplateDir($oTemplate);

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID)) {
      $this->oSmarty->assign('_website_landing_page_', WEBSITE_URL . '/' . WEBSITE_LANDING_PAGE);
      $this->_getSitemapData();
    }

    return $this->oSmarty->display($oTemplate, UNIQUE_ID);
  }

  /**
   * Show the sitemap as HTML.
   *
   * @access protected
   * @return string HTML content
   *
   */
  protected function _overview() {
    $oTemplate = $this->oSmarty->getTemplate($this->_sController, 'overview');
    $this->oSmarty->setTemplateDir($oTemplate);

    if (!$this->oSmarty->isCached($oTemplate, UNIQUE_ID))
      $this->_getSitemapData();

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   * Generate the sitemap. Query tables and build structure.
   *
   * @access protected
   *
   */
  protected function _getSitemapData() {
    $aSitemapModels = array_filter( array_map('trim', explode(',', DATA_SITEMAPS)));
    foreach ($aSitemapModels as $sSitemapModel) {
      $sModel = $this->__autoload($sSitemapModel, true);
      $oModel = new $sModel($this->_aRequest, $this->_aSession);
      $this->oSmarty->assign(strtolower($sSitemapModel), $oModel->getOverview(1000));
    }
  }
}
