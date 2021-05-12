<?php

declare(strict_types=1);
/**
 * This file is part of HyperfGlory.
 *
 * @license  https://github.com/HyperfGlory//util/master/LICENSE
 */
namespace HyperfGlory\Util\Emoji\Exceptions;

use Exception;

class CouldNotDetermineFlag extends Exception
{
    public static function countryCodeLenghtIsWrong(string $invalidCountryCode): self
    {
        return new static("`{$invalidCountryCode}` is not a valid country code. A valid country code should have two characters.");
    }
}
