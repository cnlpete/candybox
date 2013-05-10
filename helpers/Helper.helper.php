<?php

/**
 * Provide many helper methods.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 *
 */

namespace candyCMS\Core\Helpers;

use candyCMS\Core\Controllers\Main;
use candyCMS\Core\Helpers\AdvancedException;
use candyCMS\Core\Helpers\PluginManager;
use candyCMS\Core\Helpers\Cache;
use PDO;
use lessc;

class Helper {

  /**
   * Display a success message after an action is done.
   *
   * @static
   * @access public
   * @param string $sMessage message to provide
   * @param string $sRedirectTo site to redirect to
   * @param array $aData data for ajax request
   * @return boolean true
   * @todo store in main session object
   * @todo extend tests
   *
   */
  public static function successMessage($sMessage, $sRedirectTo = '', $aData = '') {
    # This is supposed to be an AJAX request, so we will return JSON
    if (!empty($aData) && (isset($aData['type']) && 'json' == $aData['type'] || isset($aData['file']))) {
      header('Content-Type: application/json');
      exit(json_encode(array(
                'success'     => true,
                'data'        => WEBSITE_MODE == 'development' ? $aData : '',
                'redirectURL' => $sRedirectTo,
                'fileData'    => isset($aData['fileData']) ? $aData['fileData'] : ''
            )));
    } else {
      $_SESSION['flash_message'] = array(
          'type'      => 'success',
          'message'   => $sMessage,
          'headline'  => '');

      return $sRedirectTo ? Helper::redirectTo ($sRedirectTo) : true;
    }
  }

  /**
   * Display a warning message after an action is done.
   *
   * @static
   * @access public
   * @param string $sMessage message to provide
   * @param string $sRedirectTo site to redirect to
   * @param array $aData data for ajax request
   * @return boolean false
   * @todo store in main session object
   * @todo extend tests
   *
   */
  public static function warningMessage($sMessage, $sRedirectTo = '', $aData = '') {
    # This is supposed to be an AJAX request, so we will return JSON
    if (!empty($aData) && (isset($aData['type']) && 'json' == $aData['type'] || isset($aData['file']))) {
      header('Content-Type: application/json');
      exit(json_encode(array(

                'success'     => false,
                'data'        => WEBSITE_MODE == 'development' ? $aData : '',
                'redirectURL' => $sRedirectTo,
                'fileData'    => isset($aData['fileData']) ? $aData['fileData'] : ''
              )));
    } else {
      $_SESSION['flash_message'] = array(
          'type'    => 'warning',
          'message' => $sMessage,
          'headline'=> I18n::get('error.warning'));

      return $sRedirectTo ? Helper::redirectTo ($sRedirectTo) : false;
    }
  }

  /**
   * Display an error message after an action is done.
   *
   * @static
   * @access public
   * @param string $sMessage message to provide
   * @param string $sRedirectTo site to redirect to
   * @param array $aData data for ajax request
   * @return boolean false
   * @todo store in main session object
   *
   */
  public static function errorMessage($sMessage, $sRedirectTo = '', $aData = '') {
    if (!empty($aData) && (isset($aData['type']) && 'json' == $aData['type'] || isset($aData['file']))) {
      header('Content-Type: application/json');
      exit(json_encode(array(

                  'success'     => false,
                  'data'        => WEBSITE_MODE == 'development' ? $aData : '',
                  'redirectURL' => $sRedirectTo,
                  'fileData'    => isset($aData['fileData']) ? $aData['fileData'] : ''
              )));
    } else {
      $_SESSION['flash_message'] = array(
          'type'    => 'error',
          'message' => $sMessage,
          'headline'=> I18n::get('error.standard'));

      return $sRedirectTo ? Helper::redirectTo ($sRedirectTo) : false;
    }
  }

  /**
   * Redirect user to a specified page.
   *
   * @static
   * @access public
   * @param string $sUrl URL to redirect the user to
   *
   */
  public static function redirectTo($sUrl) {
    if (CRAWLER && $sUrl == '/errors/404') {
      header('Status: 404 Not Found');
      header('HTTP/1.0 404 Not Found');
    }

    exit(header('Location:' . $sUrl));
  }

  /**
   * Check if the provided email address is in a correct format.
   *
   * @static
   * @access public
   * @param string $sMail email address to check
   * @return boolean
   *
   */
  public static function checkEmailAddress($sMail) {
    return (bool) preg_match("/^([a-zA-Z0-9])+(\.?[a-zA-Z0-9_-]+)*@([a-zA-Z0-9_-]+\.)+[a-zA-Z]{2,6}$/", $sMail);
  }

