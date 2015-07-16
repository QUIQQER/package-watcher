<?php

/**
 * This file contains package_quiqqer_watch_ajax_list
 */

/**
 * @param string $date
 *
 * @return array
 */
function package_quiqqer_watcher_ajax_clear($date)
{
    QUI\Watcher::clear($date);
}

QUI::$Ajax->register(
    'package_quiqqer_watcher_ajax_clear',
    array('date'),
    array('Permission::checkAdminUser', 'quiqqer.watcher.clearlog')
);
