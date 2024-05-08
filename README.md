Command line interface builder.
===============================

Create multi-command terminal application with parameter requirements that are self-documenting and easy to unit test.

***

<a href="https://github.com/PhpGt/Cli/actions" target="_blank">
	<img src="https://badge.status.php.gt/cli-build.svg" alt="Build status" />
</a>
<a href="https://app.codacy.com/gh/PhpGt/Cli" target="_blank">
	<img src="https://badge.status.php.gt/cli-quality.svg" alt="Code quality" />
</a>
<a href="https://app.codecov.io/gh/PhpGt/Cli" target="_blank">
	<img src="https://badge.status.php.gt/cli-coverage.svg" alt="Code coverage" />
</a>
<a href="https://packagist.org/packages/PhpGt/Cli" target="_blank">
	<img src="https://badge.status.php.gt/cli-version.svg" alt="Current version" />
</a>
<a href="https://www.php.gt/cli" target="_blank">
	<img src="https://badge.status.php.gt/cli-docs.svg" alt="PHP.G/Cli documentation" />
</a>

## Example usage: Twitter client

CLI interaction:

```bash
$ twitter tweet --message "Sending a test Tweet from the terminal."
Sent! View online: https://twitter.com/g105b/status/1038509073346510849
$ twitter dm --to @g105b --message "Hello, Greg!"
Sent!
$ twitter help
Twitter example application

Available commands:
• tweet		Send a Tweet to your timeline.
• view		View your timeline
• follow	Follow an account
• dm		Send a direct message.
• login		Authenticate your username.
• help		Show this help screen.
```

`twitter.php:`

```php
$app = new Application(
	"Twitter example application",
	new CliArgumentList(...$argv),
	new TweetCommand(),
	new ViewCommand(),
	new FollowCommand(),
	new DmCommand(),
	new LoginCommand()
);
$app->run();
```

`Command/tweet.php`

```php
class TweetCommand extends Command {
	public function __construct() {
		$this->setName("tweet");
		$this->setDescription("Send a Tweet to your timeline.");

		$this->setRequiredParameter(true, "message", "m");
		$this->setOptionalParameter(true, "location", "l");
	}

	public function run(ArgumentValueList $arguments):void {
		if(!TwitterApi::isLoggedIn()) {
			$this->writeLine("You must login first.", Stream::ERROR);
		}
		
		try {
			$uri = TwitterApi::sendTweet($arguments->get("message"));
			$this->writeLine("Sent! View online: $uri");
		}
		catch(TwitterApiException $exception) {
			$this->writeLine(
				"Error sending Tweet: "
				. $exception->getMessage(),
				Stream::ERROR
			);
		}
	}
}
```
