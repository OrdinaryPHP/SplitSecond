<?php

declare(strict_types=1);

namespace Ordinary\SplitSecond;

use UnexpectedValueException as PhpUnexpectedValueException;

class UnexpectedValueException extends PhpUnexpectedValueException implements SplitSecondException
{
}
