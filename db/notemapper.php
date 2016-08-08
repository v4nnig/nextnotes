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

use OCP\IDb;
use OCP\AppFramework\Db\Mapper;

class NoteMapper extends Mapper {

    public function __construct(IDb $db) {
        parent::__construct($db, 'nextnotes_notes', '\OCA\NextNotes\Db\Note');
    }

    public function find($id, $userId) {
        $sql = 'SELECT * FROM *PREFIX*nextnotes_notes WHERE id = ? AND user_id = ?';
        return $this->findEntity($sql, [$id, $userId]);
    }

    public function findAll($userId) {
        $sql = 'SELECT * FROM *PREFIX*nextnotes_notes WHERE user_id = ?';
        return $this->findEntities($sql, [$userId]);
    }

    public function fulltextSearchWithoutTagFilter($terms, $userId){
        $i = 0;
        $len = count($terms);
        $filter = '';
        foreach($terms as $term){
            if($i == $len-1){
                $filter .= 'LOWER(n.content) LIKE LOWER(\'%'.$term.'%\')';
            }else{
                $filter .= 'LOWER(n.content) LIKE LOWER(\'%'.$term.'%\') AND ';
            }
            $i++;
        }
        $sql = 'SELECT DISTINCT n.id, n.title, n.user_id, n.content FROM *PREFIX*nextnotes_notes AS n WHERE n.user_id = ? AND '.
            $filter;

        return $this->findEntities($sql, [$userId]);
    }

    public function tagSearch($tags, $userId){
        $i = 0;
        $len = count($tags);
        $filter = '';
        foreach($tags as $tag){
            if($i == $len-1){
                $filter .= 'LOWER(c.category) LIKE LOWER(\''.$tag.'\')';
            }else{
                $filter .= 'LOWER(c.category) LIKE LOWER(\''.$tag.'\') AND ';
            }
            $i++;
        }
        $sql = 'SELECT DISTINCT n.id, n.title, n.user_id, n.content FROM *PREFIX*nextnotes_notes AS n, *PREFIX*vcategory AS c, *PREFIX*vcategory_to_object AS o'.
            ' WHERE n.user_id = ? AND n.id = o.objid AND o.type = ? AND o.categoryid = c.id AND c.type = ? AND c.uid = ? AND '.
            $filter;

        return $this->findEntities($sql, [$userId, 'nextnotes', 'nextnotes', $userId]);
    }

    public function fulltextSearchWithTagFilter($terms, $tags, $userId){
        $filter = '';
        foreach($terms as $term){
            $filter .= 'LOWER(n.content) LIKE LOWER(\'%'.$term.'%\') AND ';
        }
        $i = 0;
        $len = count($tags);
        foreach($tags as $tag){
            if($i == $len-1){
                $filter .= 'LOWER(c.category) LIKE LOWER(\''.$tag.'\')';
            }else{
                $filter .= 'LOWER(c.category) LIKE LOWER(\''.$tag.'\') AND ';
            }
            $i++;
        }
        $sql = 'SELECT DISTINCT n.id, n.title, n.user_id, n.content FROM *PREFIX*nextnotes_notes AS n, *PREFIX*vcategory AS c, *PREFIX*vcategory_to_object AS o'.
            ' WHERE n.user_id = ? AND n.id = o.objid AND o.type = ? AND o.categoryid = c.id AND c.type = ? AND c.uid = ? AND '.
            $filter;
        return $this->findEntities($sql, [$userId, 'nextnotes', 'nextnotes', $userId]);
    }

}