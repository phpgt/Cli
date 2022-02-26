<?php
namespace Gt\Cli\Command;

use Gt\Cli\Argument\Argument;
use Gt\Cli\Argument\ArgumentList;
use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Argument\CommandArgument;
use Gt\Cli\Argument\NamedArgument;
use Gt\Cli\Argument\NotEnoughArgumentsException;
use Gt\Cli\Parameter\MissingRequiredParameterException;
use Gt\Cli\Parameter\MissingRequiredParameterValueException;
use Gt\Cli\Parameter\NamedParameter;
use Gt\Cli\Parameter\Parameter;
use Gt\Cli\Parameter\UserParameter;
use Gt\Cli\Stream;

abstract class Command {
	protected ?Stream $stream;

	public function setStream(Stream $stream = null):void {
		$this->stream = $stream;
	}

	abstract public function run(ArgumentValueList $arguments = null):void;

	abstract public function getName():string;

	abstract public function getDescription():string;

	/** @return  NamedParameter[] */
	abstract public function getRequiredNamedParameterList():array;

	/** @return  NamedParameter[] */
	abstract public function getOptionalNamedParameterList():array;

	/** @return  Parameter[] */
	abstract public function getRequiredParameterList():array;

	/** @return  Parameter[] */
	abstract public function getOptionalParameterList():array;

	public function getUsage(bool $includeDocumentation = false):string {
		$message = "";

		$message .= "Usage: ";
		$message .= $this->getName();

		$documentation = [];

		foreach($this->getRequiredNamedParameterList() as $parameter) {
			$message .= " ";
			$message .= $parameter->getOptionName();

			$paramDocumentation = $parameter->getDocumentation();
			if(!empty($paramDocumentation)) {
				$documentation[$parameter->getOptionName()] =
					$paramDocumentation;
			}
		}

		foreach($this->getOptionalNamedParameterList() as $parameter) {
			$message .= " [";
			$message .= $parameter->getOptionName();
			$message .= "]";

			$paramDocumentation = $parameter->getDocumentation();
			if(!empty($paramDocumentation)) {
				$documentation[$parameter->getOptionName()] =
					"(Optional) " . $paramDocumentation;
			}
		}

		foreach($this->getRequiredParameterList() as $parameter) {
			$message .= " --";
			$message .= $parameter->getLongOption();

			if($short = $parameter->getShortOption()) {
				$message .= "|-$short";
			}

			if($parameter->takesValue()) {
				$message .= " ";
				$message .= $parameter->getExampleValue();
			}

			$paramDocumentation = $parameter->getDocumentation();
			if(!empty($paramDocumentation)) {
				$paramDocumentationKey = "--" . $parameter->getLongOption();
				if($short) {
					$paramDocumentationKey .= "|-" . $short;
				}
				$documentation[$paramDocumentationKey] =
					$paramDocumentation;
			}
		}

		foreach($this->getOptionalParameterList() as $parameter) {
			$message .= " [--";
			$message .= $parameter->getLongOption();

			if($short = $parameter->getShortOption()) {
				$message .= "|-$short";
			}

			if($parameter->takesValue()) {
				$message .= " ";
				$message .= $parameter->getExampleValue();
			}

			$message .= "]";

			$paramDocumentation = $parameter->getDocumentation();
			if(!empty($paramDocumentation)) {
				$paramDocumentationKey = "--" . $parameter->getLongOption();
				if($short) {
					$paramDocumentationKey .= "|-" . $short;
				}
				$documentation[$paramDocumentationKey] =
					"(Optional) " . $paramDocumentation;
			}
		}

		if($includeDocumentation
		&& !empty($documentation)) {
			$message .= PHP_EOL;
			$message .= PHP_EOL;

			foreach($documentation as $key => $docString) {
				$wrappedDocs = wordwrap($docString, 55);
				$wrappedDocs = explode("\n", $wrappedDocs);
				foreach($wrappedDocs as $i => $line) {
					if($i === 0) {
						continue;
					}

					$wrappedDocs[$i] = "\t\t\t" . $line;
				}
				$wrappedDocs = implode("\n", $wrappedDocs);

				if(!strstr($key, "-")) {
					$message .= str_repeat(" ", 6);
					$message .= $key;
					$message .= "\t\t";
					$message .= $wrappedDocs;
					$message .= PHP_EOL;
				}
				else {
					$keyParts = explode("|", $key);
					$message .= str_repeat(" ", 2);
					if(isset($keyParts[1])) {
						$message .= $keyParts[1];
						$message .= ", ";
					}

					$message .= $keyParts[0];
					if(!isset($keyParts[1])) {
						$message .= str_repeat(" ", 3);
					}

					$message .= "\t\t";
					$message .= $wrappedDocs;

					$message .= PHP_EOL;
				}
			}
		}

		return $message;
	}

