<?php

/**
 * This plugin rewrites the standard date into a nicer "today" / "yesterday" format.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Hauke Schade <http://hauke-schade.de>
 * @license MIT
 * @since 2.1
 *
 */

namespace CandyCMS\Plugins;

use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\SmartySingleton;

final class FormatTimestamp {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'formattimestamp';

  /**
   * Show the (cached) javascript code, that enables the jQuery plugin.
   *
   * @final
   * @access public
   * @param array $aRequest
   * @param array $aSession
   * @return string HTML
   *
   */
  public final function show(&$aRequest, &$aSession) {
    $sTemplateDir   = Helper::getPluginTemplateDir('FormatTimestamp', 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->setTemplateDir($sTemplateDir);
    $oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    $sCacheId = WEBSITE_MODE . '|layout|' . WEBSITE_LOCALE . '|formattimestamp|';
    if (!$oSmarty->isCached($sTemplateFile, $sCacheId)) {
      # the jQuery.timeago plugin takes it range in milliseconds,
      # PLUGIN_FORMATTIMESTAMP_RANGE is in minutes and defaults to 3 days
      $iRange = 1000 * 60 * (defined('PLUGIN_FORMATTIMESTAMP_RANGE') ? PLUGIN_FORMATTIMESTAMP_RANGE : 4320);
      $oSmarty->assign('range', $iRange);
    }

    return $oSmarty->fetch($sTemplateFile, $sCacheId);
  }
}
