<?php

/**
 * Show blog headlines.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.5
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\SmartySingleton as Smarty;

final class Headlines {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'Headlines';

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
   * Show the (cached) headlines.
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

    $sCacheId = UNIQUE_PREFIX . '|blogs|' . self::IDENTIFIER . '|' . substr(md5($this->aSession['user']['role']), 0 , 10);
    if (!$oSmarty->isCached($oTemplate, $sCacheId)) {
      $sBlogsModel = \candyCMS\Core\Models\Main::__autoload('Blogs');
      $oModel = new $sBlogsModel($this->_aRequest, $this->_aSession);

      $oSmarty->assign('data', $oModel->getOverview(PLUGIN_HEADLINES_LIMIT));
    }

    return $oSmarty->fetch($oTemplate, $sCacheId);
  }
}
