<?php

declare(strict_types=1);
/**
 * This file is part of HyperfGlory.
 *
 * @license  https://github.com/HyperfGlory/util/master/LICENSE
 */
namespace HyperfGlory\Util;

/*
 * Define the number of blocks that should be read from the source file for each chunk.
 * For 'AES-128-CBC' each block consist of 16 bytes.
 * So if we read 10,000 blocks we load 160kb into memory. You may adjust this value
 * to read/write shorter or longer chunks.
 */
define('FILE_ENCRYPTION_BLOCKS', 10000);

/**
 * Encrypt the passed file and saves the result in a new file with ".enc" as suffix.
 *
 * @param string $source Path to file that should be encrypted
 * @param string $key The key used for the encryption
 * @param string $dest file name where the encryped file should be written to
 *
 * @return false|string Returns the file name that has been created or FALSE if an error occured
 */
function encryptFile(string $source, string $key, string $dest)
{
    $key = substr(sha1($key, true), 0, 16);
    $iv = openssl_random_pseudo_bytes(16);
    $error = false;
    if ($fpOut = fopen($dest, 'wb')) {
        // Put the initialzation vector to the beginning of the file
        fwrite($fpOut, $iv);
        if ($fpIn = fopen($source, 'rb')) {
            while (! feof($fpIn)) {
                $plaintext = fread($fpIn, 16 * FILE_ENCRYPTION_BLOCKS);
                $ciphertext = openssl_encrypt($plaintext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                // Use the first 16 bytes of the ciphertext as the next initialization vector
                $iv = substr($ciphertext, 0, 16);
                fwrite($fpOut, $ciphertext);
            }
            fclose($fpIn);
        } else {
            $error = true;
        }
        fclose($fpOut);
    } else {
        $error = true;
    }

    return $error ? false : $dest;
}

/**
 * Dencrypt the passed file and saves the result in a new file, removing the
 * last 4 characters from file name.
 *
 * @param string $source Path to file that should be decrypted
 * @param string $key The key used for the decryption (must be the same as for encryption)
 * @param string $dest file name where the decryped file should be written to
 *
 * @return false|string Returns the file name that has been created or FALSE if an error occured
 */
function decryptFile(string $source, string $key, string $dest)
{
    $key = substr(sha1($key, true), 0, 16);

    $error = false;
    if ($fpOut = fopen($dest, 'wb')) {
        if ($fpIn = fopen($source, 'rb')) {
            // Get the initialzation vector from the beginning of the file
            $iv = fread($fpIn, 16);
            while (! feof($fpIn)) {
                // we have to read one block more for decrypting than for encrypting
                $ciphertext = fread($fpIn, 16 * (FILE_ENCRYPTION_BLOCKS + 1));
                $plaintext = openssl_decrypt($ciphertext, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
                // Use the first 16 bytes of the ciphertext as the next initialization vector
                $iv = substr($ciphertext, 0, 16);
                fwrite($fpOut, $plaintext);
            }
            fclose($fpIn);
        } else {
            $error = true;
        }
        fclose($fpOut);
    } else {
        $error = true;
    }

    return $error ? false : $dest;
}

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
