<?php

/**
 * Insert Disqus instead of normal comments.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 3.0
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\SmartySingleton;

final class Disqus {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'Disqus';

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
   * Initialize the plugin and register all needed events.
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

    # Now register some events with the pluginmanager
    $oPlugins->registerSimplePlugin($this);
  }

  /**
   * Register HTML code to display.
   *
   * @final
   * @access public
   * @return string HTML content
   * @todo remove $sCacheId 
   *
   */
  public final function show() {
    $sTemplateDir   = Helper::getPluginTemplateDir(self::IDENTIFIER, 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->setTemplateDir($sTemplateDir);
    $oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);
    
    if (isset($this->_aRequest['id']))
      $oSmarty->assign('disqus_url', WEBSITE_URL . '/' . $this->_aRequest['controller'] . '/' . $this->_aRequest['id']);

    $sCacheId = WEBSITE_MODE . '|' . WEBSITE_LOCALE . '|plugins|' . self::IDENTIFIER;
    if (!$oSmarty->isCached($sTemplateFile, $sCacheId)) {
      $oSmarty->assign('WEBSITE_MODE', WEBSITE_MODE);
      $oSmarty->assign('PLUGIN_DISQUS_SHORTNAME', PLUGIN_DISQUS_SHORTNAME);
    }

    return $oSmarty->fetch($sTemplateFile, $sCacheId);
  }
}