	public function checkArguments(ArgumentList $argumentList):void {
		$numRequiredNamedParameters = count(
			$this->getRequiredNamedParameterList()
		);

		$passedNamedArguments = 0;
		foreach($argumentList as $argument) {
			if($argument instanceof NamedArgument) {
				$passedNamedArguments ++;
			}
		}

		if($passedNamedArguments < $numRequiredNamedParameters) {
			throw new NotEnoughArgumentsException(
				"Passed: $passedNamedArguments "
				. "required: $numRequiredNamedParameters"
			);
		}

		foreach($this->getRequiredParameterList() as $parameter) {
			if(!$argumentList->contains($parameter)) {
				throw new MissingRequiredParameterException(
					$parameter
				);
			}

			if($parameter->takesValue()) {
				$value = $argumentList->getValueForParameter(
					$parameter
				);
				if(is_null($value)) {
					throw new MissingRequiredParameterValueException(
						$parameter
					);
				}
			}
		}
	}

	public function getArgumentValueList(
		ArgumentList $arguments
	):ArgumentValueList {
		$namedParameterIndex = 0;
		/** @var NamedParameter[] */
		$namedParameterList = array_merge(
			$this->getRequiredNamedParameterList(),
			$this->getOptionalNamedParameterList()
		);

		/** @var Parameter[] $parameterList */
		$parameterList = array_merge(
			$this->getRequiredParameterList(),
			$this->getOptionalParameterList()
		);

		$argumentValueList = new ArgumentValueList();

		foreach($arguments as $argument) {
			if($argument instanceof CommandArgument) {
				continue;
			}
			elseif($argument instanceof NamedArgument) {
				/** @var NamedParameter|null $parameter */
				$parameter = $namedParameterList[
					$namedParameterIndex
				] ?? null;

				if(is_null($parameter)) {
					$argumentValueList->set(
						Argument::USER_DATA,
						$argument->getValue()
					);
				}
				else {
					$argumentValueList->set(
						$parameter->getOptionName(),
						$argument->getValue()
					);
				}

				$namedParameterIndex++;
			}
			elseif($argument instanceof Argument) {
				/** @var Parameter|null $parameter */
				$parameter = null;

				foreach($parameterList as $parameterToCheck) {
					$argumentKey = $argument->getKey();
					if($argumentKey === $parameterToCheck->getLongOption()
					|| $argumentKey === $parameterToCheck->getShortOption()) {
						$parameter = $parameterToCheck;
						break;
					};
				}

				if(is_null($parameter)) {
					$parameter = new UserParameter(
						!empty($argument->getValue()),
						$argument->getKey()
					);
				}

				$argumentValueList->set(
					$parameter->getLongOption(),
					$argument->getValue()
				);
			}
		}

		return $argumentValueList;
	}

	protected function write(
		string $message,
		string $streamName = Stream::OUT
	):void {
		if(!isset($this->stream) || is_null($this->stream)) {
			return;
		}

		$this->stream->write($message, $streamName);
	}

	protected function writeLine(
		string $message = "",
		string $streamName = Stream::OUT
	):void {
		$this->write($message . PHP_EOL, $streamName);
	}

	protected function readLine(string $default = null):string {
		$prefix = "";

		if(!is_null($default)) {
			$prefix = "[$default]";
		}

		$this->write("$prefix > ");
		$line = "";
		if($this->stream) {
			$line = $this->stream->readLine();
		}
		return trim($line) ?: $default ?? "";
	}
}
