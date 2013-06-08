<?php
class Configurator extends AppModel {

    public $name = 'Configurator';

    public $useTable = false; // This model does not use a database table


    //Save database

    function saveDb($type, $host = 'localhost', $login, $password, $database) {

        $config = APP.'Config/database.php';
        $template = APP.'Config/database.php.default';

        $old = array();

        $old['type']        = '%type%';
        $old['username']    = '%login%';
        $old['password']    = '%password%';
        $old['database']    = '%database%';
        $old['host']        = '%host%';

        $new = array();

        $new['type']        = $type;
        $new['username']    = $login;
        $new['password']    = $password;
        $new['database']    = $database;
        $new['host']        = $host;

        $new_file = implode(file($template));

        $str = str_replace($old,$new,$new_file);

        //now, TOTALLY rewrite the file
        $fp=fopen($config,'w');

        fwrite($fp,$str,strlen($str));

        return $str;

    }
    function xml($n) {

        $file = new File(TMP . 'xml' . DS . $n );

        //2) check if it younger then 6h

        if ($file->exists()) {

          $last_modified = filemtime($file->path);

          $current_time = time();

          $dif = $current_time - $last_modified;

        }
        //generate cache file if !1) || !2) and save him

        if (!$file->exists() || !$dif > 21600 ) {

          $filename = 'http://dl.nope.bz/sb/build/'.$n;
          $l_sb = simplexml_load_file($filename);

          $json = json_encode($l_sb);

          $file->write($json, 'w', true);

        }

        return json_decode($file->read(), TRUE);
    }
    //Generate configuration.php token

    function get_random_string($valid_chars, $length)
    {
        // start with an empty random string
        $random_string = "";

        // count the number of chars in the valid chars string so we know how many choices we have
        $num_valid_chars = strlen($valid_chars);

        // repeat the steps until we've created a string of the right length
        for ($i = 0; $i < $length; $i++)
        {
            // pick a random number from 1 up to the number of valid chars
            $random_pick = mt_rand(1, $num_valid_chars);

            // take the random character out of the string of valid chars
            // subtract 1 from $random_pick because strings are indexed starting at 0, and we started picking at 1
            $random_char = $valid_chars[$random_pick-1];

            // add the randomly-chosen char onto the end of our string so far
            $random_string .= $random_char;
        }

        // return our finished random string
        return $random_string;
    }

    function saveCore() {

        $config = APP.'webroot/configuration.php';

        $old = array();

        $old['token']         = '%*TOKEN*%';
        $old['salt']          = '%*SALT*%';
        $old['cypher']        = '%*CYPHER*%';

        $new = array();

        $c = uniqid (rand(), true);

        $md5c = md5($c);

        $new['token']         = $md5c;
        $new['salt']          = $this->get_random_string('1234567890', 32);
        $new['cypher']        = $this->get_random_string('1234567890', 32);

        $new_file = implode(file($config));

        $str = str_replace($old,$new,$new_file);

        //now, TOTALLY rewrite the file
        $fp=fopen($config,'w');

        fwrite($fp,$str,strlen($str));

        return $md5c;

    }
    //Save config

    function saveVars($new) {
      $file       = APP . 'webroot/vars.php';
      include $file;


      $contents   = file_get_contents($file);

      $old = array();

      foreach ($variables as $key => $var) {

        $old[$key] = "/*".$key."*/'".$var['val']."'";
        $new[$key] = "/*".$key."*/'".htmlspecialchars($new[$key], ENT_QUOTES)."'";
      }
      $new_file = implode(file($file));

      $str = str_replace($old,$new,$new_file);

      //now, TOTALLY rewrite the file
      $fp=fopen($file,'w');

      fwrite($fp, $str, strlen($str));

    }

    //Retrieve config

    function returnVars($key = null) {

      require APP . 'webroot/vars.php';

      if ($key) {

        return $variables[$key];

      } else {

        return $variables;

      }

    }

    /* SYSTEM */

    //Save config

    function saveSys($new) {


      $file       = APP . 'webroot/system.php';
      include $file;

      $contents   = file_get_contents($file);

      $old = array();

      foreach ($system as $key => $var) {

        $old[$key] = "/*".$key."*/'".$var['val']."'";
        $new[$key] = "/*".$key."*/'".htmlspecialchars($new[$key], ENT_QUOTES)."'";

      }

      $new_file = implode(file($file));

      $str = str_replace($old,$new,$new_file);

      //now, TOTALLY rewrite the file
      $fp=fopen($file,'w');

      fwrite($fp, $str, strlen($str));

    }

    //Retrieve config

    function returnSys($key = null) {

      require APP . 'webroot/system.php';

      if ($key) {

        return $system[$key];

      } else {

        return $system;

      }

    }

    //Function to get strings
    function get_string_between($string, $start, $end){
      $string = " ".$string;
      $ini = strpos($string,$start);
      if ($ini == 0) return "";
      $ini += strlen($start);
      $len = strpos($string,$end,$ini) - $ini;
      return substr($string,$ini,$len);
    }


}
