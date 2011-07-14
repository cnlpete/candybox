<?php

/*
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
*/

final class Helper {

  public static final function successMessage($sMSG, $sRedirectTo = '') {
    $_SESSION['flash_message']['type']      = 'success';
    $_SESSION['flash_message']['message']   = $sMSG;
    $_SESSION['flash_message']['headline']  = '';
    $_SESSION['flash_message']['show']      = '0';

    if(!empty($sRedirectTo))
      Helper::redirectTo ($sRedirectTo);
  }

  public static final function errorMessage($sMSG, $sRedirectTo = '') {
    $_SESSION['flash_message']['type']      = 'error';
    $_SESSION['flash_message']['message']   = $sMSG;
    $_SESSION['flash_message']['headline']  = LANG_ERROR_GLOBAL;
    $_SESSION['flash_message']['show']		= '0';

    if(!empty($sRedirectTo))
      Helper::redirectTo ($sRedirectTo);
  }

  public static final function redirectTo($sURL) {
    header('Location:' . WEBSITE_URL . $sURL);
    die();
  }

  public static final function checkEmailAddress($sMail) {
    if (preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $sMail))
      return true;
    else # TODO: Redunant
      return false;
  }

  public final static function createRandomChar($iLength, $bIntegerOnly = false) {
    $sChars = 'ABCDEFGHJKLMNOPQRSTUVWXYZabcedfghijkmnopqrstuvwxyz123456789';

    if ($bIntegerOnly == true)
      $sChars = '0123456789';

    $sString = '';
    for ($iI = 1; $iI <= $iLength; $iI++) {
      $iTemp = rand(1, strlen($sChars));
      $iTemp--;
      $sString .= $sChars[$iTemp];
    }

    return $sString;
  }

	public final static function createLinkTo($sUrl, $bExternal = false) {
		if($bExternal == false)
			return '<a href=\'' . WEBSITE_URL . $sUrl . '\'>' . WEBSITE_URL . $sUrl . '</a>';
		else
			return '<a href=\'' . $sUrl . '\'>' . $sUrl . '</a>';
  }

  public final static function getAvatar($sPath, $sSize, $iUserId, $aGravatar = '') {
    if (!empty($aGravatar) && $aGravatar['use_gravatar'] == true) {
      if (!is_int($sSize))
        $sSize = POPUP_DEFAULT_X;

      $sMail = md5($aGravatar['email']);
      return 'http://www.gravatar.com/avatar/' . $sMail . '.jpg?s=' . $sSize .
        '&d=' . WEBSITE_CDN . '/public/images/missing_avatar.jpg';
    }
    else {
      $sFilePath = PATH_UPLOAD . '/' . $sPath . '/' . $sSize . '/' . $iUserId;

			if (is_file($sFilePath . '.jpg'))
        return WEBSITE_URL . '/' . $sFilePath . '.jpg';

      elseif (is_file($sFilePath . '.png'))
        return WEBSITE_URL . '/' . $sFilePath . '.png';

      elseif (is_file($sFilePath . '.gif'))
        return WEBSITE_URL . '/' . $sFilePath . '.gif';

      else
        return WEBSITE_CDN . '/public/images/missing_avatar.jpg';
    }
  }

  public final static function getFileSize($sPath) {
    $iSize = filesize($sPath);

    if ($iSize > 1024 && $iSize < 1048576)
      $sReturn = round(($iSize / 1024), 2) . ' KB';

    elseif ($iSize >= 1048576 && $iSize < 1073741824)
      $sReturn = round(($iSize / 1048576), 2) . ' MB';

    elseif ($iSize >= 1073741824)
      $sReturn = round(($iSize / 1073741824), 2) . ' GB';

    else
      $sReturn = round($iSize, 2) . ' Byte';

    return $sReturn;
  }

  public final function getTemplateDir($sTemplate) {
    try {
      if (file_exists('public/skins/' . PATH_TPL . '/views/' . $sTemplate . '.tpl'))
        return 'public/skins/' . PATH_TPL . '/views/';

      elseif (file_exists('public/skins/_addons/views/' . $sTemplate . '.tpl'))
        return 'public/skins/_addons/views/';

      else {

        if (!file_exists('app/views/' . $sTemplate . '.tpl'))
          throw new AdvancedException(LANG_ERROR_GLOBAL_NO_TEMPLATE);

        else
          return 'app/views/';
      }
    }
    catch (Exception $e) {
      $e->getMessage();
    }
  }

  public final static function removeSlahes($sStr) {
    $sStr = str_replace('\&quot;', '"', $sStr);
    $sStr = str_replace('\"', '"', $sStr);
    $sStr = str_replace("\'", "'", $sStr);
    return $sStr;
  }

  public final static function formatInput($sStr, $bDisableHTML = true) {
    try {
      if (is_string($sStr) == false && is_int($sStr) == false && $bDisableHTML == true)
        throw new Exception('Input seems not valid.');

      if ($bDisableHTML == true)
        $sStr = htmlspecialchars($sStr);
    }
    catch (AdvancedException $e) {
      $oDb->rollBack();
      $e->getMessage();
      die();
    }

    return $sStr;
  }

  # Code for plugins
  public static final function formatTimestamp($iTime, $bDateOnly = false) {

    # Set active locale
    setlocale(LC_ALL, WEBSITE_LOCALE);

    if (class_exists('FormatTimestamp') == true) {
      $oDate = new FormatTimestamp();
      return $oDate->getDate($iTime, $bDateOnly);
    }
    else {

      if ($bDateOnly == true)
        return strftime(DEFAULT_DATE_FORMAT, $iTime);

      else
        return strftime(DEFAULT_DATE_FORMAT . DEFAULT_TIME_FORMAT, $iTime);
    }
  }

  public static final function formatOutput($sStr, $highlight = '') {
    $sStr = trim($sStr);
    $sStr = preg_replace('/\S{500}/', '\0 ', $sStr);

    # Remove Slashes
    $sStr = Helper::removeSlahes($sStr);

    # Highlight string
    if (!empty($highlight))
      $sStr = str_ireplace($highlight, '<mark>' . $highlight . '</mark>', $sStr);

    if (class_exists('Bbcode') == true) {
      $oBbcode = new Bbcode();
      return $oBbcode->getFormatedText($sStr);
    }
    else
      return $sStr;
  }

  public static function getLastEntry($sTable) {
    try {
      $oDb = new PDO('mysql:host=' . SQL_HOST . ';dbname=' . SQL_DB, SQL_USER, SQL_PASSWORD);
      $oDb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $oQuery = $oDb->query(" SELECT id FROM " . SQL_PREFIX . $sTable . " ORDER BY id DESC LIMIT 1");
      $aRow = $oQuery->fetch();
      return $aRow['id'];
    }
    catch (AdvancedException $e) {
      $oDb->rollBack();
    }
  }
}