<?php
namespace GT\Cli\Test\Command;

use GT\Cli\Argument\ArgumentValueList;
use GT\Cli\Command\VersionCommand;
use GT\Cli\Stream;
use PHPUnit\Framework\TestCase;

class VersionCommandTest extends TestCase {
	public function testRunWritesVersionAndReturnsZero():void {
		$stream = new Stream("php://memory", "php://memory", "php://memory");
		$command = new class extends VersionCommand {
			protected function getVersion():string {
				return "1.2.3-test";
			}
		};
		$command->setStream($stream);

		$returnCode = $command->run(new ArgumentValueList());
		self::assertSame(0, $returnCode);

		$out = $stream->getOutStream();
		$out->rewind();
		self::assertStringContainsString("1.2.3-test", $out->fread(1024));
	}

	public function testPublicMetadataMethods():void {
		$command = new VersionCommand();
		self::assertSame("version", $command->getName());
		self::assertSame(
			"Get the version of the application",
			$command->getDescription()
		);
		self::assertSame([], $command->getRequiredNamedParameterList());
		self::assertSame([], $command->getOptionalNamedParameterList());
		self::assertSame([], $command->getRequiredParameterList());
		self::assertSame([], $command->getOptionalParameterList());
	}

	public function testGetVersionReturnsString():void {
		$command = new class extends VersionCommand {
			public function exposeVersion():string {
				return $this->getVersion();
			}
		};

		self::assertIsString($command->exposeVersion());
	}
}
