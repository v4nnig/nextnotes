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
namespace OCA\NextNotes\Db;


use OCP\AppFramework\Db\Mapper;
use OCP\IDBConnection;

/**
 * Class NoteMapper
 * @package OCA\NextNotes\Db
 */
class NoteMapper extends Mapper {

    /**
     * NoteMapper constructor.
     * @param IDBConnection $db
     * @param tablename
     * @param Entity
     */
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'nextnotes_notes', '\OCA\NextNotes\Db\Note');
    }

    /**
     * Find Entity for the given id and userid.
     * @param $id
     * @param $userId
     * @return \OCP\AppFramework\Db\Entity as JSONSerializable
     */
    public function find($id, $userId) {
        $sql = 'SELECT * FROM *PREFIX*nextnotes_notes WHERE id = ? AND user_id = ?';
        return $this->findEntity($sql, [$id, $userId]);
    }

    /**
     * Find all entities for the given userid.
     * @param $userId
     * @return \OCP\AppFramework\Db\Entity as JSONSerializable
     */
    public function findAll($userId) {
        $sql = 'SELECT * FROM *PREFIX*nextnotes_notes WHERE user_id = ?';
        return $this->findEntities($sql, [$userId]);
    }

    /**
     * Fulltext search for the given array of search terms including the userid.
     * @param $terms
     * @param $userId
     * @return \OCP\AppFramework\Db\Entity as JSONSerializable
     */
    public function fulltextSearchWithoutTagFilter($terms, $userId){
        $filter = '';
        end($terms);
        $last = key($terms);
        foreach($terms as $key => $term){
            if($key === $last){
                $filter .= 'LOWER(n.content) LIKE LOWER(\'%'.$term.'%\')';
            }else{
                $filter .= 'LOWER(n.content) LIKE LOWER(\'%'.$term.'%\') AND ';
            }
        }
        $sql = 'SELECT DISTINCT n.id, n.title, n.user_id, n.content FROM *PREFIX*nextnotes_notes AS n WHERE n.user_id = ? AND '.
            $filter;

        return $this->findEntities($sql, [$userId]);
    }

    /**
     * Tag Search for the given array of tags including the userid.
     * @param $tags
     * @param $userId
     * @return \OCP\AppFramework\Db\Entity as JSONSerializable
     */
    public function tagSearch($tags, $userId){
        $filter = '';
        end($tags);
        $last = key($tags);
        foreach($tags as $key => $tag) {
            if ($key === $last){
                $filter .= 'LOWER(c.category) LIKE LOWER(\''.$tag.'\')';
            }else{
                $filter .= 'LOWER(c.category) LIKE LOWER(\''.$tag.'\') AND ';
            }
        }
        $sql = 'SELECT DISTINCT n.id, n.title, n.user_id, n.content FROM *PREFIX*nextnotes_notes AS n, *PREFIX*vcategory AS c, *PREFIX*vcategory_to_object AS o'.
            ' WHERE n.user_id = ? AND n.id = o.objid AND o.type = ? AND o.categoryid = c.id AND c.type = ? AND c.uid = ? AND '.
            $filter;

        return $this->findEntities($sql, [$userId, 'nextnotes', 'nextnotes', $userId]);
    }

    /**
     * Fulltext and tag search for the respictve arrays of search terms including the userid.
     * @param $terms
     * @param $tags
     * @param $userId
     * @return \OCP\AppFramework\Db\Entity as JSONSerializable
     */
    public function fulltextSearchWithTagFilter($terms, $tags, $userId){
        $filter = '';
        foreach($terms as $term){
            $filter .= 'LOWER(n.content) LIKE LOWER(\'%'.$term.'%\') AND ';
        }
        end($tags);
        $last = key($tags);
        foreach($tags as $key => $tag) {
            if ($key === $last){
                $filter .= 'LOWER(c.category) LIKE LOWER(\''.$tag.'\')';
            }else{
                $filter .= 'LOWER(c.category) LIKE LOWER(\''.$tag.'\') AND ';
            }
        }
        $sql = 'SELECT DISTINCT n.id, n.title, n.user_id, n.content FROM *PREFIX*nextnotes_notes AS n, *PREFIX*vcategory AS c, *PREFIX*vcategory_to_object AS o'.
            ' WHERE n.user_id = ? AND n.id = o.objid AND o.type = ? AND o.categoryid = c.id AND c.type = ? AND c.uid = ? AND '.
            $filter;
        return $this->findEntities($sql, [$userId, 'nextnotes', 'nextnotes', $userId]);
    }

	/**
     * For Hook post_deleteUser: deletes all notes of a specific user.
     * @param $userId
     * @return \PDOStatement
     */
    public function deleteAllForUser($userId){
        $sql = 'DELETE FROM *PREFIX*nextnotes_notes WHERE user_id = ?';
        return $this->execute($sql, [$userId]);
    }

}