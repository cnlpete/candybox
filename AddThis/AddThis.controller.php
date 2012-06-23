<?php

/**
 * This plugins shows sharing options from https://www.addthis.com.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.1.2
 * @see https://www.addthis.com
 *
 */

namespace CandyCMS\Plugins;

use CandyCMS\Core\Helpers\Helper;
use CandyCMS\Core\Helpers\SmartySingleton;

final class AddThis {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'addthis';

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
    $sTemplateDir   = Helper::getPluginTemplateDir('addthis', 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->setTemplateDir($sTemplateDir);
    $oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    $sCacheId = WEBSITE_MODE . '|layout|' . WEBSITE_LOCALE . '|' . IDENTIFIER . '|';

    return $oSmarty->fetch($sTemplateFile, $sCacheId);
  }
}
