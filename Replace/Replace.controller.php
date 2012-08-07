<?php

/**
 * Replaces text. This can be either used to avoid bad words, SEO optimization or to display ads.
 * The config file must be placed under "app/config/Replace.yml".
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.1.5
 *
 */

namespace CandyCMS\Plugins;

final class Replace {

  /**
   * Identifier for Template Replacements
   *
   * @var constant
   *
   */
  const IDENTIFIER = 'Replace';

  /**
   * Replace the words.
   *
   * @final
   * @access public
   * @param array $aRequest
   * @param array $aSession
   * @param string $sHtml
   * @return string HTML
   * @todo exception instead of die();
   *
   */
  public final function replace(&$aRequest, &$aSession, $sHtml) {
    # Check for config file
    if (!isset($aSession['replace'])) {
      $sReplaceDataFile = PATH_STANDARD . '/app/config/Replace.yml';

      if (file_exists($sReplaceDataFile))
        $aSession['replace'] = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($sReplaceDataFile));

      else
        die('No replace config file.');
    }

    foreach ($aSession['replace'] as $sKey => $sValue)
      $sHtml = str_replace($sKey, $sValue, $sHtml);

    return $sHtml;
  }
}