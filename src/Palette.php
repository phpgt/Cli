<?php
namespace Gt\Cli;

enum Palette:int {
	case BLACK = 0;
	case RED = 1;
	case GREEN = 2;
	case YELLOW = 3;
	case BLUE = 4;
	case MAGENTA = 5;
	case CYAN = 6;
	case WHITE = 7;

	public function getForegroundCode():string {
		return (string)(30 + $this->value);
	}

	public function getBackgroundCode():string {
		return (string)(40 + $this->value);
	}
}
