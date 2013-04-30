<?php

require_once __DIR__.'/../vendor/autoload.php';

define("RBIT_DIR_APP", __DIR__.'/../src/app/');




$app = new Silex\Application();

$app['debug'] = true;
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../views',
));

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

$app->mount('/api', include RBIT_DIR_APP.'controllers/api.php');

$app->get('/', function() use ($app) {
  $name = "INDEX";
  return $app['twig']->render('index.twig', array(
    'name' => $name,
  ));
});


$app->get('/testmap' , function() use ($app){
  return $app['twig']->render('testmap.twig');
});


$app->get('/sample', function() {
  $db = new Mongo('mongodb://localhost');
  $c_tweets = $db->tweets->tweets;
  $count_tweets = $c_tweets->count();
  echo "There are $count_tweets documents in the things collection.\n";
  $count_tweets = $c_tweets->count(array('favorited' => false));
  echo "There are $count_tweets true documents in the things collection.\n";
  $count_tweets = $c_tweets->count(array('lang' => 'it'));
  echo "There are $count_tweets lang = it documents in the things collection.\n";

/*
$c_tweets->ensureIndex('user.screen_name');
$c_tweets->ensureIndex('lang');
*/
  $keys = array("lang" => 1);
  $initial = array("count" => 0);
  $reduce = "function (obj, prev) { prev.count++; }";
  $g = $c_tweets->group($keys, $initial, $reduce);
  echo json_encode($g['retval']);

/*
  $keys = array("entities.hashtags" => 1);
  $initial = array("count" => 0);
  $reduce = "function (obj, prev) { prev.count++; }";
  $g = $c_tweets->group($keys, $initial, $reduce);
  echo json_encode($g['retval']);
*/

/*
  $keys = array("");
  $initial = array('tags'=>array(), 'count'=>0);
  $reduce = '
  function (doc, total) {
    if (doc.entities.hashtags.length) {
      doc.entities.hashtags.forEach(function (e) {
        total.tags[e.text]=total.tags[e.text]||0;
        total.tags[e.text]++; total.count++;
      });
    }
  }';
  $g = $c_tweets->group($keys, $initial, $reduce);
  echo json_encode($g['retval']);
*/

/*
$keys = array("user.screen_name"=>1);
$initial = array("count" => 0);
$reduce = "function(obj,prev){ prev.count++; }";
$g = $c_tweets->group($keys, $initial, $reduce);
echo json_encode($g['retval']);
*/

/*
  $keys = array("entities.hashtags.text" => 1);
  $initial = array("count" => 0);
  $reduce = "function (obj, prev) { prev.count++; }";
  $g = $c_tweets->group($keys, $initial, $reduce);
  echo json_encode($g['retval']);
*/

//


    return 'Hello!';
});






$app->run();
