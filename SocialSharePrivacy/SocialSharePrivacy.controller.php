<?php

/**
 * This plugins shows sharing options with a two-click-option to save the users privacy.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.1.1
 * @see http://www.heise.de/extras/socialshareprivacy/
 *
 */

namespace candyCMS\Plugins;

use candyCMS\Core\Helpers\Helper;
use candyCMS\Core\Helpers\SmartySingleton;

final class SocialSharePrivacy {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'SocialSharePrivacy';

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
    $sTemplateDir   = Helper::getPluginTemplateDir(self::IDENTIFIER, 'show');
    $sTemplateFile  = Helper::getTemplateType($sTemplateDir, 'show');

    $oSmarty = SmartySingleton::getInstance();
    $oSmarty->setTemplateDir($sTemplateDir);
    $oSmarty->setCaching(SmartySingleton::CACHING_LIFETIME_SAVED);

    return $oSmarty->fetch($sTemplateFile, WEBSITE_MODE . '|layout|' . WEBSITE_LOCALE . '|' . self::IDENTIFIER);
  }
}
