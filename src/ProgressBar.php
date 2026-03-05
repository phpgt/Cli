<?php
namespace Gt\Cli;

class ProgressBar {
	private Stream $stream;
	private int $max;
	private int $width;
	private int $current;
	private string $label;
	private string $streamName;
	private bool $complete;

	public function __construct(
		Stream $stream,
		int $max = 100,
		string $label = "Progress",
		int $width = 40,
		string $streamName = Stream::OUT
	) {
		$this->stream = $stream;
		$this->max = max(1, $max);
		$this->width = max(10, $width);
		$this->label = $label;
		$this->streamName = $streamName;
		$this->current = 0;
		$this->complete = false;
	}

	public function setProgress(int $current, ?string $label = null):void {
		if($this->complete) {
			return;
		}

		$this->current = min(
			$this->max,
			max(0, $current)
		);

		if(!is_null($label)) {
			$this->label = $label;
		}

		$this->render();
	}

	public function advance(int $amount = 1, ?string $label = null):void {
		$this->setProgress(
			$this->current + max(0, $amount),
			$label
		);
	}

	public function finish(?string $label = null):void {
		if($this->complete) {
			return;
		}

		$this->current = $this->max;
		if(!is_null($label)) {
			$this->label = $label;
		}

		$this->render();
		$this->stream->writeLine("", $this->streamName);
		$this->complete = true;
	}

	private function render():void {
		$ratio = $this->current / $this->max;
		$filledCells = (int)round($ratio * $this->width);
		$emptyCells = $this->width - $filledCells;
		$percent = (int)round($ratio * 100);

		$bar = "["
			. str_repeat("=", $filledCells)
			. str_repeat(" ", $emptyCells)
			. "]";
		$message = sprintf(
			"%s %s %3d%% (%d/%d)",
			$this->label,
			$bar,
			$percent,
			$this->current,
			$this->max
		);

		$this->stream->cursor->rewind($this->streamName);
		$this->stream->cursor->clearLine($this->streamName);
		$this->stream->write($message, $this->streamName);
	}
}
