<?php

/**
 * Print out sitemap as HTML or XML.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Core\Controllers;

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

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
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

      // @todo set second parameter to true at blogs to show multilanguage entries
      $this->oSmarty->assign(strtolower($sSitemapModel), $oModel->getOverview(1000));
    }
  }
}