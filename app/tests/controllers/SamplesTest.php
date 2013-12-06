<?php

/**
 * This is an example how to create an extension test.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.2
 *
 */

namespace candyCMS\Controllers;

require_once dirname(__FILE__) . '/../../../vendor/candycms/tests/CandyControllerTestCase.php';
require_once dirname(__FILE__) . '/../../controllers/Samples.controller.php';

/**
 * Class SamplesTest
 * @package candyCMS\Controllers
 *
 */
class SamplesTest extends \candyCMS\Core\Controllers\CandyControllerTestCase {

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   *
   * @access protected
   *
   */
  protected function setUp() {
    parent::setUp();

    $this->aSession['user'] = array(
        'email'       => '',
        'facebook_id' => '',
        'id'          => 0,
        'name'        => '',
        'surname'     => '',
        'password'    => '',
        'role'        => 0,
        'full_name'   => ''
    );

    $this->aRequest = array('controller' => 'sample');
    $this->oObject  = new Samples($this->aRequest, $this->aSession);
  }

  /**
   * Tears down the fixture, for example, closes a network connection.
   * This method is called after a test is executed.
   *
   * @access protected
   *
   */
  protected function tearDown() {
    parent::tearDown();
  }

  /**
   * Open an URL and search for defined text.
   *
   * @access public
   *
   */
  public function testShow() {
    $this->open(WEBSITE_URL . '/' . $this->aRequest['controller']);
    $this->verifyTextPresent('This is a sample extension.');
  }
}