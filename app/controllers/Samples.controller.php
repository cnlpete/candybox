<?php

/**
 * This is an example how to extend a core controller.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

namespace candyCMS\Controllers;

//use candyCMS\Core\Helpers\Helper;

/**
 * Class Samples
 * @package candyCMS\Controllers
 *
 */
class Samples extends \candyCMS\core\Controllers\Main {

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
   * @return null
   *
   */
  protected function _showFormTemplate($sTemplateName = '_form', $sTitle = '') {

  }

  /**
   *
   * @access protected
   * @return void
   *
   */
  protected function _create() {

  }

  /**
   *
   * @access protected
   * @return void
   *
   */
  protected function _update() {

  }

  /**
   *
   * @access protected
   * @return void
   *
   */
  protected function _destroy() {

  }
}