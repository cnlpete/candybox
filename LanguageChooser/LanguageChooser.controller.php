<?php

/**
 * Show all available languages
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 3.0
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\SmartySingleton as Smarty;

final class LanguageChooser {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'LanguageChooser';

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

    $sCacheId = WEBSITE_MODE . '|' . WEBSITE_LOCALE . '|plugins|' . self::IDENTIFIER . '|' . substr(md5($this->_aSession['user']['role']), 0 , 10);
    if (!$oSmarty->isCached($oTemplate, $sCacheId)) {
      $aLangs = array();
      foreach (I18n::getPossibleLanguages() as $sLang) {
        $aLangs[] = array(
            'lang'      => $sLang,
            'selected'  => WEBSITE_LANGUAGE == substr($sLang, 0, 2),
            'title'     => I18n::get('languagechooser.' . $sLang));
      }
      $oSmarty->assign('languages', $aLangs);
    }

    return $oSmarty->fetch($oTemplate, $sCacheId);
  }
}
