<?php

/**
 * This file contains package_quiqqer_watch_ajax_list
 */

/**
 * @param string $date
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    function ($date) {
        QUI\Watcher::clear($date);
    },
    'package_quiqqer_watcher_ajax_clear',
    array('date'),
    array('Permission::checkAdminUser', 'quiqqer.watcher.clearlog')
);
