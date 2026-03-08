<?php
namespace Gt\Cli\Test\Helper;

use ArrayIterator;
use Gt\Cli\Argument\ArgumentList;
use Gt\Cli\Parameter\Parameter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ArgumentMockTestCase extends TestCase {
	protected function createIteratorMock(
		string $className,
		array $items = []
	):MockObject {
		$mock = $this->createMock($className);
		$iterator = new ArrayIterator($items);

		$mock->method("rewind")
			->willReturnCallback(function()use($iterator) {
				$iterator->rewind();
			});
		$mock->method("current")
			->willReturnCallback(function()use($iterator) {
				return $iterator->current();
			});
		$mock->method("key")
			->willReturnCallback(function()use($iterator) {
				return $iterator->key();
			});
		$mock->method("next")
			->willReturnCallback(function()use($iterator) {
				$iterator->next();
			});
		$mock->method("valid")
			->willReturnCallback(function()use($iterator) {
				return $iterator->valid();
			});

		return $mock;
	}

	protected function createArgumentListMock(
		array $items = [],
		array $longArgs = []
	):MockObject {
		$argList = $this->createIteratorMock(
			ArgumentList::class,
			$items
		);

		$argList->method("contains")
			->willReturnCallback(function(Parameter $param)use($longArgs) {
				$longOption = $param->getLongOption();
				foreach($longArgs as $a) {
					if(is_array($a)) {
						if(key($a) === $longOption) {
							return true;
						}
					}
					else {
						if($a === $longOption) {
							return true;
						}
					}
				}
				return false;
			});

		$argList->method("getValueForParameter")
			->willReturnCallback(function(Parameter $param)use($longArgs) {
				$longOption = $param->getLongOption();
				foreach($longArgs as $a) {
					if(!is_array($a)) {
						continue;
					}

					$key = key($a);
					if($key !== $longOption) {
						continue;
					}

					return $a[$key];
				}
				return null;
			});

		return $argList;
	}
}
