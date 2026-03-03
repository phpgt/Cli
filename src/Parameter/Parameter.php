<?php
namespace Gt\Cli\Parameter;

class Parameter {
	protected bool $takesValue;
	protected string $longOption;
	protected ?string $shortOption;
	protected ?string $documentation;
	protected ?string $exampleValue;

	public function __construct(
		bool $takesValue,
		string $longOption,
		?string $shortOption = null,
		?string $documentation = null,
		?string $exampleValue = null
	) {
		$this->takesValue = $takesValue;
		$this->longOption = $longOption;
		$this->shortOption = $shortOption;
		$this->documentation = $documentation;
		$this->exampleValue = $exampleValue;
	}

	public function __toString():string {
		$message = $this->longOption;

		if(!is_null($this->shortOption)) {
			$message .= " (";
			$message .= $this->shortOption;
			$message .= ")";
		}

		return $message;
	}

	public function getLongOption():string {
		return $this->longOption;
	}

	public function getShortOption():?string {
		return $this->shortOption;
	}

	public function takesValue():bool {
		return $this->takesValue;
	}

	public function getDocumentation():string {
		return $this->documentation ?? "";
	}

	public function getExampleValue():string {
		return $this->exampleValue
			?? strtoupper($this->longOption);
	}
}
