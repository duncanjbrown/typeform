<?php

use PHPUnit\Framework\TestCase;
use WATR\Typeform;

class TypeformTest extends TestCase
{
	function setUp() {
		\VCR\VCR::configure()
			->enableRequestMatchers(['url', 'query_string']);
	}

	function tearDown() {
		\VCR\VCR::eject();
	}

	function testGetResponses() {
		$typeform = new Typeform('FAKE_API_KEY');
		\VCR\VCR::insertCassette('typeform_get_responses.yml');

		$response = $typeform->getResponses('zOwLuu');

		$this->assertTrue(is_array($response));
	}
}
