<?php
namespace GT\Cli\Test\Argument;

use GT\Cli\Argument\ArgumentList;
use GT\Cli\Parameter\Parameter;
use PHPUnit\Framework\TestCase;

class ArgumentListContainsProviderTest extends TestCase {
	/** @dataProvider dataRandomShortArgs */
	public function testContainsWithShortArgs(string...$args):void {
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

	/** @dataProvider dataRandomLongArgs */
	public function testContainsWithLongArgs(string...$args):void {
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

	/** @dataProvider dataRandomLongArgs */
	public function testNotContains(string...$args):void {
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
