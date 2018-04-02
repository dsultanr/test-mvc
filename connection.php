<?php
  class Db {
    private static $instance = NULL;

    private function __construct() {}

    private function __clone() {}

    public static function getInstance() {
      if (!isset(self::$instance)) {
        try {
          self::$instance = new PDO('pgsql:host=localhost;dbname=testdb1', 'dsultanr', '23456789');
          self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
          self::$instance->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $e) {
          // echo $e->getMessage(); // should be commented on Production
        }
      }
      return self::$instance;
    }
  }
?>