<?php

/**
 * This plugins shows sharing options with a two-click-option to save the users privacy.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.1.1
 * @see http://www.heise.de/extras/socialshareprivacy/
 *
 */

namespace candybox\Plugins;

use candyCMS\Core\Helpers\SmartySingleton as Smarty;

final class SocialSharePrivacy {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'SocialSharePrivacy';

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
   * Show the (cached) javascript code, that enables the jQuery plugin.
   *
   * @final
   * @access public
   * @return string HTML
   * @todo remove $sCacheId
   *
   */
  public final function show() {
    $oSmarty = Smarty::getInstance();
    $oTemplate = $oSmarty->getTemplate(self::IDENTIFIER, 'show', true);
    $oSmarty->setTemplateDir($oTemplate);
    $oSmarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);

    return $oSmarty->fetch($oTemplate, UNIQUE_PREFIX . '|plugins|' . self::IDENTIFIER);
  }
}
