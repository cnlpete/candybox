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
   * NOTE: This test will fail right now because of missing samples.csv!
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
      'Samples'  => array(
        'author_id'         => 1,
        'title'                 => 'Title',
        'content'               => 'Content',
        'date'                  => date('Y-m-d H:i:s', time())
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
    $aData = $this->oObject->getId(1);
    $this->assertTrue(is_array($aData));
  }
}