<?php
namespace GT\Cli\Test\Argument;

use GT\Cli\Argument\Argument;
use GT\Cli\Argument\ArgumentList;
use GT\Cli\Parameter\Parameter;
use PHPUnit\Framework\TestCase;

class ArgumentListTest extends TestCase {
	public function testGetCommandName_andIteratorWithNamedArgs():void {
		$argumentList = new ArgumentList(
			"script",
			"command",
			"first",
			"second"
		);

		self::assertSame("script", $argumentList->getScript());
		self::assertSame("command", $argumentList->getCommandName());

		$actual = [];
		foreach($argumentList as $argument) {
			/** @var Argument $argument */
			$actual[] = (string)$argument;
		}

		self::assertSame([
			"command",
			"first",
			"second",
		], $actual);
	}

	public function testCurrent_longArgs():void {
		$argumentList = new ArgumentList(
			"script",
			"command",
			"--one",
			"1",
			"--two",
			"2"
		);

		$arguments = iterator_to_array($argumentList);
		self::assertSame("one", $arguments[1]->getKey());
		self::assertSame("1", $arguments[1]->getValue());
		self::assertSame("two", $arguments[2]->getKey());
		self::assertSame("2", $arguments[2]->getValue());
	}

	public function testCurrent_shortArgs():void {
		$argumentList = new ArgumentList(
			"script",
			"command",
			"-a",
			"1",
			"-b",
			"2"
		);

		$arguments = iterator_to_array($argumentList);
		self::assertSame("a", $arguments[1]->getKey());
		self::assertSame("1", $arguments[1]->getValue());
		self::assertSame("b", $arguments[2]->getKey());
		self::assertSame("2", $arguments[2]->getValue());
	}

	public function testContains_withLongAndShortArgs():void {
		$argumentList = new ArgumentList(
			"script",
			"command",
			"--verbose",
			"-d"
		);

		$longParam = $this->createMock(Parameter::class);
		$longParam->method("getLongOption")->willReturn("verbose");
		$shortParam = $this->createMock(Parameter::class);
		$shortParam->method("getShortOption")->willReturn("d");

		self::assertTrue($argumentList->contains($longParam));
		self::assertTrue($argumentList->contains($shortParam));
	}

	public function testContains_missing():void {
		$argumentList = new ArgumentList(
			"script",
			"command",
			"--one",
			"1"
		);

		$param = $this->createMock(Parameter::class);
		$param->method("getLongOption")->willReturn("missing");
		self::assertFalse($argumentList->contains($param));
	}

	public function testGetValueForParameter_equalsSign():void {
		$argumentList = new ArgumentList(
			"script",
			"command",
			"--name=value",
			"-a=one"
		);

		$longParam = $this->createMock(Parameter::class);
		$longParam->method("getLongOption")->willReturn("name");
		$shortParam = $this->createMock(Parameter::class);
		$shortParam->method("getShortOption")->willReturn("a");

		self::assertSame("value", $argumentList->getValueForParameter($longParam));
		self::assertSame("one", $argumentList->getValueForParameter($shortParam));
	}

	public function testGetValueForParameter_withLongAndShortOption():void {
		$argumentList = new ArgumentList(
			"script",
			"command",
			"--path",
			"/tmp/a",
			"-f",
			"/tmp/b"
		);

		$longParam = $this->createMock(Parameter::class);
		$longParam->method("getLongOption")->willReturn("path");
		$shortParam = $this->createMock(Parameter::class);
		$shortParam->method("getShortOption")->willReturn("f");

		self::assertSame("/tmp/a", $argumentList->getValueForParameter($longParam));
		self::assertSame("/tmp/b", $argumentList->getValueForParameter($shortParam));
	}

	public function testContains_multipleFlags():void {
		$argumentList = new ArgumentList(
			"script",
			"command",
			"--one",
			"--two",
			"--three",
			"--four"
		);

		foreach(["one", "two", "three", "four"] as $name) {
			$param = $this->createMock(Parameter::class);
			$param->method("getLongOption")->willReturn($name);
			self::assertTrue($argumentList->contains($param));
		}
	}

	public function testGetValueForParameter_whenLongAndShortAreBothSet():void {
		$argumentList = new ArgumentList(
			"test-script",
			"test-command",
			"--dir",
			"/tmp/one",
			"-d",
			"/tmp/two"
		);

		$param = $this->createMock(Parameter::class);
		$param->method("getLongOption")->willReturn("dir");
		$param->method("getShortOption")->willReturn("d");

		$this->expectException(\LogicException::class);
		$this->expectExceptionMessage(
			"Parameter cannot be set by both --dir and -d"
		);
		$argumentList->getValueForParameter($param);
	}

	public function testCurrent_chainedShortOptions():void {
		$argumentList = new ArgumentList(
			"test-script",
			"test-command",
			"-czf",
			"myarchive.tar.gz"
		);

		$paramC = $this->createMock(Parameter::class);
		$paramC->method("getShortOption")->willReturn("c");
		$paramZ = $this->createMock(Parameter::class);
		$paramZ->method("getShortOption")->willReturn("z");
		$paramF = $this->createMock(Parameter::class);
		$paramF->method("getShortOption")->willReturn("f");

		self::assertTrue($argumentList->contains($paramC));
		self::assertTrue($argumentList->contains($paramZ));
		self::assertTrue($argumentList->contains($paramF));
		self::assertNull($argumentList->getValueForParameter($paramC));
		self::assertNull($argumentList->getValueForParameter($paramZ));
		self::assertSame(
			"myarchive.tar.gz",
			$argumentList->getValueForParameter($paramF)
		);
	}