  /**
   * Create a random charset.
   *
   * @static
   * @access public
   * @param integer $iLength length of the charset
   * @param boolean $bSpeakable charset is speakable by humans (every second char is a vocal)
   * @return string $sString created random charset
   *
   */
  public static function createRandomChar($iLength, $bSpeakable = false) {
    $sChars   = 'BCDFGHJKLMNPQRSTVWXZbcdfghjkmnpqrstvwxz';
    $sVocals  = 'AaEeiOoUuYy';
    $sNumbers = '123456789';

    $sString = '';

    if ($bSpeakable === false) {
      $sChars .= $sVocals . $sNumbers;
      for ($iI = 1; $iI <= $iLength; $iI++) {
        $iTemp = rand(0, strlen($sChars) - 1);
        $sString .= $sChars[$iTemp];
      }
    }
    else {
      $iI = 1;

      while ($iI < $iLength) {
        if ($iI % 5 == 0) {
          $sString .= $sNumbers[rand(0, strlen($sNumbers) - 1)];
          $iI++;
        }
        else {
          # Vocal
          $sString .= $sChars[rand(0, strlen($sChars) - 1)];

          # If we have more chars to put, use a vocal, otherwise use numbers to fill the string
          if ($iI < $iLength - 1)
            $sString .= $sVocals[rand(0, strlen($sVocals) - 1)];

          elseif ($iI < $iLength)
            $sString .= $sNumbers[rand(0, strlen($sNumbers) - 1)];

          else
            $iI--;

          $iI += 2;
        }
      }
    }

    return $sString;
  }

  /**
   * Create a simple link with provided params.
   *
   * @static
   * @access public
   * @param string $sUrl URL to create a link with
   * @param boolean $bExternal display a link to an external / absolute URL?
   * @return string HTML code with anchor
   *
   */
  public static function createLinkTo($sUrl, $bExternal = false) {
    return  $bExternal === true ?
            '<a href="' . $sUrl . '" rel="external">' . $sUrl . '</a>' :
            '<a href="' . WEBSITE_URL . '/' . $sUrl . '">' . WEBSITE_URL . '/' . $sUrl . '</a>';
  }

  /**
   * Return the URL of the user avatar.
   *
   * @static
   * @access public
   * @param mixed $mSize avatar size
   * @param integer $iUserId user ID
   * @param string $sEmail email address to search gravatar for
   * @param boolean $bUseGravatar do we want to use gravatar?
   * @return string URL of the avatar
   *
   */
  public static function getAvatar($mSize, $iUserId, $sEmail, $bUseGravatar = false) {
    $sFilePath = Helper::removeSlash(PATH_UPLOAD . '/users/' . $mSize . '/' . $iUserId);

    if ($bUseGravatar === false && file_exists($sFilePath . '.jpg'))
      return '/' . $sFilePath . '.jpg';

    elseif ($bUseGravatar === false && file_exists($sFilePath . '.png'))
      return '/' . $sFilePath . '.png';

    elseif ($bUseGravatar === false && file_exists($sFilePath . '.gif'))
      return '/' . $sFilePath . '.gif';

    else {
      if (!is_int($mSize)) {
        if ($mSize == 'popup')
          $mSize = POPUP_DEFAULT_X;

        else if ($mSize == 'thumbnail')
          $mSize = THUMB_DEFAULT_X;
      }

      # @todo check if Gravatar always gets an integer size
      # Bugfix: Make sure, that user wants to show his Gravatar and system url does not match with user ones.
      $sEmail = $bUseGravatar === true ? $sEmail : md5(WEBSITE_MAIL_NOREPLY);
      return 'http://www.gravatar.com/avatar/' . md5($sEmail) . '.jpg?s=' . $mSize . '&d=mm';
    }
  }

  /**
   * Add the avatar_* entries to $aData
   *
   * @static
   * @access public
   * @param array $aData array of user
   * @param integer $iUserId user ID
   * @param string $sEmail email address to search gravatar for
   * @param boolean $bUseGravatar do we want to use gravatar?
   * @param string $sPrefix optional prefix to prepend to keys
   * @return array $aData with all avatarURLs added
   *
   */
  public static function createAvatarURLs(&$aData, $iUserId, $sEmail, $bUseGravatar = false, $sPrefix = '') {
    $aData[$sPrefix . 'avatar_32']        = Helper::getAvatar(32, $iUserId, $sEmail, $bUseGravatar);
    $aData[$sPrefix . 'avatar_64']        = Helper::getAvatar(64, $iUserId, $sEmail, $bUseGravatar);
    $aData[$sPrefix . 'avatar_100']       = Helper::getAvatar(100, $iUserId, $sEmail, $bUseGravatar);
    $aData[$sPrefix . 'avatar_thumbnail'] = Helper::getAvatar('thumbnail', $iUserId, $sEmail, $bUseGravatar);
    $aData[$sPrefix . 'avatar_popup']     = Helper::getAvatar('popup', $iUserId, $sEmail, $bUseGravatar);

    return $aData;
  }

