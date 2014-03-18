<?php

/**
 * Translate a string.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Core\Helpers;

use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\Cache;
use \Symfony\Component\Yaml\Yaml;

/**
 * Class I18n
 * @package candyCMS\Core\Helpers
 *
 */
class I18n {

  /**
   *
   * Holds all translations
   *
   * @var static
   * @access private
   *
   */
  private static $_aLang = null;

  /**
   *
   * Holds the object
   *
   * @var static
   * @access private
   *
   */
  private static $_oObject = null;

  /**
   *
   * Holds the current language
   *
   * @var static
   * @access private
   *
   */
  private static $_sLanguage = null;

  /**
   * Build a Translation-Helper with a specified language.
   * Will use cache file for faster access.
   *
   * @access public
   * @param string $sLanguage language to load
   *
   */
  public function __construct($sLanguage = 'en') {
    self::$_oObject = $this;
    self::$_aLang = array();
    self::load($sLanguage);
  }

  /**
   *
   * Load a language to internal language cache
   *
   * @access public
   * @param string $sLanguage the language to load
   * @return boolean whether language could be loaded
   *
   */
  public static function load($sLanguage) {
    # Already loaded?
    if (isset(self::$_aLang[$sLanguage])) {
      self::$_sLanguage = $sLanguage;
      SmartySingleton::getInstance()->setDefaultLanguage(self::$_aLang[$sLanguage], $sLanguage);
      return true;
    }

    # Try to read from cache file
    if (Cache::isCachedAndLoad('translation' . $sLanguage, self::$_aLang[$sLanguage])) {
      self::$_sLanguage = $sLanguage;
      SmartySingleton::getInstance()->setDefaultLanguage(self::$_aLang[$sLanguage], $sLanguage);
      return true;
    }

    # no cache file found, load it up and generate cache file
    # Have to parse all the different YML files
    $sLanguageFile        = $sLanguage . '.yml';
    $sCustomLanguageFile  = PATH_STANDARD . '/app/languages/' . $sLanguageFile;
    $sCoreLanguageDir     = PATH_STANDARD . '/vendor/candycms/core/languages/';
    $sCoreLanguageFile    = $sCoreLanguageDir . $sLanguageFile;

    # only load languages that the user specified
    if (!file_exists($sCustomLanguageFile))
      return false;

    # Load the core language file
    if (file_exists($sCoreLanguageFile))
      self::$_aLang[$sLanguage] = Yaml::parse(file_get_contents($sCoreLanguageFile));

    # We also allow the user to create custom languages, everything he does not overwrite, will be english
    else
      self::$_aLang[$sLanguage] = Yaml::parse(
                      file_get_contents($sCoreLanguageDir + 'en.yml'));

    # Load the module language files and merge them
    $sModulePath    = PATH_STANDARD . '/modules/';
    $aModuleNames   = array('Blog', 'Calendar', 'Contents'); //.. TODO FIXME

    foreach ($aModuleNames as $sModule) {
      $sModuleLanguagesPath = $sModulePath . strtolower($sModule) . '/languages/';
      $aModuleLang = array();
      $sLowerModuleName = strtolower($sModule);
      if (file_exists($sModuleLanguagesPath . $sLanguageFile))
        $aModuleLang[$sLowerModuleName] = 
            Yaml::parse(file_get_contents($sModuleLanguagesPath . $sLanguageFile));
      else if (file_exists($sModuleLanguagesPath . 'en.yml'))
        $aModuleLang[$sLowerModuleName] = 
            Yaml::parse(file_get_contents($sModuleLanguagesPath . 'en.yml'));

      Helper::recursiveOnewayArrayReplace(self::$_aLang[$sLanguage], $aModuleLang);
    }

    # Load the plugin language files and merge them
    $sPluginPath    = PATH_STANDARD . '/plugins/';
    $oPluginManager = PluginManager::getInstance();
    $aPluginNames   = $oPluginManager->getLoadedPluginNames();

    foreach ($aPluginNames as $sPlugin) {
      $sPluginLanguagesPath = $sPluginPath . $sPlugin . '/languages/';
      $aPluginLang = array();
      $sLowerPluginName = strtolower($sPlugin);
      if (file_exists($sPluginLanguagesPath . $sLanguageFile))
        $aPluginLang[$sLowerPluginName] = 
            Yaml::parse(file_get_contents($sPluginLanguagesPath . $sLanguageFile));
      else if (file_exists($sPluginLanguagesPath . 'en.yml'))
        $aPluginLang[$sLowerPluginName] = 
            Yaml::parse(file_get_contents($sPluginLanguagesPath . 'en.yml'));

      Helper::recursiveOnewayArrayReplace(self::$_aLang[$sLanguage], $aPluginLang);
    }

    # Merge all that with the users custom language file
    $aReplace = Yaml::parse(file_get_contents($sCustomLanguageFile));
    Helper::recursiveOnewayArrayReplace(
            self::$_aLang[$sLanguage],
            $aReplace);

    # Bugfix: Disable errors during tests
    if (!ACTIVE_TEST)
      Cache::save('translation' . $sLanguage, self::$_aLang[$sLanguage]);

    self::$_sLanguage = $sLanguage;
    SmartySingleton::getInstance()->setDefaultLanguage(self::$_aLang[$sLanguage], $sLanguage);
    return true;
  }

