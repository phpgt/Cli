<?php
namespace Gt\Cli\Test\Argument;

use Gt\Cli\Argument\Argument;
use Gt\Cli\Argument\ArgumentList;
use Gt\Cli\Parameter\Parameter;
use PHPUnit\Framework\TestCase;

class ArgumentListTest extends TestCase {
	/** @dataProvider data_randomNamedArgs */
	public function testGetCommandName(string...$args) {
		$argumentList = new ArgumentList(
			array_shift($args),
			...$args
		);
		self::assertEquals(
			$args[0],
			$argumentList->getCommandName()
		);
	}

	/** @dataProvider data_randomNamedArgs */
	public function testIteratorWithNamedArgs(string...$args) {
		$argumentList = new ArgumentList(
			array_shift($args),
			...$args
		);

		foreach($argumentList as $i => $argument) {
			/** @var Argument $argument */
			self::assertInstanceOf(
				Argument::class,
				$argument
			);

			self::assertEquals(
				$args[$i],
				$argument
			);
		}
	}

	/** @dataProvider data_randomLongArgs */
	public function testIteratorWithLongArgs(string...$args) {
		$scriptName = array_shift($args);
		$argumentList = new ArgumentList(
			$scriptName,
			...$args
		);

		foreach($argumentList as $i => $argument) {
			/** @var Argument $argument */
			self::assertInstanceOf(
				Argument::class,
				$argument
			);

			if($i === 0) {
				self::assertEquals(
					$args[0],
					$argument
				);
				continue;
			}

			$originalKey = $args[($i - 1) * 2 + 1];
			$originalValue = $args[($i - 1) * 2 + 2];

			self::assertEquals(
				substr($originalKey, 2),
				$argument->getKey()
			);
			self::assertEquals(
				$originalValue,
				$argument->getValue()
			);
		}
	}

	/** @dataProvider data_randomShortArgs */
	public function testIteratorWithShortArgs(string...$args) {
		$argumentList = new ArgumentList(
			array_shift($args),
			...$args
		);

		foreach($argumentList as $i => $argument) {
			/** @var Argument $argument */
			self::assertInstanceOf(
				Argument::class,
				$argument
			);

			if($i === 0) {
				self::assertEquals(
					$args[0],
					$argument
				);
				continue;
			}

			$originalKey = $args[($i - 1) * 2 + 1];
			$originalValue = $args[($i - 1) * 2 + 2];

			self::assertEquals(
				substr($originalKey, 1),
				$argument->getKey()
			);
			self::assertEquals(
				$originalValue,
				$argument->getValue()
			);
		}
	}

	/** @dataProvider data_randomShortArgs */
	public function testContainsWithShortArgs(string...$args) {
		$argumentList = new ArgumentList(
			array_shift($args),
			...$args
		);

		foreach($args as $i => $arg) {
			if($i === 0) {
				continue;
			}

			if($i % 2 === 0) {
// Because args are passed in as "--key value" every other original arg is skipped.
				continue;
			}

			$param = self::createMock(Parameter::class);
			$param->method("getShortOption")
				->willReturn(substr($arg, 1));

			/** @var Parameter $param */
			self::assertTrue($argumentList->contains($param));
		}
	}

	/** @dataProvider data_randomLongArgs */
	public function testContainsWithLongArgs(string...$args) {
		$argumentList = new ArgumentList(
			array_shift($args),
			...$args
		);

		foreach($args as $i => $arg) {
			if($i === 0) {
				continue;
			}

			if($i % 2 === 0) {
// Because args are passed in as "--key value" every other original arg is skipped.
				continue;
			}

			$param = self::createMock(Parameter::class);
			$param->method("getLongOption")
				->willReturn(substr($arg, 2));

			/** @var Parameter $param */
			self::assertTrue($argumentList->contains($param));
		}
	}

	/** @dataProvider data_randomLongArgs */
	public function testNotContains(string...$args) {
		$argumentList = new ArgumentList(
			array_shift($args),
			...$args
		);

		foreach($args as $i => $arg) {
			if($i === 0) {
				continue;
			}

			if($i % 2 === 0) {
// Because args are passed in as "--key value" every other original arg is skipped.
				continue;
			}

			$param = self::createMock(Parameter::class);
			$param->method("getLongOption")
				->willReturn(uniqid());

			/** @var Parameter $param */
			self::assertFalse($argumentList->contains($param));
		}
	}

	/** @dataProvider data_randomLongEqualsArgs */
	public function testKeyValueSetWithLongOptionEqualsSign(string...$args) {
		$argumentList = new ArgumentList(
			array_shift($args),
			...$args
		);

		foreach($args as $i => $arg) {
			if($i === 0) {
				continue;
			}

			$arg = substr($arg, 2);
			list($key, $value) = explode("=", $arg);

			$param = self::createMock(Parameter::class);
			$param->method("getLongOption")
				->willReturn($key);

			self::assertEquals(
				$value,
				$argumentList->getValueForParameter($param)
			);
		}
	}

