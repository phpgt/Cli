<?php
namespace GT\Cli\Test\Argument;

use GT\Cli\Argument\Argument;
use GT\Cli\Argument\ArgumentList;
use PHPUnit\Framework\TestCase;

class ArgumentListIteratorProviderTest extends TestCase {
	/** @dataProvider dataRandomNamedArgs */
	public function testGetCommandName(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);
		self::assertEquals($args[0], $argumentList->getCommandName());
	}

	/** @dataProvider dataRandomNamedArgs */
	public function testIteratorWithNamedArgs(string...$args):void {
		$argumentList = new ArgumentList(array_shift($args), ...$args);

		foreach($argumentList as $i => $argument) {
			/** @var Argument $argument */
			self::assertInstanceOf(Argument::class, $argument);
			self::assertEquals($args[$i], $argument);
		}
	}

	/** @dataProvider dataRandomLongArgs */
	public function testIteratorWithLongArgs(string...$args):void {
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

	/** @dataProvider dataRandomShortArgs */
	public function testIteratorWithShortArgs(string...$args):void {
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

	public static function dataRandomNamedArgs():array {
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
}
