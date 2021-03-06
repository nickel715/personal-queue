<?php

    require_once 'vendor/autoload.php';

    use Monolog\Handler\StreamHandler;
    use Monolog\Logger;
    use PersonalQueue\JobDealer;
    use Pheanstalk\Job;
    use Pheanstalk\Pheanstalk;
    use Pheanstalk\PheanstalkInterface;

    $app = new µ;
    $app->cfg('views', __DIR__ . '/templates')
        ->cfg('tube', 'personal-queue')
        ->cfg('log.channel', 'personal-queue')
        ->cfg('log.path', 'app.log')
        ->cfg('log.handler', function(µ $app) {
            return new StreamHandler($app->cfg('log.path'));
        })
        ->cfg('log', function(µ $app) {
            $logger = new Logger($app->cfg('log.channel'));
            $logger->pushHandler($app->cfg('log.handler'));
            return $logger;
        })
        ->cfg('pheanstalk.host', '127.0.0.1')
        ->cfg('pheanstalk.port', PheanstalkInterface::DEFAULT_PORT)
        ->cfg('pheanstalk', function(µ $app) {
            return new Pheanstalk($app->cfg('pheanstalk.host'), $app->cfg('pheanstalk.port'));
        })
        ->cfg('job-dealer', function(µ $app) {
            return (new JobDealer)
                ->setLogger($app->cfg('log'))
                ->setPheanstalk($app->cfg('pheanstalk'))
                ->setTube($app->cfg('tube'));
        })
        ->cfg('getParams', function(µ $app) {
            $query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
            parse_str($query, $getParams);
            return $getParams;
        })
    ;

    if (file_exists('config.php')) {
        $config = require('config.php');
        foreach ($config as $key => $value) {
            $app->cfg($key, $value);
        }
    }

    try {

        echo $app
            ->get(
                '/job',
                function (µ $app, array $params) {
                    /** @var JobDealer $jobDealer */
                    $jobDealer = $app->cfg('job-dealer');

                    return $jobDealer->peek()->getData();
                }
            )
            ->post(
                '/job',
                function (µ $app, array $params) {
                    /** @var JobDealer $jobDealer */
                    $jobDealer = $app->cfg('job-dealer');
                    $getParams = $app->cfg('getParams');
                    $jobDealer->add(
                        file_get_contents("php://input"),
                        (!empty($getParams['priority'])) ? $getParams['priority'] : PheanstalkInterface::DEFAULT_PRIORITY,
                        (!empty($getParams['delay'])) ? $getParams['delay'] : PheanstalkInterface::DEFAULT_DELAY
                    );
                    http_response_code(201);
                }
            )
            ->delete(
                '/job/(?<id>\d+)',
                function (µ $app, array $params) {
                    /** @var JobDealer $jobDealer */
                    $jobDealer = $app->cfg('job-dealer');
                    $jobDealer->done($params['id']);
                }
            )
            ->any(
                '/reschedule/(?<id>\d+)',
                function (µ $app, array $params) {
                    /** @var JobDealer $jobDealer */
                    $jobDealer = $app->cfg('job-dealer');
                    $getParams = $app->cfg('getParams');
                    $jobDealer->reschedule(
                        $params['id'],
                        (!empty($getParams['priority'])) ? $getParams['priority'] : PheanstalkInterface::DEFAULT_PRIORITY,
                        (!empty($getParams['delay'])) ? $getParams['delay'] : PheanstalkInterface::DEFAULT_DELAY
                    );
                }
            )
            ->get(
                '/',
                function (µ $app, array $params) {
                    /** @var JobDealer $jobDealer */
                    $jobDealer = $app->cfg('job-dealer');

                    try {
                        $d = [
                            'job'   => $jobDealer->peek(),
                            'count' => $jobDealer->count(),
                        ];
                    } catch(Exception $e) {
                        $d = ['job' => new Job(0, ''), 'count' => 0];
                    }

                    return $app->view(
                        'index',
                        array_merge(['app' => $app], $d)
                    );
                }
            )
            ->run();

    } catch (Exception $e) {
        $app->cfg('log')->error($e->getMessage(), array('exception' => $e));
        http_response_code(500);
    }
