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

namespace CandyCMS\Plugins;

use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\I18n;
use CandyCMS\Core\Helpers\SmartySingleton;

final class Archive {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'archive';

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
    $sTemplateDir   = Helper::getPluginTemplateDir('archive', 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->setTemplateDir($sTemplateDir);
    $oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    $sCacheId = WEBSITE_MODE . '|blogs|' . WEBSITE_LOCALE . '|archive|' . substr(md5($aSession['user']['role']), 0 , 10);
    if (!$oSmarty->isCached($sTemplateFile, $sCacheId)) {

      $sBlogsModel = \CandyCMS\Core\Models\Main::__autoload('Blogs');
      $oModel = new $sBlogsModel($aRequest, $aSession);

      $aMonth = array();
      foreach ($oModel->getOverview(PLUGIN_ARCHIVE_LIMIT) as $aRow) {
        # Date format the month
        $sMonth = date('n', $aRow['date']['raw']);
        $sMonth = I18n::get('global.months.' . $sMonth) . ' ' . strftime('%Y', $aRow['date']['raw']);

        # Prepare array
        $aMonths[$sMonth][] = $aRow;
      }

      $oSmarty->assign('data', $aMonths);
    }

    return $oSmarty->fetch($sTemplateFile, $sCacheId);
  }
}
