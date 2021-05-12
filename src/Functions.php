<?php

declare(strict_types=1);
/**
 * This file is part of HyperfGlory.
 *
 * @license  https://github.com/HyperfGlory//util/master/LICENSE
 */
namespace HyperfGlory\Util;

if (! function_exists('rmb_capital')) {
    /**
     * 金额转中文大写.
     *
     * @param mixed $amount
     */
    function rmb_capital($amount): string
    {
        $capitalNumbers = [
            '零',
            '壹',
            '贰',
            '叁',
            '肆',
            '伍',
            '陆',
            '柒',
            '捌',
            '玖',
        ];

        $integerUnits = ['', '拾', '佰', '仟'];

        $placeUnits = ['', '万', '亿', '兆'];

        $decimalUnits = ['角', '分', '厘', '毫'];

        $result = [];

        $arr = explode('.', $amount);

        $integer = trim($arr[0] ?? '', '-');
        $decimal = $arr[1] ?? '';

        if (! ((int) $decimal)) {
            $decimal = '';
        }

        // 转换整数部分
        // 从个位开始

        $integerNumbers = $integer ? array_reverse(str_split($integer)) : [];

        $last = null;
        foreach (array_chunk($integerNumbers, 4) as $chunkKey => $chunk) {
            if (! ((int) implode('', $chunk))) {
                // 全是 0 则直接跳过
                continue;
            }

            array_unshift($result, $placeUnits[$chunkKey]);

            foreach ($chunk as $key => $number) {
                // 去除重复 零，以及第一位的 零，类似：1002、110
                if (! $number && (! $last || $key === 0)) {
                    $last = $number;
                    continue;
                }
                $last = $number;

                // 类似 1022，中间的 0 是不需要 佰 的
                if ($number) {
                    array_unshift($result, $integerUnits[$key]);
                }

                array_unshift($result, $capitalNumbers[$number]);
            }
        }

        if (! $result) {
            $result[] = $capitalNumbers[0];
        }

        $result[] = '圆';

        if (! $decimal) {
            $result[] = '整';
        }

        // 转换小数位
        $decimalNumbers = $decimal ? str_split($decimal) : [];
        foreach ($decimalNumbers as $key => $number) {
            $result[] = $capitalNumbers[$number];
            $result[] = $decimalUnits[$key];
        }

        if (strpos((string) $amount, '-') === 0) {
            array_unshift($result, '负');
        }

        return '人民币' . implode('', $result);
    }
}
