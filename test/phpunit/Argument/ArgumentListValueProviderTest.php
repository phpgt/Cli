<?php
namespace GT\Cli\Test\Argument;

use GT\Cli\Argument\ArgumentList;
use GT\Cli\Parameter\Parameter;
use PHPUnit\Framework\TestCase;

class ArgumentListValueProviderTest extends TestCase {
	/** @dataProvider dataRandomLongEqualsArgs */
	public function testKeyValueSetWithLongOptionEqualsSign(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);

		foreach($args as $i => $arg) {
			if($i === 0) {
				continue;
			}

			$arg = substr($arg, 2);
			list($key, $value) = explode("=", $arg);

			$param = self::createMock(Parameter::class);
			$param->method("getLongOption")->willReturn($key);
			self::assertEquals($value, $argumentList->getValueForParameter($param));
		}
	}

	/** @dataProvider dataRandomShortEqualsArgs */
	public function testKeyValueSetWithShortOptionEqualsSign(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);

		foreach($args as $i => $arg) {
			if($i === 0) {
				continue;
			}

			$arg = substr($arg, 1);
			list($key, $value) = explode("=", $arg);

			$param = self::createMock(Parameter::class);
			$param->method("getShortOption")->willReturn($key);
			self::assertEquals($value, $argumentList->getValueForParameter($param));
		}
	}

	/** @dataProvider dataRandomShortEqualsArgs */
	public function testGetValueForParameterNotExists(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);

		foreach(array_keys($args) as $i) {
			if($i === 0) {
				continue;
			}

			$param = self::createMock(Parameter::class);
			$param->method("getShortOption")->willReturn("Z");
			self::assertNull($argumentList->getValueForParameter($param));
		}
	}

	/** @dataProvider dataRandomLongArgs */
	public function testGetValueForParameterWithLongOption(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);
		$param = self::createMock(Parameter::class);
		$param->method("getLongOption")->willReturn(substr($args[1], 2));
		$value = $argumentList->getValueForParameter($param);
		self::assertEquals($args[2], $value);
	}

	/** @dataProvider dataRandomShortArgs */
	public function testGetValueParameterWithShortOption(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);
		$param = self::createMock(Parameter::class);
		$param->method("getShortOption")->willReturn(substr($args[1], 1));
		$value = $argumentList->getValueForParameter($param);
		self::assertEquals($args[2], $value);
	}

	public function testGetValueForParameterWhenLongAndShortAreBothSet():void {
		$argumentList = new ArgumentList(
			"test-script",
			"test-command",
			"--dir",
			"/tmp/one",
			"-d",
			"/tmp/two"
		);

		$param = self::createMock(Parameter::class);
		$param->method("getLongOption")->willReturn("dir");
		$param->method("getShortOption")->willReturn("d");

		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage(
			"Parameter cannot be set by both --dir and -d"
		);
		$argumentList->getValueForParameter($param);
	}

	public static function dataRandomLongArgs():array {
		$dataSet = [];

		for($i = 0; $i < 10; $i++) {
			$params = [];
			$params[] = uniqid("script-");
			$params[] = uniqid("command-");

			$numParams = rand(1, 10);
			if($numParams % 2 !== 0) {
				$numParams++;
			}

			for($j = 0; $j < $numParams; $j++) {
				if($j % 2 === 0) {
					$params[] = "--" . uniqid();
				}
				else {
					$params[] = uniqid();
				}
			}

			$dataSet[] = $params;
		}

		return $dataSet;
	}

	public static function dataRandomShortArgs():array {
		$dataSet = [];

		for($i = 0; $i < 10; $i++) {
			$params = [];
			$params[] = uniqid("script-");
			$params[] = uniqid("command-");

			$numParams = rand(1, 10);
			if($numParams % 2 !== 0) {
				$numParams++;
			}

			for($j = 0; $j < $numParams; $j++) {
				if($j % 2 === 0) {
					$params[] = "-" . uniqid();
				}
				else {
					$params[] = uniqid();
				}
			}

			$dataSet[] = $params;
		}

		return $dataSet;
	}

	public static function dataRandomLongEqualsArgs():array {
		$dataSet = [];

		for($i = 0; $i < 10; $i++) {
			$params = [];
			$params[] = uniqid("script-");
			$params[] = uniqid("command-");

			$numParams = rand(1, 10);
			for($j = 0; $j < $numParams; $j++) {
				$params[] = "--" . uniqid() . "=" . uniqid();
			}

			$dataSet[] = $params;
		}

		return $dataSet;
	}

	public static function dataRandomShortEqualsArgs():array {
		$dataSet = [];

		for($i = 0; $i < 10; $i++) {
			$charArray = [
				"a", "b", "c", "d", "e", "f", "g", "h", "i",
				"j", "k", "l", "m", "n", "o", "p", "q", "r",
				"s", "t", "u", "v", "w", "x", "y", "z",
			];
			$params = [];
			$params[] = uniqid("script-");
			$params[] = uniqid("command-");

			$numParams = rand(1, 10);
			for($j = 0; $j < $numParams; $j++) {
				$char = array_shift($charArray);
				$params[] = "-" . $char . "=" . uniqid();
			}

			$dataSet[] = $params;
		}

		return $dataSet;
	}
}
