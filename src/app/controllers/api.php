<?php
use Symfony\Component\HttpFoundation\Response;
$api = $app['controllers_factory'];


/**
 * /api/lang
 * It counts the tweet per lang (group by lang)
 * @return json lang the language count the occourence of the lang
 */
$api->get('/lang', function() {
  $db = new Mongo('mongodb://localhost');
  $c_tweets = $db->tweets->tweets;
  $keys = array("lang" => 1);
  $initial = array("count" => 0);
  $reduce = "function (obj, prev) { prev.count++; }";
  $g = $c_tweets->group($keys, $initial, $reduce);
  
  $response = new Response(json_encode($g['retval']));
  $response->setTtl(5);
  return $response;
})->bind("api_tweet_count_lang");


$api->get('/lang2', function() {
  $db = new Mongo('mongodb://localhost');
  $c_tweets = $db->tweets->tweets;
  $date = new DateTime();
  $hours = 3;
  
  $date->sub(new DateInterval('PT' . $hours . 'H'));
  $mdate = new MongoDate($date->getTimestamp());
  $g = $c_tweets->aggregate(
    array(
        '$match' => array(
          'rbit_created_at' => array(
            '$gt' => $mdate
        )
        )
      )
    ,
    array(
      '$group' => 
      array(
        "_id" => '$lang',
        "count" => array('$sum' => 1)
      ) 
    ),
    array(
      '$sort' => array("count"=> -1),
    )
  );
  $response = new Response(json_encode($g['result']));
  $response->setTtl(5);
  return $response;
})->bind("api_tweet_count_lang2");

$api->get('/testdate', function() {
  $db = new Mongo('mongodb://localhost');
  $c_tweets = $db->tweets->tweets;
  $start = new MongoDate(strtotime("2012-04-29 00:00:00"));
  $end = new MongoDate(strtotime("2013-05-02 00:00:00"));
  // find dates between 1/15/2010 and 1/30/2010
  $g = $c_tweets->find(array("rbit_created_at" => array('$gt' => $start)));
  //$g = $c_tweets->find();
  $array = iterator_to_array($g);
  $response = new Response(json_encode($array));
  $response->setTtl(5);
  return $response;
});

$api->get('/users', function() {
  $db = new Mongo('mongodb://localhost');
  $c_tweets = $db->tweets->tweets;
  $date = new DateTime();
  $hours = 3;
  
  $date->sub(new DateInterval('PT' . $hours . 'H'));
  $mdate = new MongoDate($date->getTimestamp());
  $g = $c_tweets->aggregate(
      array(
        '$match' => array(
          'rbit_created_at' => array(
            '$gt' => $mdate
        )
        )
      )
    ,
    array(
        '$sort' => array('rbit_created_at' => -1)
    ),
    /*
    array(
        '$limit' => 1000
    ),
    */
          
    array(
      '$group' => 
      array(
        "_id" => '$user.screen_name',
        "count" => array('$sum' => 1)
      ) 
    ),
    array(
      '$sort' => array("count"=> -1),
    ),
    array(
      '$limit' => 5
    )
  );
  $response = new Response(json_encode($g['result']));
  $response->setTtl(5);
  return $response;
})->bind("api_tweet_count_users");

$api->get('/geo', function() {
  $db = new Mongo('mongodb://localhost');
  $c_tweets = $db->tweets->tweets;
  $cursor = $c_tweets->find(array(), array("coordinates"=>1))->sort(array("rbit_created_at" => -1))->limit(200);
  $array = iterator_to_array($cursor);
  
  $response = new Response(json_encode($array));
  //$response->setTtl(5);
  return $response;
})->bind("api_tweet_geo");




$api->get('/hashtags', function() {
  $db = new Mongo('mongodb://localhost');
  $c_tweets = $db->tweets->tweets;
  $date = new DateTime();
  $hours = 3;
  
  $date->sub(new DateInterval('PT' . $hours . 'H'));
  $mdate = new MongoDate($date->getTimestamp());
  $g = $c_tweets->aggregate(
    array(
        '$match' => array(
          'rbit_created_at' => array(
            '$gt' => $mdate
        )
        )
      )
    ,
    array(
        '$unwind' => '$entities.hashtags'
    ),
          /*
    array(
        '$match' => array('entities.hashtags.text' => array('$exists' => true ))
    ),*/
          
    array(
      '$group' => 
      array(
        "_id" => '$entities.hashtags.text',
        "count" => array('$sum' => 1)
      ) 
    ),
    array(
      '$sort' => array("count"=> -1),
    ),
    array(
      '$limit' => 10
    )
  );
  $response = new Response(json_encode($g['result']));
  $response->setTtl(5);
  return $response;
})->bind("api_tweet_count_hashtags");



$api->get('/ensureindex', function() {
  $db = new Mongo('mongodb://localhost');
  $c_tweets = $db->tweets->tweets;
  $c_tweets->ensureIndex('user.screen_name');
  $c_tweets->ensureIndex('lang');
  $c_tweets->ensureIndex('created_at');
  $c_tweets->ensureIndex('rbit_created_at');
  

  $retval = array("status" => "ok");
  $response = new Response(json_encode($retval));
  
  return $response;
})->bind("api_ensureindex");





return $api;



