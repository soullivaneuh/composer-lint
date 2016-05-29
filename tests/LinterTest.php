<?php

namespace SLLH\ComposerLint\Tests;

use Composer\Json\JsonFile;
use SLLH\ComposerLint\Linter;

/**
 * @author Sullivan Senechal <soullivaneuh@gmail.com>
 */
final class LinterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Linter
     */
    private $linter;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->linter = new Linter();
    }

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

        $errors = $this->linter->validate($manifest);
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
            array(__DIR__.'/fixtures/sort-ko-deactivated.json'),
            array(__DIR__.'/fixtures/sort-ko-no-config.json'),
        );
    }
}
