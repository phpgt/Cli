<?php
namespace Gt\Cli;

use SplFileObject;

class Stream {
	const IN = "in";
	const OUT = "out";
	const ERROR = "error";
	const REPEAT_CHAR = "⟲";
	const ANSI_ESCAPE = "\033[";
	const CARRIAGE_RETURN = "\r";
	const ANSI_RESET = self::ANSI_ESCAPE . "0m";

	protected SplFileObject $error;
	protected SplFileObject $out;
	protected SplFileObject $in;
	protected SplFileObject $currentStream;
	protected ?Palette $outputForeground = null;
	protected ?Palette $outputBackground = null;

	protected string $lastLineBuffer;
	private bool $lastLineRepeats;

	public function __construct(
		?string $in = null,
		?string $out = null,
		?string $error = null
	) {
		if(is_null($in)) {
			$in = "php://stdin";
		}
		if(is_null($out)) {
			$out = "php://stdout";
		}
		if(is_null($error)) {
			$error = "php://stderr";
		}

		$this->setStream($in, $out, $error);
		$this->lastLineBuffer = "";
		$this->lastLineRepeats = false;
	}

	public function setStream(string $in, string $out, string $error):void {
		$this->in = new SplFileObject(
			$in,
			"r"
		);
		$this->out = new SplFileObject(
			$out,
			"w"
		);
		$this->error = new SplFileObject(
			$error,
			"w"
		);
	}

	public function getInStream():SplFileObject {
		return $this->in;
	}

	public function getOutStream():SplFileObject {
		return $this->out;
	}

	public function getErrorStream():SplFileObject {
		return $this->error;
	}

	public function readLine(string $streamName = self::IN):string {
		$stream = $this->getNamedStream($streamName);
		$buffer = "";

		while(!strstr($buffer, "\n")) {
			$buffer .= $stream->fread(128);
			usleep(1_000);
		}

		return $buffer;
	}

	public function write(
		string $message,
		string $streamName = self::OUT,
		?Palette $foreground = null,
		?Palette $background = null,
	):void {
		$foreground ??= $this->outputForeground;
		$background ??= $this->outputBackground;

		if($foreground || $background) {
			$message = $this->wrapInPalette(
				$message,
				$foreground,
				$background
			);
		}

		$this->getNamedStream($streamName)->fwrite($message);
	}

	public function writeLine(
		string $message = "",
		string $streamName = self::OUT,
		?Palette $foreground = null,
		?Palette $background = null,
	):void {
		$line = $message . PHP_EOL;

		if($line === $this->lastLineBuffer) {
			$this->write(
				self::REPEAT_CHAR,
				$streamName,
				$foreground,
				$background
			);
			$this->lastLineRepeats = true;
		}
		else {
			if($this->lastLineRepeats) {
				$this->write(PHP_EOL, $streamName);
			}

			$this->write(
				$line,
				$streamName,
				$foreground,
				$background
			);
			$this->lastLineRepeats = false;
		}

		$this->lastLineBuffer = $line;
	}

	public function setOutputPalette(
		?Palette $foreground = null,
		?Palette $background = null
	):void {
		$this->outputForeground = $foreground;
		$this->outputBackground = $background;
	}

	public function resetOutputPalette():void {
		$this->outputForeground = null;
		$this->outputBackground = null;
	}

	public function saveCursorPosition(string $streamName = self::OUT):void {
		$this->writeAnsi("s", $streamName);
	}

	public function restoreCursorPosition(string $streamName = self::OUT):void {
		$this->writeAnsi("u", $streamName);
	}

	public function moveCursorUp(
		int $amount = 1,
		string $streamName = self::OUT
	):void {
		$this->writeAnsi(max(1, $amount) . "A", $streamName);
	}

	public function moveCursorDown(
		int $amount = 1,
		string $streamName = self::OUT
	):void {
		$this->writeAnsi(max(1, $amount) . "B", $streamName);
	}

	public function moveCursorForward(
		int $amount = 1,
		string $streamName = self::OUT
	):void {
		$this->writeAnsi(max(1, $amount) . "C", $streamName);
	}

	public function moveCursorBack(
		int $amount = 1,
		string $streamName = self::OUT
	):void {
		$this->writeAnsi(max(1, $amount) . "D", $streamName);
	}

	public function setCursorColumn(
		int $column = 1,
		string $streamName = self::OUT
	):void {
		$this->writeAnsi(max(1, $column) . "G", $streamName);
	}

	public function rewindCursor(string $streamName = self::OUT):void {
		$this->write(self::CARRIAGE_RETURN, $streamName);
	}

	public function clearLine(string $streamName = self::OUT):void {
		$this->writeAnsi("2K", $streamName);
	}

	protected function getNamedStream(string $streamName):SplFileObject {
		switch($streamName) {
		case self::IN:
			return $this->in;
		case self::OUT:
			return $this->out;
		case self::ERROR:
			return $this->error;
		}

		throw new InvalidStreamNameException($streamName);
	}

	private function wrapInPalette(
		string $message,
		?Palette $foreground = null,
		?Palette $background = null
	):string {
		$codeList = [];
		if($foreground) {
			$codeList []= $foreground->getForegroundCode();
		}
		if($background) {
			$codeList []= $background->getBackgroundCode();
		}

		if(empty($codeList)) {
			return $message;
		}

		return self::ANSI_ESCAPE
			. implode(";", $codeList)
			. "m"
			. $message
			. self::ANSI_RESET;
	}

	private function writeAnsi(
		string $command,
		string $streamName = self::OUT
	):void {
		$this->write(
			self::ANSI_ESCAPE . $command,
			$streamName
		);
	}
}
