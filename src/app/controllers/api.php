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
  $g = $c_tweets->aggregate(
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

$api->get('/users', function() {
  $db = new Mongo('mongodb://localhost');
  $c_tweets = $db->tweets->tweets;
  $g = $c_tweets->aggregate(
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
  $cursor = $c_tweets->find(array(), array("coordinates"=>1))->sort(array("created_at" => -1))->limit(100);
  $array = iterator_to_array($cursor);
  
  $response = new Response(json_encode($array));
  //$response->setTtl(5);
  return $response;
})->bind("api_tweet_geo");




$api->get('/hashtags', function() {
  $db = new Mongo('mongodb://localhost');
  $c_tweets = $db->tweets->tweets;
  $g = $c_tweets->aggregate(
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

  $retval = array("status" => "ok");
  $response = new Response(json_encode($retval));
  
  return $response;
})->bind("api_ensureindex");





return $api;



