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

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\SmartySingleton;

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
    $sTemplateDir   = Helper::getPluginTemplateDir(self::IDENTIFIER, 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->setTemplateDir($sTemplateDir);
    $oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    $sCacheId = WEBSITE_MODE . '|blogs|' . WEBSITE_LOCALE . '|' . self::IDENTIFIER . '|' . substr(md5($this->aSession['user']['role']), 0 , 10);
    if (!$oSmarty->isCached($sTemplateFile, $sCacheId)) {
      $sBlogsModel = \candyCMS\Core\Models\Main::__autoload('Blogs');
      $oModel = new $sBlogsModel($this->_aRequest, $this->_aSession);

      $oSmarty->assign('data', $oModel->getOverview(PLUGIN_HEADLINES_LIMIT));
    }

    return $oSmarty->fetch($sTemplateFile, $sCacheId);
  }
}
