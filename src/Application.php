<?php
namespace Gt\Cli;

use Composer\InstalledVersions;
use Gt\Cli\Argument\ArgumentValueList;
use Gt\Cli\Command\Command;
use Gt\Cli\Command\HelpCommand;
use Gt\Cli\Command\InvalidCommandException;
use Gt\Cli\Command\VersionCommand;
use Gt\Cli\Parameter\MissingRequiredParameterException;
use Gt\Cli\Argument\ArgumentList;

class Application {
	protected string $description;
	protected ?ArgumentList $arguments;
	/** @var Command[]  */
	protected array $commands;
	protected Stream $stream;
	protected HelpCommand $helpCommand;
	protected VersionCommand $versionCommand;
	private int $exitCode;
	/** @var ?callable */
	private $exitHandler;

	public function __construct(
		string $description,
		ArgumentList $arguments = null,
		Command...$commands
	) {
		$this->description = $description;
		$this->arguments = $arguments;
		$this->commands = $commands;

		$script = null;
		if($arguments) {
			$script = $arguments->getScript();
		}

		$this->helpCommand = new HelpCommand(
			$this->description,
			$script,
			$this->commands
		);
		$this->versionCommand = new VersionCommand();

		array_push($this->commands, $this->helpCommand);
		$this->commands []= $this->versionCommand;

		$this->stream = new Stream(
			"php://stdin",
			"php://stdout",
			"php://stderr"
		);
		$this->helpCommand->setStream($this->stream);
		$this->versionCommand->setStream($this->stream);
	}

	public function setExitHandler(callable $exitHandler):void {
		$this->exitHandler = $exitHandler;
	}

	public function setStream(string $in, string $out, string $error):void {
		$this->stream->setStream($in, $out, $error);
	}

	public function run():void {
		$command = null;
		$exception = null;
		$this->exitCode = 1;

		if(is_null($this->arguments)) {
			$this->stream->writeLine(
				"Application has received no commands",
				Stream::ERROR
			);

			$this->exitCode = 2;
			$this->exit();
			return;
		}

		try {
			$commandName = $this->arguments->getCommandName();
			$command = $this->findCommandByName($commandName);
			$command->setStream($this->stream);

			$argumentValueList = $command->getArgumentValueList(
				$this->arguments
			);

			$firstArgument = $argumentValueList->first();
			if($firstArgument) {
				switch($firstArgument->getKey()) {
				case "help":
					$helpArgs = new ArgumentValueList();
					$helpArgs->set("command", $commandName);
					$this->helpCommand->run($helpArgs);
					return;

				case "version":
					$versionArgs = new ArgumentValueList();
					$versionArgs->set("command", $commandName);
					$this->versionCommand->run($versionArgs);
					return;
				}
			}

			$command->checkArguments(
				$this->arguments
			);
			$command->run($argumentValueList);
			$this->exitCode = 0;
		}
		catch(MissingRequiredParameterException $exception) {
			$message = "Error - Missing required parameter: "
				. $exception->getMessage();

			$this->stream->writeLine(
				$message,
				Stream::ERROR
			);
		}
		catch(CliException $exception) {
			$this->stream->writeLine(
				$exception->getMessage(),
				Stream::ERROR
			);
		}

		if($exception && $command) {
			$this->stream->writeLine(
				$command->getUsage(),
				Stream::ERROR
			);
		}

		$this->exit();
	}

	protected function findCommandByName(string $name):Command {
		$name = trim($name, "-");

		foreach($this->commands as $command) {
			if($command->getName() !== $name) {
				continue;
			}

			return $command;
		}

		throw new InvalidCommandException($name);
	}

	/** @SuppressWarnings(PHPMD.ExitExpression) */
	private function exit():void {
		if(isset($this->exitHandler)) {
			call_user_func($this->exitHandler, $this->exitCode);
		}
		elseif($this->exitCode !== 0) {
			// phpcs:ignore
			exit($this->exitCode);
		}
	}
}
