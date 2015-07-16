<?php

/**
 * This file contains package_quiqqer_watch_ajax_list
 */

/**
 * @param $params
 *
 * @return array
 */
function package_quiqqer_watcher_ajax_list($params, $search)
{
    if ($search) {
        $search = json_decode($search, true);
    }

    return QUI\Watcher::getGridList(json_decode($params, true), $search);
}

QUI::$Ajax->register(
    'package_quiqqer_watcher_ajax_list',
    array('params', 'search'),
    array('Permission::checkAdminUser', 'quiqqer.watcher.readlog')
);
