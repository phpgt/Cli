<?php
namespace Gt\Cli\Argument;

class ArgumentParser {
	/**
	 * @param string[] $arguments
	 * @return Argument[]
	 */
	public function parse(array $arguments):array {
		$argumentList = [];
		$argumentList[] = $this->createCommandArgument($arguments);
		$skipNextArgument = false;

		foreach ($arguments as $i => $arg) {
			if($skipNextArgument) {
				$skipNextArgument = false;
				continue;
			}

			if($arg[0] === "-") {
				$skipNextArgument = $this->appendOptionArgument(
					$argumentList,
					$arguments,
					$i,
					$arg
				);
				continue;
			}

			$argumentList[] = new NamedArgument($arg);
		}

		return $argumentList;
	}

	/** @param string[] $arguments */
	private function createCommandArgument(array &$arguments):CommandArgument {
		$commandName = ArgumentList::DEFAULT_COMMAND;

		if(isset($arguments[0])
		&& $arguments[0][0] !== "-") {
			$commandName = array_shift($arguments);
		}

		return new CommandArgument($commandName);
	}

	/**
	 * @param Argument[] $argumentList
	 * @param string[] $arguments
	 * @return bool True if the next argument should be skipped.
	 */
	private function appendOptionArgument(
		array &$argumentList,
		array $arguments,
		int $argumentIndex,
		string $arg
	):bool {
		if($this->isChainedShortOption($arg)) {
			return $this->appendChainedShortOptionArguments(
				$argumentList,
				$arguments,
				$argumentIndex,
				$arg
			);
		}

		list($name, $value, $skipNextArgument) = $this->extractOptionData(
			$arguments,
			$argumentIndex,
			$arg
		);

		if($arg[1] === "-") {
			$argumentList[] = new LongOptionArgument($name, $value);
			return $skipNextArgument;
		}

		$argumentList[] = new ShortOptionArgument($arg, $value);
		return $skipNextArgument;
	}

	/**
	 * @param string[] $arguments
	 * @return array{string, ?string, bool}
	 */
	private function extractOptionData(
		array $arguments,
		int $argumentIndex,
		string $arg
	):array {
		if(str_contains($arg, "=")) {
			$separatorPosition = strpos($arg, "=");
			$name = substr($arg, 0, $separatorPosition ?: 0);
			$value = substr($arg, ($separatorPosition ?: 0) + 1);
			return [$name, $value, false];
		}

		$nextArgument = $arguments[$argumentIndex + 1] ?? null;
		$nextIsValue = $this->isNextArgumentValue($nextArgument);
		$value = $nextIsValue ? $nextArgument : null;

		return [$arg, $value, $nextIsValue];
	}

	/**
	 * @param Argument[] $argumentList
	 * @param string[] $arguments
	 * @return bool True if the next argument should be skipped.
	 */
	private function appendChainedShortOptionArguments(
		array &$argumentList,
		array $arguments,
		int $argumentIndex,
		string $arg
	):bool {
		$shortOptionCharList = str_split(substr($arg, 1));
		$lastIndex = count($shortOptionCharList) - 1;
		$lastValue = null;
		$nextArgument = $arguments[$argumentIndex + 1] ?? null;
		$nextIsValue = $this->isNextArgumentValue($nextArgument);

		if($nextIsValue) {
			$lastValue = $nextArgument;
		}

		foreach($shortOptionCharList as $shortOptionIndex => $char) {
			$value = $shortOptionIndex === $lastIndex ? $lastValue : null;
			$argumentList[] = new ShortOptionArgument("-" . $char, $value);
		}

		return $nextIsValue;
	}

	private function isNextArgumentValue(?string $arg):bool {
		if(!$arg) {
			return false;
		}

		return !str_starts_with($arg, "-");
	}

	private function isChainedShortOption(string $arg):bool {
		if(strlen($arg) <= 2) {
			return false;
		}

		if($arg[1] === "-") {
			return false;
		}

		if(str_contains($arg, "=")) {
			return false;
		}

		return preg_match('/^-[a-zA-Z]+$/', $arg) === 1;
	}
}