  /**
   * Count the file size.
   *
   * @static
   * @access public
   * @param string $sPath path of the file
   * @return int size of the file in byte
   * @todo write test
   *
   */
  public static function getFileSize($sPath) {
    $iSize = @filesize( ACTIVE_TEST ? $sPath : Helper::removeSlash($sPath) );

    return $iSize === false ? -1 : $iSize;
  }

  /**
   * Print a file size in a nice way.
   *
   * @static
   * @access public
   * @param int $iSize size of file in byte
   * @return string size of the file plus hardcoded ending
   * @todo write test
   *
   */
  public static function fileSizeToString($iSize) {
    if ($iSize > 1024 && $iSize < 1048576)
      return round(($iSize / 1024), 2) . ' KB';

    elseif ($iSize >= 1048576 && $iSize < 1073741824)
      return round(($iSize / 1048576), 2) . ' MB';

    elseif ($iSize >= 1073741824)
      return round(($iSize / 1073741824), 2) . ' GB';

    else
      return round($iSize, 2) . ' Byte';
  }

  /**
   * Check the input to avoid XSS and SQL injections.
   *
   * @static
   * @access public
   * @param string $sStr string to check
   * @param boolean $bDisableHTML remove HTML code
   * @return string cleaned input
   *
   */
  public static function formatInput($sStr, $bDisableHTML = true) {
    try {
      if (!is_string($sStr) && !is_int($sStr) && $bDisableHTML === true)
        throw new AdvancedException('### Input \'' . $sStr . '\' does not seem valid. ###');

      if ($bDisableHTML === true)
        $sStr = htmlspecialchars($sStr);
    }
    catch (AdvancedException $e) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      exit($e->getMessage());
    }

		# Bugfix: Remove TinyMCE crap at URLs
    $sStr = str_replace('\"', "'", $sStr);
    $sStr = str_replace("\'", "'", $sStr);

    # Remove multiple spaces and newlines (3+)
    $sStr = preg_replace('/\s(\s)\s+/', '$1$1', trim($sStr));

