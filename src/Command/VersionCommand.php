<?php
namespace GT\Cli\Command;

use Composer\InstalledVersions;
use GT\Cli\Argument\ArgumentValueList;
use GT\Cli\Parameter\NamedParameter;
use GT\Cli\Parameter\Parameter;

class VersionCommand extends Command {
	// phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
	public function run(?ArgumentValueList $arguments = null):int {
		$this->writeLine($this->getVersion());
		return 0;
	}

	public function getName():string {
		return "version";
	}

	public function getDescription():string {
		return "Get the version of the application";
	}

	/** @return  NamedParameter[] */
	public function getRequiredNamedParameterList():array {
		return [];
	}

	/** @return  NamedParameter[] */
	public function getOptionalNamedParameterList():array {
		return [];
	}

	/** @return  Parameter[] */
	public function getRequiredParameterList():array {
		return [];
	}

	/** @return  Parameter[] */
	public function getOptionalParameterList():array {
		return [];
	}

	/** @SuppressWarnings(PHPMD.StaticAccess) */
	protected function getVersion():string {
		$package = InstalledVersions::getRootPackage()["name"];
		return InstalledVersions::getVersion($package) ?? "";
	}
}