	/** @dataProvider dataContainsProviderRandomShortArgs */
	public function testContains_generatedShortArgs(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);

		foreach($args as $i => $arg) {
			if($i === 0 || $i % 2 === 0) {
				continue;
			}

			$param = self::createMock(Parameter::class);
			$param->method("getShortOption")
				->willReturn(substr($arg, 1));
			self::assertTrue($argumentList->contains($param));
		}
	}

	/** @dataProvider dataContainsProviderRandomLongArgs */
	public function testContains_generatedLongArgs(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);

		foreach($args as $i => $arg) {
			if($i === 0 || $i % 2 === 0) {
				continue;
			}

			$param = self::createMock(Parameter::class);
			$param->method("getLongOption")
				->willReturn(substr($arg, 2));
			self::assertTrue($argumentList->contains($param));
		}
	}

	/** @dataProvider dataContainsProviderRandomLongArgs */
	public function testContains_missingGeneratedArgs(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);

		foreach(array_keys($args) as $i) {
			if($i === 0 || $i % 2 === 0) {
				continue;
			}

			$param = self::createMock(Parameter::class);
			$param->method("getLongOption")
				->willReturn(uniqid());
			self::assertFalse($argumentList->contains($param));
		}
	}

	public static function dataContainsProviderRandomLongArgs():array {
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

	public static function dataContainsProviderRandomShortArgs():array {
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

	/** @dataProvider dataIteratorProviderRandomNamedArgs */
	public function testGetCommandName_generatedArgs(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);
		self::assertEquals($args[0], $argumentList->getCommandName());
	}

	/** @dataProvider dataIteratorProviderRandomNamedArgs */
	public function testCurrent_generatedNamedArgs(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);

		foreach($argumentList as $i => $argument) {
			/** @var Argument $argument */
			self::assertInstanceOf(Argument::class, $argument);
			self::assertEquals($args[$i], $argument);
		}
	}

	/** @dataProvider dataIteratorProviderRandomLongArgs */
	public function testCurrent_generatedLongArgs(string...$args):void {
		$scriptName = array_shift($args);
		$argumentList = new ArgumentList($scriptName, ...$args);

		foreach($argumentList as $i => $argument) {
			/** @var Argument $argument */
			self::assertInstanceOf(Argument::class, $argument);

			if($i === 0) {
				self::assertEquals($args[0], $argument);
				continue;
			}

			$originalKey = $args[($i - 1) * 2 + 1];
			$originalValue = $args[($i - 1) * 2 + 2];
			self::assertEquals(substr($originalKey, 2), $argument->getKey());
			self::assertEquals($originalValue, $argument->getValue());
		}
	}

	/** @dataProvider dataIteratorProviderRandomShortArgs */
	public function testCurrent_generatedShortArgs(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);

		foreach($argumentList as $i => $argument) {
			/** @var Argument $argument */
			self::assertInstanceOf(Argument::class, $argument);

			if($i === 0) {
				self::assertEquals($args[0], $argument);
				continue;
			}

			$originalKey = $args[($i - 1) * 2 + 1];
			$originalValue = $args[($i - 1) * 2 + 2];
			self::assertEquals(substr($originalKey, 1), $argument->getKey());
			self::assertEquals($originalValue, $argument->getValue());
		}
	}

	public static function dataIteratorProviderRandomNamedArgs():array {
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
				$params[] = uniqid();
			}

			$dataSet[] = $params;
		}

		return $dataSet;
	}

	public static function dataIteratorProviderRandomLongArgs():array {
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

	public static function dataIteratorProviderRandomShortArgs():array {
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

	/** @dataProvider dataValueProviderRandomLongEqualsArgs */
	public function testGetValueForParameter_generatedLongEqualsArgs(string...$args):void {
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

	/** @dataProvider dataValueProviderRandomShortEqualsArgs */
	public function testGetValueForParameter_generatedShortEqualsArgs(string...$args):void {
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

	/** @dataProvider dataValueProviderRandomShortEqualsArgs */
	public function testGetValueForParameter_notExists(string...$args):void {
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

	/** @dataProvider dataValueProviderRandomLongArgs */
	public function testGetValueForParameter_generatedLongOption(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);
		$param = self::createMock(Parameter::class);
		$param->method("getLongOption")->willReturn(substr($args[1], 2));
		$value = $argumentList->getValueForParameter($param);
		self::assertEquals($args[2], $value);
	}

	/** @dataProvider dataValueProviderRandomShortArgs */
	public function testGetValueForParameter_generatedShortOption(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);
		$param = self::createMock(Parameter::class);
		$param->method("getShortOption")->willReturn(substr($args[1], 1));
		$value = $argumentList->getValueForParameter($param);
		self::assertEquals($args[2], $value);
	}

	public function testGetValueForParameter_bothOptionsSetProvider():void {
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

	public static function dataValueProviderRandomLongArgs():array {
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

	public static function dataValueProviderRandomShortArgs():array {
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

	public static function dataValueProviderRandomLongEqualsArgs():array {
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

	public static function dataValueProviderRandomShortEqualsArgs():array {
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
