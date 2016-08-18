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
namespace OCA\NextNotes\Tests\Service;


use OCA\NextNotes\Service\TagService;
use OCA\NextNotes\Tests\TestCase;



class TagServiceTest extends TestCase {

	private $tagM;
	private $service;
	private $logger;
	private $userId = 'john';

	public function setUp() {
		$this->logger = $this->getMockBuilder('OCP\ILogger')
			->disableOriginalConstructor()
			->getMock();
		$this->tagM = $this->getMockBuilder('\OCP\ITagManager')
			->disableOriginalConstructor()
			->getMock();
		$this->service = new TagService($this->tagM, $this->logger);
	}
}