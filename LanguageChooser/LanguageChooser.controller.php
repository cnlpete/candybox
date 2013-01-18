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
   * Show the (cached) headlines.
   *
   * @final
   * @access public
   * @param array $aRequest
   * @param array $aSession
   * @return string HTML
   *
   */
  public final function show(&$aRequest, &$aSession) {
    $sTemplateDir   = Helper::getPluginTemplateDir(self::IDENTIFIER, 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->setTemplateDir($sTemplateDir);
    $oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    $sCacheId = WEBSITE_MODE . '|layout|' . WEBSITE_LOCALE . '|' . self::IDENTIFIER . '|' . substr(md5($aSession['user']['role']), 0 , 10);
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