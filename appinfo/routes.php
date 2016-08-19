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
        'note_api' => ['url' => '/api/1.0/notes']
    ],
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'note_api#preflighted_cors', 'url' => '/api/1.0/{path}',
            'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']],
        ['name' => 'tag_api#preflighted_cors', 'url' => '/api/1.0/{path}',
            'verb' => 'OPTIONS', 'requirements' => ['path' => '.+']],
        ['name' => 'tag#index', 'url' => '/tags', 'verb' => 'GET'],
        ['name' => 'tag#create', 'url' => '/tagging', 'verb' => 'POST'],
        ['name' => 'tag#show', 'url' => '/tags', 'verb' => 'POST'],
        ['name' => 'tag#remove', 'url' => '/untag', 'verb' => 'POST'],
        ['name' => 'tag#delete', 'url' => '/deletetag', 'verb' => 'POST'],
        ['name' => 'tag_api#index', 'url' => '/api/1.0/tags', 'verb' => 'GET'],
        ['name' => 'tag_api#create', 'url' => '/api/1.0/tagging', 'verb' => 'POST'],
        ['name' => 'tag_api#show', 'url' => '/api/1.0/tags', 'verb' => 'POST'],
        ['name' => 'tag_api#remove', 'url' => '/api/1.0/untag', 'verb' => 'POST'],
        ['name' => 'tag_api#delete', 'url' => '/api/1.0/deletetag', 'verb' => 'POST'],
        ['name' => 'note#search', 'url' => '/notes/search', 'verb' => 'POST'],
        ['name' => 'note_api#search', 'url' => '/api/1.0/notes/search', 'verb' => 'POST']
    ]
];