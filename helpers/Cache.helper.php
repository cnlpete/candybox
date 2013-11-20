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

class Cache {

  /**
   * determine whether there is cached data
   *
   * @static
   * @access public
   * @param string $sIdent the identifier where the data is stored
   * @param array $aData the array where the loaded data will be appended to, if a cache was found
   * @return boolean true|false whether a cache was found
   *
   */
  public static function isCachedAndLoad($sIdent, &$aData) {
    $sCacheFile = PATH_CACHE . '/' . WEBSITE_MODE . '/' . WEBSITE_LOCALE . '/' . md5($sIdent) . '.cache';

    if (file_exists($sCacheFile)) {
      $aData = json_decode(file_get_contents($sCacheFile), true);
      return $aData !== false;
    }

    else
      return false;
  }

  /**
   * save some array with data to a cache file
   *
   * @static
   * @access public
   * @param string $sIdent the identifier where the data will be stored
   * @param array $aData the array with the data, note that only basic objects are supported (string|int|...)
   * @return boolean true|false
   *
   */
  public static function save($sIdent, &$aData) {
    $sCacheFile = PATH_CACHE . '/' . WEBSITE_MODE . '/' . WEBSITE_LOCALE . '/' . md5($sIdent) . '.cache';
    return (bool) file_put_contents($sCacheFile, json_encode($aData));
  }

  /**
   * clear a cache file
   *
   * @static
   * @access public
   * @param string $sIdent the identifier for the cache
   * @return boolean whether the cache could be deleted, true if no cache was found
   *
   */
  public static function clear($sIdent) {
    $sCacheFile = PATH_CACHE . '/' . WEBSITE_MODE . '/' . WEBSITE_LOCALE . '/' . md5($sIdent) . '.cache';

    return file_exists($sCacheFile) ? unlink($sCacheFile) : true;
  }
}