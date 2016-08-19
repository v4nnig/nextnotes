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
use OCA\NextNotes\Db\NoteMapper;

class NoteMapperTest extends MapperTestUtility {

	private $mapper;

	private $notes;

	private $twoRows;

	private $userId = 'john';

	protected function setUp(){
		parent::setUp();
		$this->mapper = new NoteMapper($this->db);
		// create mock notes
		$note1 = new Note();
		$note2 = new Note();
		$this->notes = [$note1, $note2];
		$this->twoRows = [
			['id' => $this->notes[0]->getId()],
			['id' => $this->notes[1]->getId()]
		];
	}

	public function testFind(){
		$id = 3;
		$rows = [['id' => $this->notes[0]->getId()]];
		$sql = 'SELECT * FROM *PREFIX*nextnotes_notes WHERE id = ? AND user_id = ?';
		$this->setMapperResult($sql, [$id, $this->userId], $rows);
		$result = $this->mapper->find($id, $this->userId);
		$this->assertEquals($this->notes[0], $result);
	}

	/**
	 * @expectedException \OCP\AppFramework\Db\DoesNotExistException
	 */
	public function testFindNotFound(){
		$id = 3;
		$sql = 'SELECT * FROM *PREFIX*nextnotes_notes WHERE id = ? AND user_id = ?';
		$this->setMapperResult($sql, [$id, $this->userId]);
		$this->mapper->find($id, $this->userId);
	}

	/**
	 * @expectedException \OCP\AppFramework\Db\MultipleObjectsReturnedException
	 */
	public function testFindMoreThanOneResultFound(){
		$id = 3;
		$rows = $this->twoRows;
		$sql = 'SELECT * FROM *PREFIX*nextnotes_notes WHERE id = ? AND user_id = ?';
		$this->setMapperResult($sql, [$id, $this->userId], $rows);
		$this->mapper->find($id, $this->userId);
	}

	public function testFindAll(){
		$rows = $this->twoRows;
		$sql = 'SELECT * FROM *PREFIX*nextnotes_notes WHERE user_id = ?';
		$this->setMapperResult($sql, [$this->userId], $rows);
		$result = $this->mapper->findAll($this->userId);
		$this->assertEquals($this->notes, $result);
	}

	public function testFulltextSearchWithoutTagFilter(){
		$term = 'Text';
		$filter = 'LOWER(n.content) LIKE LOWER(\'%'.$term.'%\')';
		$rows = $this->twoRows;
		$sql = 'SELECT DISTINCT n.id, n.title, n.user_id, n.content FROM *PREFIX*nextnotes_notes AS n WHERE n.user_id = ? AND '.
			$filter;
		$this->setMapperResult($sql, [$this->userId], $rows);
		$result = $this->mapper->fulltextSearchWithoutTagFilter([$term], $this->userId);
		$this->assertEquals($this->notes, $result);
	}

	public function testTagSearch(){
		$tag = 'Tag';
		$filter = 'LOWER(c.category) LIKE LOWER(\''.$tag.'\')';
		$rows = $this->twoRows;
		$sql = 'SELECT DISTINCT n.id, n.title, n.user_id, n.content FROM *PREFIX*nextnotes_notes AS n, *PREFIX*vcategory AS c, *PREFIX*vcategory_to_object AS o'.
			' WHERE n.user_id = ? AND n.id = o.objid AND o.type = ? AND o.categoryid = c.id AND c.type = ? AND c.uid = ? AND '.
			$filter;
		$this->setMapperResult($sql, [$this->userId], $rows);
		$result = $this->mapper->tagSearch([$tag], $this->userId);
		$this->assertEquals($this->notes, $result);
	}

	public function testFulltextSearchWithTagFilter(){
		$term = 'Text';
		$tag = 'Tag';
		$filter = '';
		$filter .= 'LOWER(n.content) LIKE LOWER(\'%'.$term.'%\') AND ';
		$filter .= 'LOWER(c.category) LIKE LOWER(\''.$tag.'\')';
		$rows = $this->twoRows;
		$sql = 'SELECT DISTINCT n.id, n.title, n.user_id, n.content FROM *PREFIX*nextnotes_notes AS n, *PREFIX*vcategory AS c, *PREFIX*vcategory_to_object AS o'.
			' WHERE n.user_id = ? AND n.id = o.objid AND o.type = ? AND o.categoryid = c.id AND c.type = ? AND c.uid = ? AND '.
			$filter;
		$this->setMapperResult($sql, [$this->userId], $rows);
		$result = $this->mapper->fulltextSearchWithTagFilter([$term], [$tag], $this->userId);
		$this->assertEquals($this->notes, $result);
	}

	public function testDeleteAllForUser(){
		$sql = 'DELETE FROM *PREFIX*nextnotes_notes WHERE user_id = ?';
		$this->setMapperResult($sql, [$this->userId]);
		$this->mapper->deleteAllForUser($this->userId);
	}
}