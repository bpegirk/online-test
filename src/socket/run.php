<?php
require __DIR__ . '/../../vendor/autoload.php';

use Workerman\Worker;
use PHPSocketIO\SocketIO;

$service = new \App\Service();

// listen port 2020 for socket.io client
$io = new SocketIO(2020);
$io->on('connection', function ($socket) use ($service, $io) {
    $socket->on('answer', function () use ($io, $service) {
        $results = $service->getStatistic();
        $io->emit('updateResult', [
            'results' => $results
        ]);
    });
    $socket->on('user_results', function () use ($io) {
        echo "update user results on clients\n";
        $io->emit('user_results_client');
    });
});

Worker::runAll();