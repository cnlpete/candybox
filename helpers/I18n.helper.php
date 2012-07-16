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

    if (!isset(I18n::$_aLang) || WEBSITE_MODE == 'development' || WEBSITE_MODE == 'test') {
      $sLanguageFile = $sLanguage . '.language.yml';
      $sLanguagePath = PATH_STANDARD . '/app/languages/' . $sLanguageFile;

      # Remove mistakenly set cookie to avoid exceptions.
      if (!file_exists($sLanguagePath))
        $_COOKIE['default_language'] = 'en';

      # reload the files, if necessary
      if (WEBSITE_MODE == 'development' || WEBSITE_MODE == 'test' || !isset($aSession['lang'])) {
        # load the core language file
        I18n::$_aLang = \Symfony\Component\Yaml\Yaml::parse(file_get_contents(PATH_STANDARD . '/vendor/candyCMS/core/languages/' . $sLanguageFile));

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
              # default to en, if required language si not found
              $aPluginLang = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sPluginPath . $sPlugin . '/languages/en.language.yml'));

            Helper::recursiveOnewayArrayReplace(I18n::$_aLang, $aPluginLang);
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
              $aExtensionLang = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sExtensionLanguagePath . $sFile . '/en.language.yml'));

            Helper::recursiveOnewayArrayReplace(I18n::$_aLang, $aExtensionLang);
          }
        }

        # merge all that with the users cusom language file
        $aUserLang = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sLanguagePath));
        Helper::recursiveOnewayArrayReplace(I18n::$_aLang, $aUserLang);

        if ($aSession != null)
          $aSession['lang'] = & I18n::$_aLang;
      }
      else
        I18n::$_aLang = & $aSession['lang'];
    }
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
    return !$sPart ? I18n::$_aLang : I18n::$_aLang[$sPart];
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
    if (isset( I18n::$_aLang)) {
      $mTemp =  I18n::$_aLang;
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
   * @access public
   *
   */
  public static function unsetLanguage() {
    I18n::$_aLang = null;
    if (I18n::$_oObject != null)
      unset(I18n::$_oObject->_aSession['lang']);
  }
}