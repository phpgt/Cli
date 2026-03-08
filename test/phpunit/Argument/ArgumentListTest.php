<?php
namespace Gt\Cli\Test\Argument;

use Gt\Cli\Argument\Argument;
use Gt\Cli\Argument\ArgumentList;
use Gt\Cli\Parameter\Parameter;
use PHPUnit\Framework\TestCase;

class ArgumentListTest extends TestCase {
	public function testGetCommandNameAndIteratorWithNamedArgs():void {
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

	public function testIteratorWithLongArgs():void {
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

	public function testIteratorWithShortArgs():void {
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

	public function testContainsWithLongAndShortArgs():void {
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

	public function testNotContains():void {
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

	public function testEqualsSignValueParsing():void {
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

	public function testGetValueForParameterWithLongAndShortOption():void {
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

	public function testContainsMultipleFlags():void {
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

	public function testGetValueForParameterWhenLongAndShortAreBothSet():void {
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

	public function testChainedShortOptions():void {
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
}
