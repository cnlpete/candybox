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

namespace CandyCMS\Controllers;

require_once dirname(__FILE__) . '/../../../vendor/candyCMS/tests/CandyControllerTestCase.php';
require_once dirname(__FILE__) . '/../../extensions/controllers/Sample.controller.php';

/**
 * Test class for Helper.
 */
class SampleTest extends \CandyCMS\Core\Controllers\CandyControllerTestCase {

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   *
   * @access protected
   *
   */
  protected function setUp() {
    parent::setUp();

    $this->aRequest	= array('controller' => 'sample');
    $this->oObject  = new Sample($this->aRequest, $this->aSession);
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

  /**
   * Use candyCMS' main test cases.
   *
   * @access public
   *
   */
  public function testCreate() {
    $this->create($this->aRequest['controller']);
  }

  /**
   * Use candyCMS' main test cases.
   *
   * @access public
   *
   */
  public function testUpdate() {
    $this->update($this->aRequest['controller']);
  }

  /**
   * Use candyCMS' main test cases.
   *
   * @access public
   *
   */
  public function testDestroy() {
    $this->destroy($this->aRequest['controller']);
  }
}