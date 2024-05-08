<?php
namespace Gt\Cli\Command;

use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Parameter\NamedParameter;
use Gt\Cli\Parameter\Parameter;

class HelpCommand extends Command {
	const ALL_COMMANDS = "*";

	protected string $applicationDescription;
	/** @var Command[] */
	protected array $applicationCommandList;
	protected ?string $scriptName;

	/** @param Command[] $applicationCommandList */
	public function __construct(
		string $applicationDescription,
		string $scriptName = null,
		array $applicationCommandList = []
	) {
		$this->applicationDescription = $applicationDescription;
		$this->scriptName = $scriptName;
		$this->applicationCommandList = $applicationCommandList;
		$this->applicationCommandList []= $this;
	}

	public function run(ArgumentValueList $arguments = null):void {
		$command = null;
		if($arguments) {
			$command = (string)$arguments->get(
				"command",
				self::ALL_COMMANDS
			);
		}

		if($command === self::ALL_COMMANDS) {
			$output = $this->getHelpForAllCommands();
		}
		else {
			$output = $this->getHelpForCommand($command);
		}

		$this->writeLine($output);
	}

	public function getName():string {
		return "help";
	}

	public function getDescription():string {
		return "Display information about available commands";
	}

	/** @return  NamedParameter[] */
	public function getRequiredNamedParameterList():array {
		return [];
	}

	/** @return  NamedParameter[] */
	public function getOptionalNamedParameterList():array {
		return [
			new NamedParameter("command"),
		];
	}

	/** @return  Parameter[] */
	public function getRequiredParameterList():array {
		return [];
	}

	/** @return  Parameter[] */
	public function getOptionalParameterList():array {
		return [
			new Parameter(
				false,
				"version",
				"v",
				"Return the version number of the current command"
			)
		];
	}

	protected function getHelpForAllCommands():string {
		$output = "";
		$output .= $this->applicationDescription;
		$output .= PHP_EOL;
		$output .= PHP_EOL;

		if(empty($this->applicationCommandList)) {
			$output .= "There are no commands available";
			$output .= PHP_EOL;
			return $output;
		}

		$output .= "Available commands:";
		$output .= PHP_EOL;

		$maxNameLength = 0;
		foreach($this->applicationCommandList as $command) {
			$nameLength = strlen($command->getName());
			if($nameLength > $maxNameLength) {
				$maxNameLength = $nameLength;
			}
		}

		foreach($this->applicationCommandList as $command) {
			$output .= " • ";
			$output .= str_pad(
				$command->getName(),
				$maxNameLength,
				" "
			);
			$output .= "\t";
			$output .= $command->getDescription();
			$output .= PHP_EOL;
		}

		$output .= PHP_EOL;
		$output .= "Type `{$this->scriptName} help COMMAND` "
			. "to get help for that command.";
		return $output;
	}

	protected function getHelpForCommand(string $commandName = null):string {
		$output = "";

		$command = null;
		foreach($this->applicationCommandList as $command) {
			if($command->getName() !== $commandName) {
				continue;
			}

			break;
		}

		if(!$command) {
			return "No help for command `$commandName`." . PHP_EOL;
		}

		$output .= $command->getName();
		$output .= ": ";
		$output .= $command->getDescription();
		$output .= PHP_EOL;
		$output .= $command->getUsage(true);

		return $output;
	}
}
