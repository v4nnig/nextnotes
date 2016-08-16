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
namespace OCA\NextNotes\Service;


use PHPUnit_Framework_TestCase;

use OCP\AppFramework\Db\DoesNotExistException;


class TagServiceTest extends PHPUnit_Framework_TestCase {

	private $tagM;
	private $service;
	private $userId = 'john';

	public function setUp() {
		$this->tagM = $this->getMockBuilder('\OCP\ITagManager')
			->disableOriginalConstructor()
			->getMock();
		$this->service = new TagService($this->tagM);
	}
}