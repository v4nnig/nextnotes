<?php
/**
 * @copyright Copyright (c) 2016, nextcloud GmbH
 *
 * @author Joas Schilling <coding@schilljs.com>
 * @author Janis Koehr <janiskoehr@icloud.com>
 *
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\NextNotes\Tests\AppInfo;
use OCA\NextNotes\AppInfo\Application;
use OCA\NextNotes\Tests\TestCase;
/**
 * Class ApplicationTest
 *
 * @group DB
 * @package OCA\NextNotes\Tests\AppInfo
 */
class ApplicationTest extends TestCase {

	/** @var \OCA\NextNotes\AppInfo\Application */
	protected $app;

	/** @var \OCP\AppFramework\IAppContainer */
	protected $container;

	protected function setUp() {
		parent::setUp();
		$this->app = new Application();
		$this->container = $this->app->getContainer();
	}

	public function testContainerAppName() {
		$this->app = new Application();
		$this->assertEquals('nextnotes', $this->container->getAppName());
	}

	public function queryData() {
		return array(
			array('NoteMapper', 'OCA\NextNotes\Db\NoteMapper'),
			array('OCP\IL10N', 'OCP\IL10N'),
			array('Tagger', 'OCP\ITags'),
			array('TagService', 'OCA\NextNotes\Service\TagService'),
			array('NoteService', 'OCA\NextNotes\Service\NoteService'),
			array('NoteController', 'OCP\AppFramework\Controller'),
			array('TagController', 'OCP\AppFramework\Controller')
		);
	}
	/**
	 * @dataProvider queryData
	 * @param string $service
	 * @param string $expected
	 */
	public function testContainerQuery($service, $expected) {
		$this->assertTrue($this->container->query($service) instanceof $expected);
	}
}