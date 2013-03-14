<?php

/**
 * This is an example how to create a single extension.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Controllers;

use candyCMS\Core\Helpers\Helper;

class Sample extends \candyCMS\core\Controllers\Main {

  /**
   * Method to include the model files and start action beside the constructor.
   *
   * @access public
   *
   */
  public function __init() {
    require_once PATH_STANDARD . '/app/models/Sample.model.php';
    $this->_oModel = new \candyCMS\Models\Sample($this->_aRequest, $this->_aSession, $this->_aFile);
  }

  /**
   * Return the content.
   *
   * @access protected
   * @return string example content.
   *
   */
  protected function _show() {
    $sTemplateDir  = Helper::getTemplateDir('samples', 'show');
    $sTemplateFile = Helper::getTemplateType($sTemplateDir, 'show');
    $this->oSmarty->setTemplateDir($sTemplateDir);

    $this->setTitle('Sample extension');

    return $this->oSmarty->fetch($sTemplateFile, UNIQUE_ID);
  }

  /**
   *
   * @access protected
   * @param boolean $bUpdate
   *
   */
  protected function _showFormTemplate($bUpdate = true) {

  }

  /**
   *
   * @access protected
   *
   */
  protected function _create() {

  }

  /**
   *
   * @access protected
   *
   */
  protected function _update() {

  }

  /**
   *
   * @access protected
   *
   */
  protected function _destroy() {

  }
}