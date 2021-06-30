# composer-lint

composer-lint is a plugin for Composer.

It extends the composer validate command with extra rules.

[![Latest Stable Version](https://poser.pugx.org/sllh/composer-lint/v/stable)](https://packagist.org/packages/sllh/composer-lint)
[![Latest Unstable Version](https://poser.pugx.org/sllh/composer-lint/v/unstable)](https://packagist.org/packages/sllh/composer-lint)
[![License](https://poser.pugx.org/sllh/composer-lint/license)](https://packagist.org/packages/sllh/composer-lint)
[![Dependency Status](https://www.versioneye.com/php/sllh:composer-lint/badge.svg)](https://www.versioneye.com/php/sllh:composer-lint)
[![Reference Status](https://www.versioneye.com/php/sllh:composer-lint/reference_badge.svg)](https://www.versioneye.com/php/sllh:composer-lint/references)

[![Total Downloads](https://poser.pugx.org/sllh/composer-lint/downloads)](https://packagist.org/packages/sllh/composer-lint)
[![Monthly Downloads](https://poser.pugx.org/sllh/composer-lint/d/monthly)](https://packagist.org/packages/sllh/composer-lint)
[![Daily Downloads](https://poser.pugx.org/sllh/composer-lint/d/daily)](https://packagist.org/packages/sllh/composer-lint)

[![Build Status](https://travis-ci.org/Soullivaneuh/composer-lint.svg?branch=master)](https://travis-ci.org/Soullivaneuh/composer-lint)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Soullivaneuh/composer-lint/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Soullivaneuh/composer-lint/?branch=master)
[![Code Climate](https://codeclimate.com/github/Soullivaneuh/composer-lint/badges/gpa.svg)](https://codeclimate.com/github/Soullivaneuh/composer-lint)
[![Coverage Status](https://coveralls.io/repos/Soullivaneuh/composer-lint/badge.svg?branch=master)](https://coveralls.io/r/Soullivaneuh/composer-lint?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/69dec7a4-61a0-4760-bfa2-d3167ae52630/mini.png)](https://insight.sensiolabs.com/projects/69dec7a4-61a0-4760-bfa2-d3167ae52630)

## Installation

You can install it either globally (recommended):

```bash
composer global require sllh/composer-lint
```

or locally:

```bash
composer require sllh/composer-lint
```

## Usage

That's it! Composer will enable automatically the plugin as soon it's installed.

Just run `composer validate` command to see the plugin working.

## Configuration

You can configure the plugin via [`COMPOSER_HOME/config.json`](https://getcomposer.org/doc/03-cli.md#composer-home) or `./composer.json`(for installed locally). Here is the default one:

```json
{
    "config": {
        "sllh-composer-lint": {
            "php": true,
            "type": true,
            "minimum-stability": true,
            "version-constraints": true
        },
        "sort-packages": false
    }
}
```

* `php`: Checks if the PHP requirement is set on the `require` section.
* `type`: Check if package `type` is defined.
* `minimum-stability`: Checks if `minimum-stability` is set. It raises an error if it is, except for `project` packages.
* `version-constraints`: Checks if version constraint formats are valid (e.g. `~2.0` should be `^2.0`).
* `sort-packages`: Checks if packages are sorted on each section. This option is outside `sllh-composer-lint` because it's a composer native one.
