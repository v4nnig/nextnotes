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

return [
	'resources' => [
		'note' => ['url' => '/notes'],
		'note_api' => ['url' => '/api/0.1/notes']
	],
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
		['name' => 'note_api#preflighted_cors', 'url' => '/api/0.1/{path}',
			'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']],
		['name' => 'tag#create', 'url' => '/tags', 'verb' => 'POST'],
		['name' => 'tag#show', 'url' => '/tags/{id}', 'verb' => 'GET'],
		['name' => 'tag#remove', 'url' => '/tags/{id}/{title}', 'verb' => 'DELETE'],
		['name' => 'tag#delete', 'url' => '/tags/{title}', 'verb' => 'DELETE']
	]
];