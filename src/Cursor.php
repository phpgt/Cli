<?php
namespace Gt\Cli;

class Cursor {
	private Stream $stream;

	public function __construct(Stream $stream) {
		$this->stream = $stream;
	}

	public function savePosition(string $streamName = Stream::OUT):void {
		$this->stream->write(Stream::ANSI_ESCAPE . "s", $streamName);
	}

	public function restorePosition(string $streamName = Stream::OUT):void {
		$this->stream->write(Stream::ANSI_ESCAPE . "u", $streamName);
	}

	public function moveUp(
		int $amount = 1,
		string $streamName = Stream::OUT
	):void {
		$this->stream->write(
			Stream::ANSI_ESCAPE . max(1, $amount) . "A",
			$streamName
		);
	}

	public function moveDown(
		int $amount = 1,
		string $streamName = Stream::OUT
	):void {
		$this->stream->write(
			Stream::ANSI_ESCAPE . max(1, $amount) . "B",
			$streamName
		);
	}

	public function moveForward(
		int $amount = 1,
		string $streamName = Stream::OUT
	):void {
		$this->stream->write(
			Stream::ANSI_ESCAPE . max(1, $amount) . "C",
			$streamName
		);
	}

	public function moveBack(
		int $amount = 1,
		string $streamName = Stream::OUT
	):void {
		$this->stream->write(
			Stream::ANSI_ESCAPE . max(1, $amount) . "D",
			$streamName
		);
	}

	public function setColumn(
		int $column = 1,
		string $streamName = Stream::OUT
	):void {
		$this->stream->write(
			Stream::ANSI_ESCAPE . max(1, $column) . "G",
			$streamName
		);
	}

	public function rewind(string $streamName = Stream::OUT):void {
		$this->stream->write(Stream::CARRIAGE_RETURN, $streamName);
	}

	public function clearLine(string $streamName = Stream::OUT):void {
		$this->stream->write(Stream::ANSI_ESCAPE . "2K", $streamName);
	}
}
