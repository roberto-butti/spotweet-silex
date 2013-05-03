<?php
require_once('lib/Phirehose.php');
require_once('./config.php');

if (constant('RBIT_CONFIG_USERNAME') === null) {
  die("Please create your config.php file (see config.template.php)");
}

class MyStream extends Phirehose {
  static $tweetCounter;
  var $db;
  private $config_saveimage;

  private $datas = array();
  private $datas_count=0;
  

  public function __construct($username, $password, $method = Phirehose::METHOD_SAMPLE, $format = self::FORMAT_JSON)
  {
    parent::__construct($username, $password, $method, $format);
    //connect to the Mongo database, and select collection
    $m = new Mongo("mongodb://localhost");
    $this->collection = $m->tweets->tweets;
    $this->config_saveimage = false;
    $this->config_echotweet = false;
    $this->datas = array();
    $this->datas_count = 0;

  }

  public function log($string = "PING") {
    echo $string;
  }
  public function enqueueStatus($status) {
    $data = json_decode($status, true);
    //$this->log($data["created_at"]." - ");
    //$d = date("l M j \- g:ia",strtotime($data["created_at"]));
    $d = strtotime($data["created_at"]);
    $md = new MongoDate($d);
    $data["rbit_created_at"] =$md;
    $this->log(".");
    //var_dump($md);
    $this->datas[]= $data;
    $this->datas_count++;
    if ($this->datas_count == 100) {
      $this->collection->batchInsert($this->datas);
      $this->datas=array();
      $this->datas_count=0;
      $this->log("O");

    }
    

    //$this->log(".");


    if ($this->config_saveimage) {
      if ( isset($data['entities']['media'][0]['media_url']) ) {
        $s = file_get_contents($data['entities']['media'][0]['media_url']);
        self::$tweetCounter ++;
        $filePath = 'file_' . time() . '_' . self::$tweetCounter . '.png';
        //echo $data['user']['screen_name']. " ... ". $filePath;
        file_put_contents($filePath, $s);
        //echo "\n\n";
      }
    }
    if ($this->config_echotweet) {
      if (is_array($data) && isset($data['user']) &&  isset($data['text']) && isset($data['user']['screen_name'])) {  
        $this->log($data['user']['screen_name'] . ': ' . urldecode($data['text']) . "\n");
      }
    }
  }
} // END OF CLASS



$stream = new MyStream(constant('RBIT_CONFIG_USERNAME'), constant('RBIT_CONFIG_PASSWORD'), Phirehose::METHOD_FILTER);
$stream->setLocationsByCircle(array(
  //array(-122.419416, 37.774929, 2000),
  //array(-74.005973, 40.714353, 2000),
  array(11.07, 45.15, 1000),
));
//$stream->setTrack(array('test));
$stream->consume();
