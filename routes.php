<?php
  function call($controller, $action) {
    require_once('controllers/' . $controller . '_controller.php');

    switch($controller) {
      case 'pages':
        $controller = new PagesController();
      break;
      case 'logs':
        // we need the model to query the database later in the controller
        require_once('models/log.php');
        $controllerToRun = new LogsController();
      break;
    }

    $controllerToRun->{ $action }();
  }


  if (array_key_exists($controller, $controllers)) {
    if (in_array($action, $controllers[$controller])) {
      call($controller, $action);
    } else {
      call('pages', 'error');
    }
  } else {
    call('pages', 'error');
  }
?>