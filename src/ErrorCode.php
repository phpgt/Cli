<?php
namespace GT\Cli;

use Exception;
use GT\Cli\Argument\NotEnoughArgumentsException;
use GT\Cli\Command\CommandException;
use GT\Cli\Command\InvalidCommandException;
use GT\Cli\Parameter\MissingRequiredParameterException;
use GT\Cli\Parameter\MissingRequiredParameterValueException;

class ErrorCode {
	const DEFAULT_CODE = 1000;

	/** @var string[] */
	protected static array $classList = [
		NotEnoughArgumentsException::class,
		CommandException::class,
		InvalidCommandException::class,
		MissingRequiredParameterValueException::class,
		MissingRequiredParameterException::class,
	];

	/** @param string|Exception $exception */
	public static function get($exception):int {
		if($exception instanceof Exception) {
			$exception = get_class($exception);
		}

		return (int)array_search(
			$exception,
			self::$classList
		) ?: self::DEFAULT_CODE;
	}
}
