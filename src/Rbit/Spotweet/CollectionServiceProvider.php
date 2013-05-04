<?php
namespace Rbit\Spotweet;

use Silex\Application;
use Silex\ServiceProviderInterface;
/**
 * Description of CollectionServiceProvider
 *
 * @author rbutti
 */
class CollectionServiceProvider implements ServiceProviderInterface{
  public function register(Application $app) {
    
    $app['collection_tweet'] = $app->share(function () use ($app) {
      
      $mc = new \MongoClient($app['mongo-connection']);
      $db = $mc->selectDB($app['mongo-database']);
      $c_tweets = $db->tweets;
      return $c_tweets;
    });
  }
    
  public function boot(Application $app) {
  }
}

?>
