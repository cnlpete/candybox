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

//use candyCMS\Core\Helpers\Helper;

class Sample extends \candyCMS\core\Controllers\Main {

  /**
   * Return the content.
   *
   * @access protected
   * @return string example content.
   *
   */
  protected function _show() {
    $oTemplate =  $this->oSmarty->getTemplate($this->_sController, 'show');
    $this->oSmarty->setTemplateDir($oTemplate);

    $this->setTitle('Sample extension');

    return $this->oSmarty->fetch($oTemplate, UNIQUE_ID);
  }

  /**
   *
   * @access protected
   * @param string $sTemplateName name of form template
   * @param string $sTitle title to show
   *
   */
  protected function _showFormTemplate($sTemplateName = '_form', $sTitle = '') {

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