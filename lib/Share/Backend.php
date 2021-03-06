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

namespace OCA\NextNotes\Share;

use OCP\Share_Backend;

class Backend implements Share_Backend {

    /**
     * Check if this $itemSource exist for the user
     *
     * @param string $itemSource
     * @param string $uidOwner Owner of the item
     * @return boolean|null Source
     *
     * Return false if the item does not exist for the user
     * @since 5.0.0
     */
    public function isValidSource($itemSource, $uidOwner) {
        // TODO: Implement isValidSource() method.
    }

    /**
     * Get a unique name of the item for the specified user
     *
     * @param string $itemSource
     * @param string|false $shareWith User the item is being shared with
     * @param array|null $exclude List of similar item names already existing as shared items @deprecated since version OC7
     * @return string Target name
     *
     * This function needs to verify that the user does not already have an item with this name.
     * If it does generate a new name e.g. name_#
     * @since 5.0.0
     */
    public function generateTarget($itemSource, $shareWith, $exclude = null) {
        // TODO: Implement generateTarget() method.
    }

    /**
     * Converts the shared item sources back into the item in the specified format
     *
     * @param array $items Shared items
     * @param int $format
     * @return array
     *
     * The items array is a 3-dimensional array with the item_source as the
     * first key and the share id as the second key to an array with the share
     * info.
     *
     * The key/value pairs included in the share info depend on the function originally called:
     * If called by getItem(s)Shared: id, item_type, item, item_source,
     * share_type, share_with, permissions, stime, file_source
     *
     * If called by getItem(s)SharedWith: id, item_type, item, item_source,
     * item_target, share_type, share_with, permissions, stime, file_source,
     * file_target
     *
     * This function allows the backend to control the output of shared items with custom formats.
     * It is only called through calls to the public getItem(s)Shared(With) functions.
     * @since 5.0.0
     */
    public function formatItems($items, $format, $parameters = null) {
        // TODO: Implement formatItems() method.
    }

    /**
     * Check if a given share type is allowd by the back-end
     *
     * @param int $shareType share type
     * @return boolean
     *
     * The back-end can enable/disable specific share types. Just return true if
     * the back-end doesn't provide any specific settings for it and want to allow
     * all share types defined by the share API
     * @since 8.0.0
     */
    public function isShareTypeAllowed($shareType) {
        // TODO: Implement isShareTypeAllowed() method.
}}