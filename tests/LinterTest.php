<?php

namespace SLLH\ComposerLint\Tests;

use Composer\Json\JsonFile;
use PHPUnit\Framework\TestCase;
use SLLH\ComposerLint\Linter;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class LinterTest extends TestCase
{
    /**
     * @dataProvider getLintData
     *
     * @param string $file
     * @param int    $expectedErrorsCount
     */
    public function testLint($file, $expectedErrorsCount = 0)
    {
        $json = new JsonFile($file);
        $manifest = $json->read();
        $linter = new Linter(
            isset($manifest['config']['sllh-composer-lint']) ? $manifest['config']['sllh-composer-lint'] : array()
        );

        $errors = $linter->validate($manifest);
        $this->assertCount($expectedErrorsCount, $errors);
    }

    /**
     * @return array[]
     */
    public function getLintData()
    {
        return array(
            array(__DIR__.'/fixtures/sort-ok.json'),
            array(__DIR__.'/fixtures/sort-ok-minimal.json'),
            array(__DIR__.'/fixtures/sort-ko.json', 6),
            array(__DIR__.'/fixtures/sort-ko-disabled.json'),
            array(__DIR__.'/fixtures/sort-ko-no-config.json'),
            array(__DIR__.'/fixtures/php-ok.json'),
            array(__DIR__.'/fixtures/php-ko.json', 1),
            array(__DIR__.'/fixtures/php-on-dev.json', 1),
            array(__DIR__.'/fixtures/php-ko-disabled.json'),
            array(__DIR__.'/fixtures/minimum-stability-ok.json'),
            array(__DIR__.'/fixtures/minimum-stability-ko.json', 1),
            array(__DIR__.'/fixtures/minimum-stability-project.json'),
            array(__DIR__.'/fixtures/minimum-stability-ko-disabled.json'),
            array(__DIR__.'/fixtures/type-ok.json'),
            array(__DIR__.'/fixtures/type-ko.json', 1),
            array(__DIR__.'/fixtures/type-ko-disabled.json'),
            array(__DIR__.'/fixtures/version-constraints-ok.json'),
            array(__DIR__.'/fixtures/version-constraints-ko.json', 5),
            array(__DIR__.'/fixtures/version-constraints-ko-disabled.json'),
        );
    }
}
