<?php
  class LogsController {
    public function index() {
      require_once('views/logs/index.php');
    }

    public function jsondata() {
      // main method to feed the store
      // we store all the logs in a variable
      $logs = Log::all(isset($_GET['limit'])?$_GET['limit']:'',isset($_GET['start'])?$_GET['start']:'',isset($_GET['ip'])?$_GET['ip']:'');
      header("Content-Type: application/json");
      echo json_encode(array('totalCount'=>$logs['totalCount'][0],'logs'=>$logs['logs']));
      return;
    }

    public function dbseed() {
      // method to seed the DB with START DATA (DROPS 2 TABLES AND CREATES THEM FROM FILES)
      if (isset($_GET['proceed'])) {
        $logs = Log::dbseed();
      }
      require_once('views/logs/dbseed.php');
    }

    public function show() {
      // stub for record show
      require_once('views/logs/show.php');
    }
  }
?>