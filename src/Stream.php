<?php
namespace Gt\Cli;

use SplFileObject;

class Stream {
	const IN = "in";
	const OUT = "out";
	const ERROR = "error";
	const ANSI_ESCAPE = "\033[";
	const ANSI_RESET = self::ANSI_ESCAPE . "0m";

	protected SplFileObject $error;
	protected SplFileObject $out;
	protected SplFileObject $in;
	protected SplFileObject $currentStream;
	protected ?Palette $outputForeground = null;
	protected ?Palette $outputBackground = null;

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
		$this->write(
			$message . PHP_EOL,
			$streamName,
			$foreground,
			$background
		);
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
}
