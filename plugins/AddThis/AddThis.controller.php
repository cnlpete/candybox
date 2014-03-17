<?php

/**
 * This plugins shows sharing options from https://www.addthis.com.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.1.2
 * @see https://www.addthis.com
 *
 */

namespace candybox\Plugins;

use candyCMS\Core\Helpers\SmartySingleton as Smarty;

final class AddThis {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'AddThis';

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

    # now register some events with the pluginmanager
    $oPlugins->registerSimplePlugin($this);
  }

  /**
   * Show the (cached) javascript code, that enables the jQuery plugin.
   *
   * @final
   * @access public
   * @param array $aRequest
   * @param array $aSession
   * @return string HTML
   * @todo remove $sCacheId
   *
   */
  public final function show() {
    $oSmarty = Smarty::getInstance();
    $oTemplate = $oSmarty->getTemplate(self::IDENTIFIER, 'show', true);
    $oSmarty->setTemplateDir($oTemplate);
    $oSmarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);

    $sCacheId = UNIQUE_PREFIX . '|plugins|' . self::IDENTIFIER;

    return $oSmarty->fetch($oTemplate, $sCacheId);
  }
}
