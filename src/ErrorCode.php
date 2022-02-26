<?php
namespace Gt\Cli;

use Exception;
use Gt\Cli\Argument\NotEnoughArgumentsException;
use Gt\Cli\Command\CommandException;
use Gt\Cli\Command\InvalidCommandException;
use Gt\Cli\Parameter\MissingRequiredParameterException;
use Gt\Cli\Parameter\MissingRequiredParameterValueException;

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
