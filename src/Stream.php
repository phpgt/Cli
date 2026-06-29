<?php
namespace GT\Cli;

use SplFileObject;

class Stream {
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
	/** @var array<string, string> */
	private array $lineBuffer;
	public Cursor $cursor;

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

		$this->cursor = new Cursor($this);
		$this->setStream($in, $out, $error);
		$this->lastLineBuffer = "";
		$this->lastLineRepeats = false;
		$this->lineBuffer = [];
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

	public function readLine(StreamName $streamName = StreamName::IN):string {
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
		StreamName $streamName = StreamName::OUT,
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
		StreamName $streamName = StreamName::OUT,
		?Palette $foreground = null,
		?Palette $background = null,
	):void {
		$this->writeCompleteLine(
			$message . PHP_EOL,
			$streamName,
			$foreground,
			$background,
		);
	}

	public function writeBufferedLines(
		string $message,
		StreamName $streamName = StreamName::OUT,
		?Palette $foreground = null,
		?Palette $background = null,
	):void {
		$bufferKey = $streamName->value;
		$this->lineBuffer[$bufferKey] ??= "";
		$this->lineBuffer[$bufferKey] .= $message;

		while(true) {
			$newlinePos = strpos($this->lineBuffer[$bufferKey], "\n");
			if($newlinePos === false) {
				break;
			}

			$line = substr(
				$this->lineBuffer[$bufferKey],
				0,
				$newlinePos + 1,
			);
			$remainingBufferOffset = $newlinePos + 1;
			$this->lineBuffer[$bufferKey] = substr(
				$this->lineBuffer[$bufferKey],
				$remainingBufferOffset,
			);
			$this->writeCompleteLine(
				$line,
				$streamName,
				$foreground,
				$background,
			);
		}
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

	protected function getNamedStream(StreamName $streamName):SplFileObject {
		switch($streamName) {
		case StreamName::IN:
			return $this->in;
		case StreamName::OUT:
			return $this->out;
		case StreamName::ERROR:
			return $this->error;
		}
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

	private function writeCompleteLine(
		string $line,
		StreamName $streamName,
		?Palette $foreground = null,
		?Palette $background = null,
	):void {
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
}
