<?php

namespace SLLH\ComposerLint\Tests;

use Composer\Command\ValidateCommand;
use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventDispatcher;
use Composer\IO\BufferIO;
use Composer\Package\RootPackage;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PluginManager;
use PHPUnit\Framework\TestCase;
use SLLH\ComposerLint\LintPlugin;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class LintPluginTest extends TestCase
{
    /**
     * @var BufferIO
     */
    private $io;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Composer
     */
    private $composer;

    /**
     * @var ValidateCommand
     */
    private $validateCommand;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->io = new BufferIO();
        $this->composer = new Composer();
        $this->config = new Config(false);

        $this->composer->setPluginManager(new PluginManager($this->io, $this->composer));
        $this->composer->setEventDispatcher(new EventDispatcher($this->composer, $this->io));
        $this->composer->setConfig($this->config);
        $this->composer->setPackage(new RootPackage('root/root', '1.0.0', '1.0.0'));

        $this->validateCommand = new ValidateCommand();
    }

    public function testValidateCommand()
    {
        $this->addComposerPlugin(new LintPlugin());

        $input = new ArrayInput(array(
            'file' => __DIR__.'/fixtures/composer.json',
        ), $this->validateCommand->getDefinition());

        $commandEvent = new CommandEvent(PluginEvents::COMMAND, 'validate', $input, new NullOutput());

        $this->assertSame(1, $this->composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent));
        $this->assertSame(<<<'EOF'
Links under require section are not sorted.
Links under require-dev section are not sorted.
You must specifiy the PHP requirement.
The package type is not specified.
Requirement format of 'sllh/php-cs-fixer-styleci-bridge:~2.0' is not valid. Should be '^2.0'.

EOF
            , $this->io->getOutput());
    }

    public function testValidateWithComposerEnvVariable()
    {
        if (version_compare(Composer::VERSION, '1.6.0', '<')) {
            $this->markTestSkipped('Need composer >=1.6');
        }

        putenv('COMPOSER='.__DIR__.'/fixtures/composer.json');

        $this->addComposerPlugin(new LintPlugin());

        $input = new ArrayInput(array(), $this->validateCommand->getDefinition());

        $commandEvent = new CommandEvent(PluginEvents::COMMAND, 'validate', $input, new NullOutput());

        $this->assertSame(1, $this->composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent));
        $this->assertSame(<<<'EOF'
Links under require section are not sorted.
Links under require-dev section are not sorted.
You must specifiy the PHP requirement.
The package type is not specified.
Requirement format of 'sllh/php-cs-fixer-styleci-bridge:~2.0' is not valid. Should be '^2.0'.

EOF
            , $this->io->getOutput());
        putenv('COMPOSER'); // Be sure to be removed for the other tests.
    }

    public function testValidateWithConfigCommand()
    {
        $this->config->merge(array(
            'config' => array(
                'sllh-composer-lint' => array(
                    'php' => false,
                    'type' => false,
                    'version-constraints' => false,
                ),
            ),
        ));

        $this->addComposerPlugin(new LintPlugin());

        $input = new ArrayInput(array(
            'file' => __DIR__.'/fixtures/composer.json',
        ), $this->validateCommand->getDefinition());

        $commandEvent = new CommandEvent(PluginEvents::COMMAND, 'validate', $input, new NullOutput());

        $this->assertSame(1, $this->composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent));
        $this->assertSame(<<<'EOF'
Links under require section are not sorted.
Links under require-dev section are not sorted.

EOF
            , $this->io->getOutput());
    }

    /**
     * The plugin should not be executed at all.
     */
    public function testDummyCommand()
    {
        $this->addComposerPlugin(new LintPlugin());

        $input = new ArrayInput(array(), $this->validateCommand->getDefinition());

        $commandEvent = new CommandEvent(PluginEvents::COMMAND, 'dummy', $input, new NullOutput());

        $this->assertSame(0, $this->composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent));
        $this->assertSame(<<<'EOF'

EOF
            , $this->io->getOutput());
    }

    private function addComposerPlugin(PluginInterface $plugin)
    {
        $pluginManagerReflection = new \ReflectionClass($this->composer->getPluginManager());
        $addPluginReflection = $pluginManagerReflection->getMethod('addPlugin');
        $addPluginReflection->setAccessible(true);
        $addPluginReflection->invoke($this->composer->getPluginManager(), $plugin);
    }
}
