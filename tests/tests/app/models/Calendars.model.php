<?php

/**
 * PHP unit tests
 *
 * @link http://github.com/marcoraddatz/candyCMS
 * @author Marco Raddatz <http://marcoraddatz.com>
 * @license MIT
 * @since 2.0
 *
 */

require_once PATH_STANDARD . '/vendor/candyCMS/core/models/Calendars.model.php';

use \CandyCMS\Core\Models\Calendars;

class UnitTestOfCalendarModel extends CandyUnitTest {

  function setUp() {

    $this->aRequest = array(
        'title' => 'Title',
        'content' => 'Content',
        'start_date' => '0000-00-00',
        'end_date' => '0000-00-00',
        'section' => 'calendar',
        'controller'  => 'calendars',
        'language'    => 'en');

    $this->oObject = new Calendars($this->aRequest, $this->aSession);
  }

  function testCreate() {
    $this->assertTrue($this->oObject->create());

    $this->iLastInsertId = (int) Calendars::getLastInsertId();
    $this->assertIsA($this->iLastInsertId, 'integer', 'Calendar #' . $this->iLastInsertId . ' created.');
  }

  function testGetData() {
    $this->assertIsA($this->oObject->getData(0), 'array');
    $this->assertIsA($this->oObject->getData(), 'array');

    // no action or id...
    $aData = $this->oObject->getData();
    $this->assertIsA($aData, 'array');
    $this->assertEqual(count($aData), 1);

    // with id
    $aData = $this->oObject->getData(0);
    $this->assertIsA($aData, 'array');
    $this->assertEqual(count($aData), 1);

    // archive ...
    $this->aRequest = array(
      'controller'  => 'calendars',
      'action'    => 'archive');
    $this->oObject = new Calendars($this->aRequest, $this->aSession);
    $aData = $this->oObject->getData();
    $this->assertIsA($aData, 'array');
    // no entries for current year
    $this->assertEqual(count($aData), 0);

    // ical feed ...
    $this->aRequest = array(
      'controller'  => 'calendars',
      'action'    => 'icalfeed');
    $this->oObject = new Calendars($this->aRequest, $this->aSession);
    $aData = $this->oObject->getData();
    $this->assertIsA($aData, 'array');
    $this->assertNotNull($aData['January2020']);
    $this->assertNotNull($aData['January2000']);
 }

  function testUpdate() {
    $this->assertTrue($this->oObject->update($this->iLastInsertId), 'Calendar #' . $this->iLastInsertId . ' updated.');
  }

  function testDestroy() {
    $this->assertTrue($this->oObject->destroy($this->iLastInsertId), 'Calendar #' . $this->iLastInsertId . ' destroyed.');
  }
}