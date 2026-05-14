<?php
namespace GT\Cli;

enum StreamName:string {
	case IN = "in";
	case OUT = "out";
	case ERROR = "error";
}
