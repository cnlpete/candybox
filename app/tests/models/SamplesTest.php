<?php

/**
 * This is an example how to test a model extension.
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 4.0.1
 *
 */

namespace candyCMS\Core\Models;

if (!defined('PATH_STANDARD'))
  define ('PATH_STANDARD', dirname(__FILE__) . '/../../..');

require_once PATH_STANDARD . '/vendor/candycms/tests/CandyModelTestCase.php';
require_once PATH_STANDARD . '/app/models/Samples.model.php';

/**
 * Class SamplesTest
 * @package candyCMS\Core\Models
 *
 */
class SamplesTest extends CandyModelTestCase {

  /**
   * Insert our SQL data from the fixtures we entered below.
   * Make sure that this data is saved as CSV!
   *
   * @access public
   * @return PHPUnit_Extensions_Database_DataSet_IDataSet
   *
   */
  public function getDataSet() {
    $oDataSet = new \PHPUnit_Extensions_Database_DataSet_CsvDataSet();
    $oDataSet->addTable('samples', dirname(__FILE__) . '/../fixtures/data/samples.csv');
    return $oDataSet;
  }

  /**
   * Sets up the fixture, for example, opens a network connection.
   * This method is called before a test is executed.
   *
   * @access protected
   *
   */
  protected function setUp() {
    parent::setUp();

    $this->aRequest = array(
      'controller' => 'samples',
      'samples'  => array(
        'author_id' => 1,
        'title'     => 'Title',
        'content'   => 'Content',
        'date'      => date('Y-m-d H:i:s', time())
      )
    );

    $this->oObject = new \candyCMS\Models\Samples($this->aRequest, $this->aSession);
  }

  /**
   *
   * @covers candyCMS\Models\Samples::getId
   *
   */
  public function testGetId() {
    $bStatus = $this->oObject->getId(1);
    $this->assertTrue($bStatus);
  }

  /**
   *
   * We overwrite existing methods because they have no function yet.
   * @covers candyCMS\Models\Samples::create
   *
   */
  public function testCreate() {
    $this->markTestIncomplete();
  }

  /**
   *
   * @covers candyCMS\Models\Samples::update
   *
   */
  public function testUpdate() {
    $this->markTestIncomplete();
  }

  /**
   *
   * @covers candyCMS\Models\Samples::destroy
   *
   */
  public function testDestroy() {
    $this->markTestIncomplete();
  }
}