  /**
   * Return the language array.
   *
   * @static
   * @access public
   * @param string $sPart main part of the array to return string from
   * @return array $_SESSION['lang'] session array with language data
   *
   */
  public static function getArray($sPart = '') {
    return !$sPart ? I18n::$_aLang[self::$_sLanguage] : I18n::$_aLang[self::$_sLanguage][$sPart];
  }

  /**
   * Get language as JSON for JavaScript.
   *
   * @static
   * @access public
   * @return string JSON
   *
   */
  public static function getJson() {
    return json_encode(self::getArray('javascript'));
  }

  /**
   * Get a specific language string.
   *
   * @static
   * @access public
   * @param string $sLanguagePart language part we want to load. Separated by dots.
   * @return string $mTemp
   *
   */
  public static function get($sLanguagePart) {
    if (isset(I18n::$_aLang[self::$_sLanguage])) {
      $mTemp = I18n::$_aLang[self::$_sLanguage];
      foreach (explode('.', $sLanguagePart) as $sPart) {
        if (!is_string($mTemp)) {
          if (array_key_exists($sPart, $mTemp)) {
            $mTemp = & $mTemp[$sPart];
          }
        }
      }

      # Do we have other parameters?
      $iNumArgs = func_num_args();
      if ($iNumArgs > 1) {
        # use sprintf
        $aArgs = func_get_args();
        array_shift($aArgs);
        $mTemp = vsprintf($mTemp, $aArgs);
      }

      if (!is_string($mTemp)) {
        AdvancedException::writeLog('MISSING TRANSLATION: "' . $sLanguagePart);
        return '### MISSING TRANSLATION: "' . $sLanguagePart . '" ###';
      }

      return (string) $mTemp;
    }
  }

  /**
   * Get all possible Languages
   *
   * @static
   * @return array all language strings that can be loaded
   * @access public
   * @todo test
   *
   */
  public static function getPossibleLanguages() {
    $aLangs = array();
    if (!Cache::isCachedAndLoad('translation.all', $aLangs)) {
      $sLanguagesPath = PATH_STANDARD . '/app/languages/';
      $oDir = opendir($sLanguagesPath);

      while ($sFile = readdir($oDir)) {
        if (substr($sFile, -4) != '.yml')
          continue;

        $sLang = substr($sFile, 0, -4);
        $aLangs[] = $sLang;
      }

      closedir($oDir);

      Cache::save('translation.all', $aLangs);
    }

    return $aLangs;
  }

  /**
   * Unset the language saved in the session.
   *
   * @static
   * @param string $sLanguage language part we want to unload. Unload all if not set
   * @access public
   * @todo test
   *
   */
  public static function unsetLanguage($sLanguage = '') {
    if ($sLanguage == '') {
      self::$_aLang = array();

      # clear the possible languages cache
      Cache::clear('translation.all');

      # clear all possible languages
      $aLangs = self::getPossibleLanguages();
      foreach ($aLangs as $sLang)
        Cache::clear('translation' . $sLang);
    }
    else {
      self::$_aLang[$sLanguage] = null;
      Cache::clear('translation' . $sLanguage);
    }
  }
}
