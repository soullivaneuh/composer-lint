<?php

namespace SLLH\ComposerLint;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class Linter
{
    /**
     * @var array
     */
    private $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $defaultConfig = array(
            'php' => true,
        );

        $this->config = array_merge($defaultConfig, $config);
    }

    /**
     * @param array $manifest composer.json file manifest
     *
     * @return string[]
     */
    public function validate($manifest)
    {
        $errors = array();

        if (isset($manifest['config']['sort-packages']) && $manifest['config']['sort-packages']) {
            foreach (array('require', 'require-dev', 'conflict', 'replace', 'provide', 'suggest') as $linksSection) {
                if (array_key_exists($linksSection, $manifest) && !$this->packagesAreSorted($manifest[$linksSection])) {
                    array_push($errors, 'Links under '.$linksSection.' section are not sorted.');
                }
            }
        }

        if (true === $this->config['php'] &&
            (array_key_exists('require-dev', $manifest) || array_key_exists('require', $manifest))) {
            $isOnRequireDev = array_key_exists('require-dev', $manifest) && array_key_exists('php', $manifest['require-dev']);
            $isOnRequire = array_key_exists('require', $manifest) && array_key_exists('php', $manifest['require']);

            if ($isOnRequireDev) {
                array_push($errors, 'PHP requirement should be in the require section, not in the require-dev section.');
            } elseif (!$isOnRequire) {
                array_push($errors, 'You must specifiy the PHP requirement.');
            }
        }

        return $errors;
    }

    private function packagesAreSorted(array $packages)
    {
        $names = array_keys($packages);

        $hasPHP = in_array('php', $names);
        $extNames = array_filter($names, function ($name) {
            return 'ext-' === substr($name, 0, 4) && !strstr($name, '/');
        });
        sort($extNames);
        $vendorName = array_filter($names, function ($name) {
            return 'ext-' !== substr($name, 0, 4) && 'php' !== $name;
        });
        sort($vendorName);

        $sortedNames = array_merge(
            $hasPHP ? array('php') : array(),
            $extNames,
            $vendorName
        );

        return $sortedNames === $names;
    }
}
