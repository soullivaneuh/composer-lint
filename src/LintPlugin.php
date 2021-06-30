<?php

namespace SLLH\ComposerLint;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Composer\Plugin\CommandEvent;
use Composer\Plugin\PluginEvents;
use Composer\Plugin\PluginInterface;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class LintPlugin implements PluginInterface, EventSubscriberInterface
{
    private Composer $composer;

    private IOInterface $io;

    private Linter $linter;

    /**
     * {@inheritdoc}
     */
    public function activate(Composer $composer, IOInterface $io)
    {
        $this->composer = $composer;
        $this->io = $io;
        $config = $this->composer->getConfig()->get('sllh-composer-lint');
        $this->linter = new Linter($config ?: array());
    }

    /**
     * {@inheritdoc}
     */
    public function deactivate(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(Composer $composer, IOInterface $io)
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            PluginEvents::COMMAND => array(
                array('command'),
            ),
        );
    }

    /**
     * @return bool true if no violation, false otherwise
     */
    public function command(CommandEvent $event)
    {
        if ('validate' !== $event->getCommandName()) {
            return true;
        }

        $file = $event->getInput()->getArgument('file') ?: Factory::getComposerFile();
        $json = new JsonFile($file);
        $manifest = $json->read();
        $locker = $this->composer->getLocker();
        $lockData = $locker && $locker->isLocked() ? $locker->getLockData() : array();

        $errors = $this->linter->validate($manifest, $lockData);

        foreach ($errors as $error) {
            $this->io->writeError(sprintf('<error>%s</error>', $error));
        }

        return empty($errors);
    }
}
