<?php

/**
 * This plugin rewrites the standard date into a nicer "today" / "yesterday" format.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.1
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\SmartySingleton as Smarty;

final class FormatTimestamp {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'FormatTimestamp';

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

    $sCacheId = WEBSITE_MODE . '|' . WEBSITE_LOCALE . '|plugins|' . self::IDENTIFIER;
    if (!$oSmarty->isCached($oTemplate, $sCacheId)) {
      # the jQuery.timeago plugin takes it range in milliseconds,
      # PLUGIN_FORMATTIMESTAMP_RANGE is in minutes and defaults to 3 days
      $iRange = 1000 * 60 * (defined('PLUGIN_FORMATTIMESTAMP_RANGE') ? PLUGIN_FORMATTIMESTAMP_RANGE : 4320);
      $oSmarty->assign('range', $iRange);
    }

    return $oSmarty->fetch($oTemplate, $sCacheId);
  }
}
