<?php

/**
 * Translate a string.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace CandyCMS\Core\Helpers;

use CandyCMS\Core\Helpers\AdvancedException;

class I18n {

  /**
   *
   * holds all translations
   *
   * @var static
   * @access private
   *
   */
  private static $_aLang = null;

  /**
   *
   * holds the object
   *
   * @var static
   * @access private
   *
   */
  private static $_oObject = null;

  /**
  *
  * holds the wanted Language
  *
  * @var static
  * @access private
  *
  */
  private static $_sLanguage = null;

  /**
   * Read the language yaml and save information into session due to fast access.
   *
   * @access public
   * @param string $sLanguage language to load
   * @param array $aSession the session object, if given save the translations in S_SESSION['lang']
   *
   */
  public function __construct($sLanguage = 'en', &$aSession = null) {
    if ($aSession)
      $this->_aSession = $aSession;

    I18n::$_oObject = $this;

    # first call
    if (!isset(I18n::$_aLang) || WEBSITE_MODE == 'development' || WEBSITE_MODE == 'test') {
      $sLanguageFile = $sLanguage . '.yml';
      $sLanguagePath = PATH_STANDARD . '/app/languages/' . $sLanguageFile;

      # Remove mistakenly set cookie to avoid exceptions.
      if (!file_exists($sLanguagePath))
        $_COOKIE['default_language'] = 'en';

      # reload the files, if necessary
      if (WEBSITE_MODE == 'development' || WEBSITE_MODE == 'test' || !isset($aSession['lang'])) {
        self::$_aLang = array();

        if ($aSession != null)
          $aSession['lang'] = & I18n::$_aLang;
      }
      # use the already loaded session stuff
      else
        self::$_aLang = & $aSession['lang'];

      # load the default language
      self::load($sLanguage);
    }
  }

  /**
   *
   * load a language to internal language cache
   *
   * @param string $sLanguage the language to load
   * @return boolean whether language could be loaded
   */
  public static function load($sLanguage) {
    # already loaded?
    if (isset(I18n::$_aLang[$sLanguage])) {
      self::$_sLanguage = $sLanguage;
      SmartySingleton::getInstance()->setDefaultLanguage(self::$_aLang[$sLanguage], $sLanguage);
      return true;
    }

    # have to load from yml-files
    $sLanguageFile        = $sLanguage . '.yml';
    $sCustomLanguageFile  = PATH_STANDARD . '/app/languages/' . $sLanguageFile;
    $sCoreLanguageFile    = PATH_STANDARD . '/vendor/candyCMS/core/languages/' . $sLanguageFile;

    # language does not exist
    if (!file_exists($sCustomLanguageFile))
      return false;

    # load the core language file
    self::$_aLang[$sLanguage] = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sCoreLanguageFile));

    # load the plugin language files and merge them
    //FIXME only load enabled plugins ...
    $sPluginPath = PATH_STANDARD . '/vendor/candyCMS/plugins/';
    $oDir = opendir($sPluginPath);
    $aPlugins = array();
    while ($sFile = readdir($oDir)) {
      if ($sFile == '.' || $sFile == '..')
        continue;
      $aPlugins[] = $sFile;
    }
    foreach ($aPlugins as $sPlugin)
      if (file_exists($sPluginPath . $sPlugin . '/languages')) {
        $aPluginLang = array();
        if (file_exists($sPluginPath . $sPlugin . '/languages/' . $sLanguageFile))
          $aPluginLang = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sPluginPath . $sPlugin . '/languages/' . $sLanguageFile));
        else
          # default to en, if required language is not found
          $aPluginLang = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sPluginPath . $sPlugin . '/languages/en.yml'));

        Helper::recursiveOnewayArrayReplace(self::$_aLang[$sLanguage], $aPluginLang);
      }

    # load the extension languag-files and merge them
    if (EXTENSION_CHECK) {
      $sExtensionLanguagePath = PATH_STANDARD . '/app/extensions/languages/';
      $oDir = opendir($sExtensionLanguagePath);
      while ($sFile = readdir($oDir)) {
        if ($sFile == '.' || $sFile == '..')
          continue;

        $aExtensionLang = array();
        if (file_exists($sExtensionLanguagePath . $sFile . '/' . $sLanguageFile))
          $aExtensionLang = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sExtensionLanguagePath . $sFile . '/' . $sLanguageFile));
        else
          # default to en, if required language si not found
          $aExtensionLang = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sExtensionLanguagePath . $sFile . '/en.yml'));

        Helper::recursiveOnewayArrayReplace(self::$_aLang[$sLanguage], $aExtensionLang);
      }
    }

    # merge all that with the users cusom language file
    $aUserLang = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sCustomLanguageFile));
    Helper::recursiveOnewayArrayReplace(I18n::$_aLang, $aUserLang);

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
    if (isset( I18n::$_aLang[self::$_sLanguage])) {
      $mTemp =  I18n::$_aLang[self::$_sLanguage];
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

      try {
        return is_string($mTemp) ? (string) $mTemp : '';
      }
      catch (AdvancedException $e) {
        die('No such translation: ' . $e->getMessage());
      }
    }
  }

  /**
   * Unset the language saved in the session.
   *
   * @static
   * @param string $sLanguage language part we want to unload. Unload all if not set
   * @access public
   *
   */
  public static function unsetLanguage($sLanguage = '') {
    if ($sLanguage == '') {
      self::$_aLang = null;
      if (self::$_oObject != null)
        unset(self::$_oObject->_aSession['lang']);
    }
    else {
      self::$_aLang[$sLanguage] = null;
      if (self::$_oObject != null)
        unset(self::$_oObject->_aSession['lang'][$sLanguage]);
    }
  }
}