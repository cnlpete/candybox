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

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\SmartySingleton;

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
    $sTemplateDir   = Helper::getPluginTemplateDir(self::IDENTIFIER, 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->setTemplateDir($sTemplateDir);
    $oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    $sCacheId = WEBSITE_MODE . '|plugins|' . WEBSITE_LOCALE . '|' . self::IDENTIFIER;
    if (!$oSmarty->isCached($sTemplateFile, $sCacheId)) {
      $oSmarty->assign('WEBSITE_MODE', WEBSITE_MODE);
      $oSmarty->assign('PLUGIN_PIWIK_ID', PLUGIN_PIWIK_ID);
      $oSmarty->assign('PLUGIN_PIWIK_URL', PLUGIN_PIWIK_URL);
    }

    return $oSmarty->fetch($sTemplateFile, $sCacheId);
  }
}