    # Fix quotes to avoid problems with inputs
		return $bDisableHTML === true ? str_replace('"', "&quot;", $sStr) : $sStr;
  }

  /**
   * Format HTML output .
   *
   * If the "Bbcode" plugin is enabled, load plugin do some advanced work.
   *
   * @static
   * @access public
   * @param string $sStr string to format
   * @param string $sHighlight string to highlight
   * @param boolean $bFormat format this field using ContentDisplayPlugins?
   * @return string $sStr formatted string
   * @see vendor/candycms/core/Bbcode/Bbcode.controller.php
   * @todo fix test; this one fails
   *
   */
  public static function formatOutput(&$sStr, $sHighlight = '', $bFormat = false) {
    if ($sHighlight)
      $sStr = str_ireplace(urldecode($sHighlight), '<mark>' . urldecode($sHighlight) . '</mark>', $sStr);

    if ($bFormat) {
      $oPluginManager = PluginManager::getInstance();
      $sStr = $oPluginManager->runContentDisplayPlugins($sStr);
    }

    return $sStr;
  }

  /**
   * Fetch the last entry from database.
   *
   * @static
   * @access public
   * @param string $sTable table to fetch data from
   * @return integer latest ID
   *
   */
  public static function getLastEntry($sTable) {
    try {
      $sModel = Main::__autoload('Main', true);
      $oDb    = $sModel::connectToDatabase();

      $oQuery = $oDb->query("SELECT id FROM " . SQL_PREFIX . $sTable . " ORDER BY id DESC LIMIT 1");
      $aRow   = $oQuery->fetch();

      return (int) $aRow['id'];
    }
    catch (AdvancedException $e) {
      AdvancedException::reportBoth(__METHOD__ . ' - ' . $e->getMessage());
      exit('SQL error.');
    }
  }

  /**
   * Replace non alphachars with predefined values.
   *
   * @static
   * @access public
   * @param string $sStr string to replace chars
   * @return string string with formatted chars
   *
   */
  public static function replaceNonAlphachars($sStr) {
    $sStr = str_replace('"', '', $sStr);
    $sStr = str_replace('Ä', 'Ae', $sStr);
    $sStr = str_replace('ä', 'ae', $sStr);
    $sStr = str_replace('Ü', 'Ue', $sStr);
    $sStr = str_replace('ü', 'ue', $sStr);
    $sStr = str_replace('Ö', 'Oe', $sStr);
    $sStr = str_replace('ö', 'oe', $sStr);
    $sStr = str_replace('ß', 'ss', $sStr);

    # Remove non alpha chars exept the needed dot
    $sStr = preg_replace("/[^a-zA-Z0-9\.\s]/", '', $sStr);

    # Remove spaces
    $sStr = str_replace(' ', '_', $sStr);

    return $sStr;
  }

  /**
   * Removes first slash at dirs.
   *
   * @static
   * @access public
   * @param string $sStr
   * @return string without slash
   *
   */
  public static function removeSlash($sStr) {
    return substr($sStr, 0, 1) == '/' ? substr($sStr, 1) : $sStr;
  }

  /**
   * Adds slash for dirs.
   *
   * @static
   * @access public
   * @param string $sStr
   * @return string with slash
   *
   */
  public static function addSlash($sStr) {
    return substr($sStr, 0, 1) == '/' ? $sStr : '/' . $sStr;
  }

  /**
   * Pluralize a string.
   *
   * Note that this is just a rudimentary funtion. F.e. "boy" will not be pluralized correctly.
   * Simple stuff will however work. F.e. 'log' becomes 'logs', 'kiss' will become 'kisses', ...
   *
   * @static
   * @access public
   * @param string $sStr
   * @return string pluralized string
   *
   */
  public static function pluralize($sStr) {
    if ($sStr == 'rss')
      return $sStr;

    elseif (substr($sStr, -1) == 'h' || substr($sStr, -2) == 'ss' || substr($sStr, -1) == 'o')
      return $sStr . 'es';

    elseif (substr($sStr, -1) == 's')
      return $sStr;

    elseif (substr($sStr, -1) == 'y')
      return substr($sStr, 0, -1) . 'ies';

    else
      return $sStr . 's';
  }

  /**
   * Singleize a string.
   *
   * Note that this is just a rudimentary funtion. F.e. "phase" and "boy" will not be pluralized corrctly.
   *
   * @static
   * @access public
   * @param string $sStr
   * @return string singleize string
   * @see vendor/candycms/core/controllers/Main.controller.php
   *
   */
  public static function singleize($sStr) {
    if (substr($sStr, -3) == 'ies')
      return substr($sStr, 0, -3) . 'y';

    elseif (substr($sStr, -2) == 'es')
      return substr($sStr, 0, -2);

    elseif (substr($sStr, -1) == 's' && substr($sStr, -2) !== 'ss')
      return substr($sStr, 0, -1);

    else
      return $sStr;
  }

  /**
   * Recursively replace one array with values from a second array.
   *
   * @static
   * @access public
   * @param array $aAr1 this is the target array
   * @param array $aAr2 all values from $aAr1 will be replaced with values from this array
   * @return array updated data
   *
   */
  public static function recursiveOnewayArrayReplace(&$aAr1, &$aAr2) {
    foreach ($aAr2 as $sKey => &$mValue) {
      if (isset($aAr2[$sKey])) {
        if (is_array($mValue))
          self::recursiveOnewayArrayReplace($aAr1[$sKey], $aAr2[$sKey]);
        else
          $aAr1[$sKey] = $aAr2[$sKey];
      }
    }

    return $aAr1;
  }

  /**
   * Get languages from app/languages folder.
   *
   * @static
   * @access public
   * @return array $aLanguages array with our languages
   * @todo test cases
   *
   */
  public static function getLanguages() {
    $aLanguages = array();
    $oPathDir = opendir(PATH_STANDARD . '/app/languages');

    while ($sFile = readdir($oPathDir)) {
      # Skip extra german languages.
      if (substr($sFile, 0, 1) == '.' || substr($sFile, 0, 3) == 'de_')
        continue;

      array_push($aLanguages, substr($sFile, 0, 2));
    }

    closedir($oPathDir);

    return $aLanguages;
  }

  /**
   * Try to compile the less stylesheet
   *
   * @static
   * @access public
   * @param string $sSource the less file
   * @param string $sOutput the target output file
   * @param bool $bCompressed whether to use the compressed output mode
   * @todo test cases
   *
   */
  public static function compileStylesheet($sSource, $sOutput, $bCompressed = true) {
    if (file_exists($sSource)) {
      $mCache = $sSource;
      Cache::isCachedAndLoad($sOutput, $mCache);

      $oLessc = new lessc();
      $bCompressed ? $oLessc->setFormatter('compressed') : $oLessc->setFormatter('classic');

      $aNewCache = $oLessc->cachedCompile($mCache);

      if (!is_array($mCache) || $aNewCache['updated'] > $mCache['updated']) {
        # save the compiled css
        if (!(file_put_contents($sOutput, $aNewCache['compiled']) === false))
          Cache::save($sOutput, $aNewCache);
      }
    }
  }
}
