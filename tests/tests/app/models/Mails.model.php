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

require_once PATH_STANDARD . '/vendor/candyCMS/core/models/Mails.model.php';

use \CandyCMS\Core\Models\Mails;

class UnitTestOfMailsModel extends CandyUnitTest {

  function setUp() {
    $this->aRequest = array(
        'mails' => array(
          'email'     => 'Title',
          'subject'    => 'Teaser',
          'content'   => 'Content'),
        'controller'=> 'mails');

    $this->oObject = new Mails($this->aRequest, $this->aSession);
  }

	function tearDown() {
		parent::tearDown();
	}

  function testCreate() {
    $this->assertTrue($this->oObject->create('subject',
            'message',
            'Candy Test Receiver',
            WEBSITE_MAIL,
            'Candy Test Sender',
            WEBSITE_MAIL_NOREPLY,
            '',
            false));

    //empty mail body will fail
    $this->assertFalse($this->oObject->create('',
            '',
            'Candy Test Receiver',
            WEBSITE_MAIL . 'nomail',
            'Candy Test Sender',
            WEBSITE_MAIL_NOREPLY . 'nomail',
            '',
            true));

    $this->iLastInsertId = (int) Mails::getLastInsertId();
    $this->assertIsA($this->iLastInsertId, 'integer');
  }

  function testGetData() {
    $this->assertIsA($this->oObject->getOverview(), 'array');
  }

  function testResend() {
    //resending will still fail, since there is no mail body
    $this->assertFalse($this->oObject->resend($this->iLastInsertId));
  }

  function testDestroy() {
    $this->assertTrue($this->oObject->destroy($this->iLastInsertId));
  }
}