<?php
$rootDir = __DIR__ . '/../../';
require_once $rootDir . 'vendor/autoload.php';
session_start();

use \Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();
$controller = new \App\Controller();
$app['debug'] = true;

// render get
$app->get('/', function () use ($rootDir) {
    return file_get_contents($rootDir . '/front/index.html');
});
$app->get('/admin', function (Request $request) use ($rootDir) {
    if ($request->get('key') == '0712') {
        return file_get_contents($rootDir . '/front/admin.html');
    } else {
        return 'denied';
    }
});
$app->get('/results', function () use ($rootDir) {
    return file_get_contents($rootDir . '/front/statistic.html');
});
// json get
$app->get('/switch', function (Request $request) use ($app, $controller) {
    if ($request->get('key') == '0712') {
        $status = $request->get('status', 0);
        $result = ['status' => true, 'opened' => $controller->switch($status)];
    } else {
        $result = ['error' => 'invalid secret key'];
    }
    return $app->json($result);
});
$app->get('/user', function () use ($app, $controller) {
    return $app->json($controller->getUser());
});
$app->get('/questions', function () use ($app, $controller) {
    return $app->json($controller->getQuestions());
});
$app->get('/user-result', function () use ($app, $controller) {
    return $app->json($controller->getUserResult());
});
$app->get('/restart', function () use ($app, $controller) {
    return $app->json($controller->restartQuestions());
});
// json post
$app->post('/sign', function (Request $request) use ($app, $controller) {
    $name = $request->get('name');
    $edu = $request->get('edu');

    return $app->json($controller->saveUserData([
        'name' => $name,
        'edu' => $edu,
    ]));
});
$app->post('/save-answer', function (Request $request) use ($app, $controller) {
    return $app->json($controller->saveUserAnswer($request->request->all()));
});
// csv
$app->get('/get-statistic', function () use ($app, $controller) {
    return $controller->getStatistic();
});

$app->run();