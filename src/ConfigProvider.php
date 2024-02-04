<?php

declare(strict_types=1);

namespace Firezihai\HyperfSqlite;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                'db.connector.sqlite' => \Firezihai\Sqlite\Connectors\SQLiteConnector::class,
            ],
            'listener'=>[
                \Firezihai\Sqlite\Listener\RegisterConnectionListener::class
            ],
            'commands' => [
            ],
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
        ];
    }
}
