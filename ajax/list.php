<?php

/**
 * This file contains package_quiqqer_watch_ajax_list
 */

/**
 * @param $params
 *
 * @return array
 */
function package_quiqqer_watcher_ajax_list($params)
{
    return QUI\Watcher::getGridList(json_decode($params, true));
}

QUI::$Ajax->register(
    'package_quiqqer_watcher_ajax_list',
    array('params'),
    'Permission::checkAdminUser'
);
