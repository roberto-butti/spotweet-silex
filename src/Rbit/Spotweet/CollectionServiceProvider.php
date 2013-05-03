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
      
      $db = new \Mongo($app['mongo-connection']);
      $c_tweets = $db->tweets->tweets;
      
      return $c_tweets;
    });
  }
    
  public function boot(Application $app) {
  }
}

?>
