<?php

/**
 * This plugin adds javscript code to make some textbox into a tinymce instance.
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

final class TinyMCE {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'TinyMCE';

  /**
   * Show the (cached) tinymce javascript code.
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

    $sCacheId = WEBSITE_MODE . '|layout|' . WEBSITE_LOCALE . '|' . self::IDENTIFIER . '|';

    return $oSmarty->fetch($sTemplateFile, $sCacheId);
  }
}
