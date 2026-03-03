<?php
namespace Gt\Cli\Argument;

use Iterator;

/** @implements Iterator<int, ArgumentValue> */
class ArgumentValueList implements Iterator {
	/** @var ArgumentValue[] */
	protected array $valueList = [];
	/** @var array<string, ArgumentValue> */
	protected array $argumentValueMap = [];
	protected int $iteratorIndex;

	public function set(string $key, ?string $value = null):void {
		if($this->contains($key)) {
			$valueObject = $this->get($key);
		}
		else {
			$valueObject = $key === Argument::USER_DATA
				? new NamedArgumentValue($key)
				: new ArgumentValue($key);
			$this->valueList []= $valueObject;
		}

		$valueObject->push($value);
		$this->argumentValueMap[$key] = $valueObject;
	}

	public function get(
		string $key,
		?string $default = null
	):ArgumentValue {
		if(!$this->contains($key)) {
			if(!is_null($default)) {
				return new DefaultArgumentValue($default);
			}

			throw new ArgumentValueListNotSetException($key);
		}

		return $this->argumentValueMap[$key];
	}

	public function contains(string $key):bool {
		return isset($this->argumentValueMap[$key]);
	}

	/** @link https://php.net/manual/en/iterator.rewind.php */
	public function rewind():void {
		$this->iteratorIndex = 0;
	}

	/** @link https://php.net/manual/en/iterator.key.php */
	public function key():int {
		return $this->iteratorIndex;
	}

	/**
	 * @link https://php.net/manual/en/iterator.valid.php
	 */
	public function valid():bool {
		return isset($this->valueList[$this->iteratorIndex]);
	}

	/** @link https://php.net/manual/en/iterator.current.php */
	public function current():?ArgumentValue {
		if(!$this->iteratorIndex) {
			$this->rewind();
		}
		return $this->valueList[$this->iteratorIndex] ?? null;
	}

	/** @link https://php.net/manual/en/iterator.next.php */
	public function next():void {
		$this->iteratorIndex++;
	}

	public function first():?ArgumentValue {
		return $this->valueList[0] ?? null;
	}
}
