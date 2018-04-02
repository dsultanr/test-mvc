<?php
    ini_set("display_errors","on");
    ini_set("display_startup_errors","1");
    ini_set('error_reporting', E_ALL);

    require_once('connection.php');

    if (isset($_GET['controller']) && isset($_GET['action'])) {
        $controller = $_GET['controller'];
        $action     = $_GET['action'];
    } else {
        $controller = 'logs';
        $action     = 'index';
    }

    // we're adding an entry for the new controller and its actions
    $controllers = array('pages' => ['home', 'error'],
                       'logs' => ['index', 'show', 'dbseed', 'jsondata']);

    // no need layout for json data (actions list, should be improved to contoller/action)
    $nolayout = array('jsondata');

    require_once('views/layout.php');
?>