<?php

/**
 * TagCloud Plugin.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.1
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\SmartySingleton as Smarty;

final class TagCloud {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'TagCloud';

  /**
   * @var array
   * @access protected
   *
   */
  protected $_aRequest;

  /**
   * @var array
   * @access protected
   *
   */
  protected $_aSession;

  /**
   * Initialize the software by adding input params.
   *
   * @access public
   * @param array $aRequest alias for the combination of $_GET and $_POST
   * @param array $aSession alias for $_SESSION
   * @param object $oPlugins the PluginManager
   *
   */
  public function __construct(&$aRequest, &$aSession, &$oPlugins) {
    $this->_aRequest  = & $aRequest;
    $this->_aSession  = & $aSession;

    # now register some events with the pluginmanager
    $oPlugins->registerSimplePlugin($this);
  }

  /**
   * Show the (cached) tagcloud.
   *
   * @final
   * @access public
   * @param array $aRequest
   * @param array $aSession
   * @return string HTML
   *
   */
  public final function show() {
    $oSmarty = Smarty::getInstance();
    $oTemplate = $oSmarty->getTemplate(self::IDENTIFIER, 'show', true);
    $oSmarty->setTemplateDir($oTemplate);
    $oSmarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);

    $sCacheId = UNIQUE_PREFIX . '|blogs|' . self::IDENTIFIER . '|' . substr(md5($this->_aSession['user']['role']), 0 , 10);
    if (!$oSmarty->isCached($oTemplate, $sCacheId)) {

      $sBlogsModel = \candyCMS\Core\Models\Main::__autoload('Blogs');
      $oModel = new $sBlogsModel($this->_aRequest, $this->_aSession);

      // get all tags + how often they are used
      $aTags = array();
      $aSortableTags = array();

      foreach ($oModel->getOverview(0) as $aRow) {
        foreach ($aRow['tags'] as $sTag) {
          // initialize, if tag did not appear before
          if (!$aTags[$sTag]) {
            $aTags[$sTag] = array($aRow);
            $aSortableTags[$sTag] = 1;
          }
          else {
            // update counter
            $aSortableTags[$sTag] = $aSortableTags[$sTag] + 1;
            $aTags[$sTag][] = $aRow;
          }
        }
      }

      // order by appearance amount DESC
      arsort($aSortableTags);

      $aData = array();
      $iIndex = 0;

      if (!defined('PLUGIN_TAGCLOUD_LIMIT'))
        define('PLUGIN_TAGCLOUD_LIMIT', 10);

      if (!defined('PLUGIN_TAGCLOUD_FILTER'))
        define('PLUGIN_TAGCLOUD_FILTER', 1);

      foreach ($aSortableTags as $sTag => $iAmount) {
        if ($iIndex >= PLUGIN_TAGCLOUD_LIMIT)
          break;

        if ($iAmount < PLUGIN_TAGCLOUD_FILTER)
          break;

        $aData[$iIndex] = array(
            'title'       => $sTag,
            'amount'      => $iAmount,
            'blogentries' => $aTags[$sTag],
            'url'         => WEBSITE_URL . '/blogs/' . $sTag);

        ++$iIndex;
      }

      $oSmarty->assign('data', $aData);
    }

    return $oSmarty->fetch($oTemplate, $sCacheId);
  }
}
