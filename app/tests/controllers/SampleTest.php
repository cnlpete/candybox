<?php

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
   */
  protected function tearDown() {
    parent::tearDown();
  }

  /**
   *
   */
	public function testShow() {
    $this->open(WEBSITE_URL . '/' . $this->aRequest['controller']);
    $this->verifyTextPresent('This is a sample extension.');
	}

  /**
   *
   */
  public function testCreate() {
    $this->create($this->aRequest['controller']);
  }

  /**
   *
   */
  public function testUpdate() {
    $this->update($this->aRequest['controller']);
  }

  /**
   *
   */
  public function testDestroy() {
    $this->destroy($this->aRequest['controller']);
  }
}