<?php
  class Log {
    // we define all attributes
    // they are public so that we can access them using $log->id directly
    public $id;
    public $ip;
    public $os;
    public $browser;
    public $lasturl;
    public $firstref;
    public $discurlcount;

    public function __construct($id, $ip, $os, $browser, $lasturl, $firstref, $discurlcount) {
      $this->id = $id;
      $this->ip = $ip;
      $this->os = $os;
      $this->browser = $browser;
      $this->lasturl = $lasturl;
      $this->firstref = $firstref;
      $this->discurlcount = $discurlcount;
    }

    public static function dbseed() {
      $users = 0;
      $requests = 0;
      $db = Db::getInstance();
      if (!$db) return false;
      try {
        // dropping Users table
        $db->query("DROP TABLE IF EXISTS USERS");
        $sql ="CREATE TABLE USERS(
        ID SERIAL PRIMARY KEY,
        IP INET NOT NULL,
        OS TEXT NOT NULL,
        BROWSER TEXT NOT NULL);";
        $db->query($sql);

        // Parsing the users data file and seed the table
        $usersfile='data/ip_os_bro.txt';
        if (file_exists($usersfile)) {
          $handle = @fopen($usersfile, "r");
          if ($handle) {
              while (($buffer = fgets($handle, 4096)) !== false) {
                  list($ip,$os,$browser)=explode('|', $buffer);
                  if (preg_match('~\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}~',$ip) && strlen($os)>1 && strlen($browser)>1) {
                    $req = $db->prepare('INSERT INTO USERS (IP,OS,BROWSER) VALUES (?, ?, ?)');
                    $req->execute(array($ip, $os, $browser));
                    $users++;
                  }
              }
              if (!feof($handle)) {
                  echo "Oops. Something wrong with data files.";
              }
              fclose($handle);
          }
        }

        // dropping requests table
        $db->query("DROP TABLE IF EXISTS REQUESTS");
        $sql ="CREATE TABLE REQUESTS(
        ID SERIAL PRIMARY KEY,
        IP INET NOT NULL,
        DT TIMESTAMP NOT NULL,
        URL TEXT NOT NULL,
        REFER TEXT NOT NULL);";
        $db->query($sql);

        // Parsing the requests data file and seed the table
        $requestsfile='data/ip_dt_tm_url_ref.txt';
        if (file_exists($requestsfile)) {
          $handle = @fopen($requestsfile, "r");
          if ($handle) {
              while (($buffer = fgets($handle, 4096)) !== false) {
                  list($ip,$dt,$tm,$url,$refer)=explode('|', $buffer);
                  $fulltime = $dt.' '.$tm;
                  if (preg_match('~\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}~',$ip) && strlen($fulltime)>1 && strlen($url)>1 && strlen($refer)>1) {
                    $req = $db->prepare('INSERT INTO REQUESTS (IP,DT,URL,REFER) VALUES (?, ?, ?, ?)');
                    $req->execute(array($ip, $fulltime, $url, $refer));
                    $requests++;
                  }
              }
              if (!feof($handle)) {
                  echo "Oops. Something wrong with data files.";
              }
              fclose($handle);
          }
        }
        return array('users'=>$users,'requests'=>$requests);
      } catch(Exception $e) {
        // echo $e->getMessage();//Remove or change message in production code
      }
    }

    public static function all($limit,$offset,$ip) {
      // method
      $list = [];
      $totalCount = 0;
      $db = Db::getInstance();
      if (!$db) return;
      try {
        $sql = 'SELECT DISTINCT ON (T1.IP) T1.IP,T1.ID,T4.DISCURLCOUNT,T1.BROWSER,T1.OS,T2.URL AS LASTURL,T3.REFER AS FIRSTREF FROM USERS AS T1
                  LEFT JOIN (SELECT IP, DT, URL
                  FROM (
                    SELECT IP, DT, URL,
                     ROW_NUMBER() OVER (PARTITION BY IP ORDER BY DT DESC) AS RN
                    FROM REQUESTS
                  ) T
                  WHERE RN = 1) AS T2 ON T1.IP=T2.IP
                  LEFT JOIN (SELECT IP, DT, REFER
                  FROM (
                    SELECT IP, DT, REFER,
                     ROW_NUMBER() OVER (PARTITION BY IP ORDER BY DT ASC) AS RN
                    FROM REQUESTS
                  ) T
                  WHERE RN = 1) AS T3 ON T1.IP=T3.IP
                  LEFT JOIN
                  (SELECT IP,COUNT(URL) AS DISCURLCOUNT FROM REQUESTS GROUP BY IP) AS T4 ON T1.IP=T4.IP';

        $prepareArray = array('limit'=>intval($limit),'offset'=>intval($offset));

        // ip filtering
        if ($ip) {
          preg_match('~\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}~',$ip,$parsedIP);
          $ip=$parsedIP[0];
          $totalCount = 1;
          $prepareArray['ip']=$ip;
        } else {
          // need a total count for pagination
          $totalCount = $db->query("SELECT COUNT(*) FROM (".$sql.") as tt")->fetch();
        }

        $req = $db->prepare($sql . ($ip?" WHERE T1.IP=:ip":'') . ' ORDER BY IP LIMIT :limit OFFSET :offset;');
        $req->execute($prepareArray);

        if ($req === false) {
          $list = null;
        } else {
          foreach($req->fetchAll() as $log) {

            // we create a list of Log objects from the database results
            $list[] = new Log($log['id'], $log['ip'], $log['os'], $log['browser'], ($log['lasturl']?$log['lasturl']:'-'), ($log['firstref']?$log['firstref']:'-'), ($log['discurlcount']?$log['discurlcount']:'-'));
          }
        }
      } catch (Exception $err) {
          // echo $err->getMessage();
          $list = null;
      }
      return array('totalCount'=>$totalCount,'logs'=>$list);
    }

  }
?>