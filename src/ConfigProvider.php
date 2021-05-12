<?php

declare(strict_types=1);
/**
 * This file is part of HyperfGlory.
 *
 * @license  https://github.com/HyperfGlory//util/master/LICENSE
 */
namespace HyperfGlory\Util;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
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
