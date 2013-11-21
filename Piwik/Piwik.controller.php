<?php

/**
 * Piwik analytics code.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\SmartySingleton as Smarty;

final class Piwik {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'Piwik';

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
   * Get The HTML-Code for Piwik.
   *
   * @final
   * @access public
   * @return string HTML
   *
   */
  public final function show() {
    $oSmarty = Smarty::getInstance();
    $oTemplate = $oSmarty->getTemplate(self::IDENTIFIER, 'show', true);
    $oSmarty->setTemplateDir($oTemplate);
    $oSmarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);

    $sCacheId = UNIQUE_PREFIX . '|plugins|' . self::IDENTIFIER;
    if (!$oSmarty->isCached($oTemplate, $sCacheId)) {
      $oSmarty->assign('WEBSITE_MODE', WEBSITE_MODE);
      $oSmarty->assign('PLUGIN_PIWIK_ID', PLUGIN_PIWIK_ID);
      $oSmarty->assign('PLUGIN_PIWIK_URL', PLUGIN_PIWIK_URL);
    }

    return $oSmarty->fetch($oTemplate, $sCacheId);
  }
}
