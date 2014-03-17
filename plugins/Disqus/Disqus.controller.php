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

namespace candybox\Plugins;

use candyCMS\Core\Helpers\SmartySingleton as Smarty;

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
    $oPlugins->registerCommentPlugin($this);
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
    $oSmarty = Smarty::getInstance();
    $oTemplate = $oSmarty->getTemplate(self::IDENTIFIER, 'show', true);
    $oSmarty->setTemplateDir($oTemplate);
    $oSmarty->setCaching(Smarty::CACHING_LIFETIME_SAVED);

    if (isset($this->_aRequest['id']))
      $oSmarty->assign('disqus_url', WEBSITE_URL . '/' . $this->_aRequest['controller'] . '/' . $this->_aRequest['id']);

    $sCacheId = UNIQUE_PREFIX . '|plugins|' . self::IDENTIFIER;
    if (!$oSmarty->isCached($oTemplate, $sCacheId)) {
      $oSmarty->assign('WEBSITE_MODE', WEBSITE_MODE);
      $oSmarty->assign('PLUGIN_DISQUS_SHORTNAME', PLUGIN_DISQUS_SHORTNAME);
    }

    return $oSmarty->fetch($oTemplate, $sCacheId);
  }
}
