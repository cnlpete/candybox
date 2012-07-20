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
  * Holds the wanted Language
  *
  * @var static
  * @access private
  *
  */
  private static $_sLanguage = null;

  /**
  *
  * Holds the user specified plugins
  *
  * @var static
  * @access private
  *
  */
  private static $_aPlugins = null;

  /**
   * Read the language yaml and save information into session due to fast access.
   *
   * @access public
   * @param string $sLanguage language to load
   * @param array $aSession the session object, if given save the translations in S_SESSION['lang']
   * @param array $aPlugins plugins to load
   *
   */
  public function __construct($sLanguage = 'en', &$aSession = null, $aPlugins = array()) {
    if ($aSession)
      $this->_aSession = $aSession;

    self::$_oObject = $this;

    self::$_aPlugins = $aPlugins;

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
   * Load a language to internal language cache
   *
   * @access public
   * @param string $sLanguage the language to load
   * @return boolean whether language could be loaded
   *
   */
  public static function load($sLanguage) {
    # Already loaded?
    if (isset(I18n::$_aLang[$sLanguage])) {
      self::$_sLanguage = $sLanguage;
      SmartySingleton::getInstance()->setDefaultLanguage(self::$_aLang[$sLanguage], $sLanguage);
      return true;
    }

    # Have to load from YML files
    $sLanguageFile        = $sLanguage . '.yml';
    $sCustomLanguageFile  = PATH_STANDARD . '/app/languages/' . $sLanguageFile;
    $sCoreLanguageFile    = PATH_STANDARD . '/vendor/candyCMS/core/languages/' . $sLanguageFile;

    # Language does not exist
    if (!file_exists($sCustomLanguageFile))
      return false;

    # Load the core language file
    if (file_exists($sCoreLanguageFile))
      self::$_aLang[$sLanguage] = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sCoreLanguageFile));

    else
    # We also allow the user to create custom languages, everything he does not overwrite, will be english
      self::$_aLang[$sLanguage] = \Symfony\Component\Yaml\Yaml::parse(
                      file_get_contents(PATH_STANDARD . '/vendor/candyCMS/core/languages/en.yml'));

    # Load the plugin language files and merge them
    $sPluginPath = PATH_STANDARD . '/vendor/candyCMS/plugins/';

    foreach (self::$_aPlugins as $sPlugin) {
      if (file_exists($sPluginPath . $sPlugin . '/languages')) {
        $aPluginLang = file_exists($sPluginPath . $sPlugin . '/languages/' . $sLanguageFile) ?
                \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sPluginPath . $sPlugin . '/languages/' . $sLanguageFile)) :
                \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sPluginPath . $sPlugin . '/languages/en.yml'));

        Helper::recursiveOnewayArrayReplace(self::$_aLang[$sLanguage], $aPluginLang);
      }
    }

    # Merge all that with the users custom language file
    Helper::recursiveOnewayArrayReplace(I18n::$_aLang,
            \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sCustomLanguageFile)));

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