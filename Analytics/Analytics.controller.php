<?php

/**
 * Cache analytics code.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\SmartySingleton;

final class Analytics {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'Analytics';

  /**
   * @final
   * @access public
   * @param array $aRequest
   * @param array $aSession
   * @return string HTML content
   *
   */
  public final function show(&$aRequest, &$aSession) {
    $sTemplateDir   = Helper::getPluginTemplateDir(self::IDENTIFIER, 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->setTemplateDir($sTemplateDir);
    $oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    $sCacheId = WEBSITE_MODE . '|plugins|' . WEBSITE_LOCALE . '|' . self::IDENTIFIER;
    if (!$oSmarty->isCached($sTemplateFile, $sCacheId)) {
      $oSmarty->assign('WEBSITE_MODE', WEBSITE_MODE);
      $oSmarty->assign('PLUGIN_ANALYTICS_TRACKING_CODE', PLUGIN_ANALYTICS_TRACKING_CODE);
    }

    return $oSmarty->fetch($sTemplateFile, $sCacheId);
  }
}