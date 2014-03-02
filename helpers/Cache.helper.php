<?php

/**
 * Provide many caching utility helper methods.
 *
 * Usefull for data that has to be compiled from various sources otherwise, e.g. translations, ...
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://www.marcoraddatz.com>
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 3.1
 *
 */

namespace candyCMS\Core\Helpers;

/**
 * Class Cache
 * @package candyCMS\Core\Helpers
 *
 */
class Cache {

  /**
   * Determine whether there is cached data
   *
   * @static
   * @access public
   * @param string $sIdent the identifier where the data is stored
   * @param array $aData the array where the loaded data will be appended to, if a cache was found
   * @return boolean true|false whether a cache was found
   *
   */
  public static function isCachedAndLoad($sIdent, &$aData) {
    $sCacheFile = PATH_CACHE . '/' . WEBSITE_MODE . '/' . WEBSITE_LOCALE . '/' . md5($sIdent) . '.php.cache';

    if (file_exists($sCacheFile)) {
      $aData = false;
      require $sCacheFile;
      return $aData !== false;
    }

    else
      return false;
  }

  /**
   * Save some array with data to a cache file
   *
   * @static
   * @access public
   * @param string $sIdent the identifier where the data will be stored
   * @param array $aData the array with the data, note that only basic objects are supported (string|int|...)
   * @return boolean true|false
   *
   */
  public static function save($sIdent, &$aData) {
    $sCacheDir = PATH_CACHE . '/' . WEBSITE_MODE . '/' . WEBSITE_LOCALE . '/';
    $sCacheFile = $sCacheDir . md5($sIdent) . '.php.cache';

    $sData = "<?php\n\$aData = " . var_export($aData, true) . ";\n";
    $bResult = (bool) file_put_contents($sCacheFile, $sData);
    return $bResult;
  }

  /**
   * Clear a cache file
   *
   * @static
   * @access public
   * @param string $sIdent the identifier for the cache
   * @return boolean whether the cache could be deleted, true if no cache was found
   *
   */
  public static function clear($sIdent) {
    $sCacheFile = PATH_CACHE . '/' . WEBSITE_MODE . '/' . WEBSITE_LOCALE . '/' . md5($sIdent) . '.php.cache';

    return file_exists($sCacheFile) ? unlink($sCacheFile) : true;
  }
}
