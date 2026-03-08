<?php
namespace Gt\Cli\Argument;

use Gt\Cli\Parameter\Parameter;
use Iterator;
use LogicException;

/** @implements Iterator<int, Argument> */
class ArgumentList implements Iterator {
	const DEFAULT_COMMAND = "help";

	protected string $script;
	/** @var Argument[] */
	protected array $argumentList = [];
	protected int $iteratorIndex;

	public function __construct(string $script, string...$arguments) {
		$this->script = $script;
		$this->parseArguments($arguments);
	}

	public function getScript():string {
		return $this->script;
	}

	public function getCommandName():string {
		return $this->argumentList[0]->getValue() ?? "";
	}

	/** @param string[] $arguments */
	protected function parseArguments(array $arguments):void {
		$parser = new ArgumentParser();
		$this->argumentList = $parser->parse($arguments);
	}

	/**
	 * @link http://php.net/manual/en/iterator.current.php
	 */
	public function current():Argument {
		return $this->argumentList[$this->iteratorIndex];
	}

	/**
	 * @link http://php.net/manual/en/iterator.next.php
	 */
	public function next():void {
		$this->iteratorIndex++;
	}

	/**
	 * @link http://php.net/manual/en/iterator.key.php
	 */
	public function key():int {
		return $this->iteratorIndex;
	}

	/**
	 * @link http://php.net/manual/en/iterator.valid.php
	 */
	public function valid():bool {
		return isset($this->argumentList[$this->iteratorIndex]);
	}

	/**
	 * @link http://php.net/manual/en/iterator.rewind.php
	 */
	public function rewind():void {
		$this->iteratorIndex = 0;
	}

	public function contains(Parameter $parameter):bool {
		$longOption = $parameter->getLongOption();
		$shortOption = $parameter->getShortOption();
		$containsLong = false;
		$containsShort = false;

		foreach($this->argumentList as $argument) {
			$key = $argument->getKey();

			if($argument instanceof LongOptionArgument) {
				if($key === $longOption) {
					$containsLong = true;
				}
			}
			elseif($argument instanceof ShortOptionArgument) {
				if($key === $shortOption) {
					$containsShort = true;
				}
			}
		}

		$this->throwIfBothLongAndShortOptionAreSet(
			$longOption,
			$shortOption,
			$containsLong,
			$containsShort
		);

		return $containsLong || $containsShort;
	}

	public function getValueForParameter(Parameter $parameter):?string {
		$longOption = $parameter->getLongOption();
		$shortOption = $parameter->getShortOption();
		$containsLong = false;
		$containsShort = false;
		$longValue = null;
		$shortValue = null;

		foreach($this->argumentList as $argument) {
			$key = $argument->getKey();

			if($argument instanceof LongOptionArgument) {
				if($key === $longOption) {
					$containsLong = true;
					$longValue = $argument->getValue();
				}
			}
			elseif($argument instanceof ShortOptionArgument) {
				if($key === $shortOption) {
					$containsShort = true;
					$shortValue = $argument->getValue();
				}
			}
		}

		$this->throwIfBothLongAndShortOptionAreSet(
			$longOption,
			$shortOption,
			$containsLong,
			$containsShort
		);

		return $longValue ?? $shortValue;
	}

	private function throwIfBothLongAndShortOptionAreSet(
		string $longOption,
		?string $shortOption,
		bool $containsLong,
		bool $containsShort
	):void {
		if(!$shortOption) {
			return;
		}

		if($containsLong && $containsShort) {
			throw new LogicException(
				"Parameter cannot be set by both --$longOption and -$shortOption"
			);
		}
	}
}
