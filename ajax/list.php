<?php

/**
 * This file contains package_quiqqer_watch_ajax_list
 */

/**
 * @param $params
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    function ($params, $search) {
        if ($search) {
            $search = json_decode($search, true);
        }

        return QUI\Watcher::getGridList(json_decode($params, true), $search);
    },
    'package_quiqqer_watcher_ajax_list',
    array('params', 'search'),
    array('Permission::checkAdminUser', 'quiqqer.watcher.readlog')
);
