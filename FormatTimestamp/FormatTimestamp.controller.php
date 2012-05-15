<?php

/**
 * This plugin rewrites the standard date into a nicer "today" / "yesterday" format.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 1.0
 *
 *
 */

namespace CandyCMS\Plugins;

use CandyCMS\Core\Helpers\I18n;

final class FormatTimestamp {

  /**
   * Format timestamp
   *
   * @final
   * @access private
   * @param integer $iTime
   * @param integer $iOptions
   * @return string
   *
   */
  private final function _setDate($iTime, $iOptions) {
    if(!$iTime)
      return;

    $sTime = strftime(I18n::get('global.time.format.time'), $iTime);

    if(date('Ymd', $iTime) == date('Ymd', time()))
      $sDay = I18n::get('global.today');

    elseif(date('Ymd', $iTime) == date('Ymd', (time()-60*60*24)))
      $sDay = I18n::get('global.yesterday');

    else
      $sDay = strftime(I18n::get('global.time.format.date'), $iTime);

    if($iOptions == 1)
      return $sDay;

    elseif($iOptions == 2)
      return $sTime;

    else
      return $sDay . ', ' . $sTime;
  }

  /**
   * @final
   * @access public
   * @param integer $iTime
   * @param integer $iOptions
   * @return string
   *
   */
  public final function getDate($iTime, $iOptions) {
    return $this->_setDate($iTime, $iOptions);
  }
}