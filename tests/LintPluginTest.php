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
use Symfony\Component\Console\Input\ArrayInput;
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

        $this->composer->setPluginManager(new PluginManager($this->io, $this->composer));
        $this->composer->setEventDispatcher(new EventDispatcher($this->composer, $this->io));
        $this->composer->setConfig(new Config(false));
        $this->composer->setPackage(new RootPackage('root/root', '1.0.0', '1.0.0'));
    }

    public function testValidateCommand()
    {
        $this->addComposerPlugin(new LintPlugin());

        $commandEvent = new CommandEvent(PluginEvents::COMMAND, 'validate', new ArrayInput(array()), new NullOutput());
        $ret = $this->composer->getEventDispatcher()->dispatch($commandEvent->getName(), $commandEvent);

        $this->assertSame(1, $ret);
        $this->assertSame(<<<'EOF'
This plugin is over development. Please do not use it for the moment.

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
