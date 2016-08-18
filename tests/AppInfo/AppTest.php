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
use OCA\NextNotes\Tests\TestCase;

/**
 * Class AppTest
 *
 * @group DB
 * @package OCA\NextNotes\Tests\AppInfo
 */
class AppTest extends TestCase {

	/**
	 * Tests the NavigationEntry registration
	 */
	public function testNavigationEntry() {
		$navigationManager = \OC::$server->getNavigationManager();
		$navigationManager->clear();
		$this->assertEmpty($navigationManager->getAll());
		require '../appinfo/app.php';
		// Test whether the navigation entry got added
		$this->assertCount(1, $navigationManager->getAll());
	}

	/**
	 * Tests the registration of the hooks
	 */
	public function testRegisterHooks(){
		\OC_Hook::clear();
		$this->assertEmpty(\OC_Hook::getHooks());
		require '../appinfo/app.php';
		$this->assertCount(2, \OC_Hook::getHooks()['OC_User']);
		$this->assertCount(2, \OC_Hook::getHooks()['OC_User']['post_deleteUser']);
	}
}