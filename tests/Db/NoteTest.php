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

namespace OCA\NextNotes\Tests\Db;

use OCA\NextNotes\Db\Note;
use OCA\NextNotes\Tests\TestCase;

class NoteTest extends TestCase {

	public function testSerialize() {
		$note = new Note();
		$note->setId(3);
		$note->setTitle('Title');
		$note->setContent('Content');
		$note->setUserId('john');
		$this->assertEquals([
			'id' => 3,
			'title' => 'Title',
			'content' => 'Content'
		], $note->jsonSerialize());
	}
}