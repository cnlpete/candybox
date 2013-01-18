<?php

/**
 * The archive plugin lists all blog entries by month and date.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.5
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\I18n;
use candyCMS\Core\Helpers\SmartySingleton;

final class Archive {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'Archive';

  /**
   * Show the (cached) archive.
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

    $sCacheId = WEBSITE_MODE . '|blogs|' . WEBSITE_LOCALE . '|' . self::IDENTIFIER . '|' .
            substr(md5($aSession['user']['role']), 0 , 10);

    if (!$oSmarty->isCached($sTemplateFile, $sCacheId)) {
      $sBlogsModel = \candyCMS\Core\Models\Main::__autoload('Blogs');
      $oModel = new $sBlogsModel($aRequest, $aSession);

      $aMonthNames  = array();
      $aMonths      = array();

      foreach ($oModel->getOverviewByMonth(defined('PLUGIN_ARCHIVE_RANGE') ? PLUGIN_ARCHIVE_RANGE : 12) as $aRow) {
        # Date format the month
        $sMonth = date('n', $aRow['date']['raw']);
        if (!isset($aMonthNames[$sMonth]))
          $aMonthNames[$sMonth] = I18n::get('global.months.' . $sMonth);

        $sMonth = $aMonthNames[$sMonth] . ' ' . strftime('%Y', $aRow['date']['raw']);

        # Prepare array
        $aMonths[$sMonth][] = $aRow;
      }

      $oSmarty->assign('data', $aMonths);
    }

    return $oSmarty->fetch($sTemplateFile, $sCacheId);
  }
}