<?php

/**
 * Show all available languages
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.2
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\SmartySingleton;

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
    $sTemplateDir   = Helper::getPluginTemplateDir(self::IDENTIFIER, 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->setTemplateDir($sTemplateDir);
    $oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    $sCacheId = WEBSITE_MODE . '|layout|' . WEBSITE_LOCALE . '|' . self::IDENTIFIER . '|' . substr(md5($this->_aSession['user']['role']), 0 , 10);
    if (!$oSmarty->isCached($sTemplateFile, $sCacheId)) {
      $aLangs = array();
      $sLanguagesPath = PATH_STANDARD . '/app/languages/';
      $oDir = opendir($sLanguagesPath);
      while ($sFile = readdir($oDir)) {
        if (substr($sFile, -4) != '.yml')
          continue;

        $sLang = substr($sFile, 0, -4);
        $aLangs[] = array(
            'lang' => $sLang,
            'selected' => WEBSITE_LANGUAGE == substr($sLang, 0, 2),
            'title' => I18n::get('languagechooser.' . $sLang));
      }
      closedir($sLanguagesPath);
      $oSmarty->assign('languages', $aLangs);
    }

    return $oSmarty->fetch($sTemplateFile, $sCacheId);
  }
}
