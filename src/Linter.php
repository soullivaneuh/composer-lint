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
            'type' => true,
            'minimum-stability' => true,
            'version-constraints' => true,
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
        $linksSections = array('require', 'require-dev', 'conflict', 'replace', 'provide', 'suggest');

        if (isset($manifest['config']['sort-packages']) && $manifest['config']['sort-packages']) {
            foreach ($linksSections as $linksSection) {
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

        if (true === $this->config['type'] && !array_key_exists('type', $manifest)) {
            array_push($errors, 'The package type is not specified.');
        }

        if (true === $this->config['minimum-stability'] && array_key_exists('minimum-stability', $manifest) &&
            array_key_exists('type', $manifest) && 'project' !== $manifest['type']) {
            array_push($errors, 'The minimum-stability should be only used for project packages.');
        }

        if (true === $this->config['version-constraints']) {
            foreach ($linksSections as $linksSection) {
                if (array_key_exists($linksSection, $manifest)) {
                    $errors = array_merge($errors, $this->validateVersionConstraints($manifest[$linksSection]));
                }
            }
        }

        return $errors;
    }

    private function packagesAreSorted(array $packages)
    {
        $names = array_keys($packages);

        $hasPHP = in_array('php', $names, true);
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

    /**
     * @param string[] $packages
     *
     * @return array
     */
    private function validateVersionConstraints(array $packages)
    {
        $errors = array();

        foreach ($packages as $name => $constraint) {
            // Checks if OR format is correct
            // From Composer\Semver\VersionParser::parseConstraints
            $orConstraints = preg_split('{\s*\|\|?\s*}', trim($constraint));
            foreach ($orConstraints as &$subConstraint) {
                // Checks ~ usage
                $subConstraint = str_replace('~', '^', $subConstraint);

                // Checks for usage like ^2.1,>=2.1.5. Should be ^2.1.5.
                // From Composer\Semver\VersionParser::parseConstraints
                $andConstraints = preg_split('{(?<!^|as|[=>< ,]) *(?<!-)[, ](?!-) *(?!,|as|$)}', $subConstraint);
                if (2 === count($andConstraints) && '>=' === substr($andConstraints[1], 0, 2)) {
                    $andConstraints[1] = '^'.substr($andConstraints[1], 2);
                    array_shift($andConstraints);
                    $subConstraint = implode(',', $andConstraints);
                }
            }

            $expectedConstraint = implode(' || ', $orConstraints);

            if ($expectedConstraint !== $constraint) {
                array_push($errors, sprintf(
                    "Requirement format of '%s:%s' is not valid. Should be '%s'.",
                    $name,
                    $constraint,
                    $expectedConstraint
                ));
            }
        }

        return $errors;
    }
}