	/** @dataProvider data_randomShortEqualsArgs */
	public function testKeyValueSetWithShortOptionEqualsSign(string...$args) {
		$argumentList = new ArgumentList(
			array_shift($args),
			...$args
		);

		foreach($args as $i => $arg) {
			if($i === 0) {
				continue;
			}

			$arg = substr($arg, 1);
			list($key, $value) = explode("=", $arg);

			$param = self::createMock(Parameter::class);
			$param->method("getShortOption")
				->willReturn($key);

			$paramValue = $argumentList->getValueForParameter($param);

			if($paramValue != $value) {

				var_dump($args);die();
			}

			self::assertEquals(
				$value,
				$paramValue
			);
		}
	}

	/** @dataProvider data_randomShortEqualsArgs */
	public function testGetValueForParameterNotExists(string...$args) {
		$argumentList = new ArgumentList(
			array_shift($args),
			...$args
		);

		foreach($args as $i => $arg) {
			if($i === 0) {
				continue;
			}

			$arg = substr($arg, 1);
			list($key, $value) = explode("=", $arg);

			$param = self::createMock(Parameter::class);
			$param->method("getShortOption")
				->willReturn("Z");

			self::assertNull($argumentList->getValueForParameter($param));
		}
	}

	/** @dataProvider data_randomLongArgs */
	public function testGetValueForParameterWithLongOption(string...$args) {
		$argumentList = new ArgumentList(
			array_Shift($args),
			...$args
		);

		$param = self::createMock(Parameter::class);
		$param->method("getLongOption")
			->willReturn(substr($args[1], 2));
		$value = $argumentList->getValueForParameter($param);

		self::assertEquals($args[2], $value);
	}

	/** @dataProvider data_randomShortArgs */
	public function testGetValueParameterWithShortOption(string...$args) {
		$argumentList = new ArgumentList(
			array_shift($args),
			...$args
		);

		$param = self::createMock(Parameter::class);
		$param->method("getShortOption")
			->willReturn(substr($args[1], 1));
		$value = $argumentList->getValueForParameter($param);

		self::assertEquals($args[2], $value);
	}

	public function testGetValueForParameterForMultiple() {
		$argumentList = new ArgumentList(
			"test-script",
			"test-command",
			"--one",
			"--two",
			"--three",
			"--four"
		);

		$param1 = self::createMock(Parameter::class);
		$param1->method("getLongOption")->willReturn("one");
		$param2 = self::createMock(Parameter::class);
		$param2->method("getLongOption")->willReturn("two");
		$param3 = self::createMock(Parameter::class);
		$param3->method("getLongOption")->willReturn("three");
		$param4 = self::createMock(Parameter::class);
		$param4->method("getLongOption")->willReturn("four");

		self::assertTrue($argumentList->contains($param1));
		self::assertTrue($argumentList->contains($param2));
		self::assertTrue($argumentList->contains($param3));
		self::assertTrue($argumentList->contains($param4));
	}

	public static function data_randomNamedArgs():array {
		$dataSet = [];

		for($i = 0; $i < 10; $i++) {
			$params = [];

			$params []= uniqid("script-");
			$params []= uniqid("command-");

			$numParams = rand(1, 10);
			if($numParams % 2 !== 0) {
				$numParams ++;
			}

			for($j = 0; $j < $numParams; $j++) {
				$params []= uniqid();
			}

			$dataSet []= $params;
		}

		return $dataSet;
	}

	public static function data_randomLongArgs():array {
		$dataSet = [];

		for($i = 0; $i < 10; $i++) {
			$params = [];

			$params []= uniqid("script-");
			$params []= uniqid("command-");

			$numParams = rand(1, 10);
			if($numParams % 2 !== 0) {
				$numParams ++;
			}

			for($j = 0; $j < $numParams; $j++) {
				if($j % 2 === 0) {
					$params []= "--" . uniqid();
				}
				else {
					$params []= uniqid();
				}
			}

			$dataSet []= $params;
		}

		return $dataSet;
	}

	public static function data_randomShortArgs():array {
		$dataSet = [];

		for($i = 0; $i < 10; $i++) {
			$params = [];

			$params []= uniqid("script-");
			$params []= uniqid("command-");

			$numParams = rand(1, 10);
			if($numParams % 2 !== 0) {
				$numParams ++;
			}

			for($j = 0; $j < $numParams; $j++) {
				if($j % 2 === 0) {
					$params []= "-" . uniqid();
				}
				else {
					$params []= uniqid();
				}
			}

			$dataSet []= $params;
		}

		return $dataSet;
	}

	public static function data_randomLongEqualsArgs():array {
		$dataSet = [];

		for($i = 0; $i < 10; $i++) {
			$params = [];

			$params []= uniqid("script-");
			$params []= uniqid("command-");

			$numParams = rand(1, 10);
			for($j = 0; $j < $numParams; $j++) {
				$params []= "--" . uniqid() . "=" . uniqid();
			}

			$dataSet []= $params;
		}

		return $dataSet;
	}

	public static function data_randomShortEqualsArgs():array {
		$dataSet = [];

		for($i = 0; $i < 10; $i++) {
			$charArray = ["a","b","c","d","e","f","g","h","i","j","k","l","m","n","o","p","q","r","s","t","u","v","w","x","y","z"];
			$params = [];

			$params []= uniqid("script-");
			$params []= uniqid("command-");

			$numParams = rand(1, 10);
			for($j = 0; $j < $numParams; $j++) {
				$char = array_shift($charArray);

				$params []= "-"
					. $char
					. "="
					. uniqid();
			}

			$dataSet []= $params;
		}

		return $dataSet;
	}
}
