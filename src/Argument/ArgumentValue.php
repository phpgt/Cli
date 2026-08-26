<?php
namespace GT\Cli\Argument;

class ArgumentValue {
	protected string $key;
	/** @var string[] */
	protected array $valueList;
	protected int $queueIndex;

	public function __construct(string $key) {
		$this->key = $key;
		$this->valueList = [];
		$this->queueIndex = 0;
	}

	public function __toString():string {
		return implode(" ", $this->getAll());
	}

	public function push(?string $value = null):void {
		array_push($this->valueList, $value);
	}

	public function getKey():string {
		return $this->key;
	}

	public function get():?string {
		$value = $this->valueList[$this->queueIndex];
		$this->queueIndex++;
		return $value;
	}

	public function getString():string {
		return (string)$this->get();
	}

	public function getInt():int {
		return (int)$this->get();
	}

	public function getFloat():float {
		return (float)$this->get();
	}

	public function getBool():bool {
		return (bool)$this->get();
	}

	/** @return string[] */
	public function getAll():array {
		return $this->valueList;
	}
}
