<?php

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::create(
    Dotenv\Repository\RepositoryBuilder::createWithDefaultAdapters()
        ->addAdapter(Dotenv\Repository\Adapter\PutenvAdapter::class)
        ->make(),
    __DIR__ . '/..',
    '.env.test'
);
$dotenv->safeLoad();
