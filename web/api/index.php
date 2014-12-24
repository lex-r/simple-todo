<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use \Silex\Application;
use \Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();

$app->register(new SilexPhpRedis\PhpRedisProvider(), array(
    'redis.host' => 'redis',
    'redis.port' => 6379,
    'redis.timeout' => 1,
    'redis.persistent' => true,
    'redis.serializer.php' => true,
    'redis.prefix' => '',
    'redis.database' => '0'
));

$app->before(function (Request $request) {
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
});

$app->get('/', function() {
   return "";
});

$app->get('/todos', function(Application $app) {

    $redis = $app['redis'];
    $todoKeys = $redis->keys('todo:*');
    $todos = array();
    foreach ($todoKeys as $todoKey) {
        $todo = $redis->hGetAll($todoKey);
        if (!array_key_exists('status', $todo)) {
            $todo['status'] = false;
        }

        $todos[] = $todo;
    }

   return $app->json($todos);
});

$app->put('/todos', function(Application $app, Request $request) {

    $data = $request->request->get('text');

    $todo = array(
        'id' => 0,
        'text' => $data,
        'status' => false,
    );

    $redis = $app['redis'];
    $increment = $redis->incr('todo_increment');
    $todo['id'] = $increment;
    $redis->hMset('todo:' . $increment, $todo);

    return $app->json($todo);
});

$app->delete('/todos/{id}', function(Application $app, Request $request) {
    $id = $request->get('id');

    $redis = $app['redis'];
    $redis->delete('todo:' . $id);

    return $app->json($id);
});

$app->post('/todos/{id}', function(Application $app, Request $request) {
    $id = $request->get('id');
    $status = $request->get('status');

    $redis = $app['redis'];
    $todo = $redis->hGetAll('todo:' . $id);
    if (!$todo) {
        $app->abort(404, 'Todo not found');
    }

    $todo['status'] = $status;
    $redis->hMset('todo:' . $todo['id'], $todo);

    return $app->json($todo);
});



$app->run();
