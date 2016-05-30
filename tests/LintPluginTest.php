<?php

namespace SLLH\ComposerLint\Tests;

use Composer\Composer;
use Composer\Config;
use Composer\EventDispatcher\EventDispatcher;
use Composer\IO\BufferIO;
use Composer\Package\RootPackage;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;
use Composer\Plugin\PluginManager;
use SLLH\ComposerLint\LintPlugin;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class LintPluginTest extends \PHPUnit_Framework_TestCase
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
     * @var Composer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $composer;

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
    }

    public function testValidateCommand()
    {
        $this->addComposerPlugin(new LintPlugin());

        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $input->expects($this->once())->method('getArgument')->with('file')
            ->willReturn(__DIR__.'/fixtures/composer.json');

        $commandEvent = new CommandEvent(PluginEvents::COMMAND, 'validate', $input, new NullOutput());

        $this->assertSame(1, $this->composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent));
        $this->assertSame(<<<'EOF'
Links under require section are not sorted.
Links under require-dev section are not sorted.
You must specifiy the PHP requirement.
The package type is not specified.

EOF
            , $this->io->getOutput());
    }

    public function testValidateWithConfigCommand()
    {
        $this->config->merge(array(
            'config' => array(
                'sllh-composer-lint' => array(
                    'php' => false,
                    'type' => false,
                ),
            ),
        ));

        $this->addComposerPlugin(new LintPlugin());

        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $input->expects($this->once())->method('getArgument')->with('file')
            ->willReturn(__DIR__.'/fixtures/composer.json');

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

        $input = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $input->expects($this->never())->method('getArgument')->with('file');

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
