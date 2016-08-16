<?php
/**
 * nextCloud - nextnotes
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Janis Koehr <janiskoehr@icloud.com>
 * @copyright Janis Koehr 2016
 */

namespace OCA\NextNotes\Controller;

use PHPUnit_Framework_TestCase;

use OCP\AppFramework\Http;

use OCA\NextNotes\Service\NotFoundException;


class TagControllerTest extends PHPUnit_Framework_TestCase {
	protected $controller;
	protected $service;
	protected $userId = 'john';
	protected $request;

	public function setUp() {
		$this->request = $this->getMockBuilder('OCP\IRequest')->getMock();
		$this->service = $this->getMockBuilder('OCA\NextNotes\Service\TagService')
			->disableOriginalConstructor()
			->getMock();
		$this->controller = new TagController(
			'nextnotes', $this->request, $this->service, $this->userId
		);
	}
}