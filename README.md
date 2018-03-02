# Membership by Relationship

## Problem Description

## Usage

### Bulk Actions

# Development

## Design Principles

## Unit Tests
This extension's tests are based on CiviCRM's [Testapalooza PHPUnit
Template](https://github.com/civicrm/org.civicrm.testapalooza/tree/phpunit).
They run "headlessly" (possibly an abuse of the term), which is to say that they
run against a test database spun up expressly for the purpose of testing.

To run the unit tests, you must have [cv](https://github.com/civicrm/cv) and
phpunit4 installed. (If you are using
[civicrm-buildkit](https://github.com/civicrm/civicrm-buildkit), both will have
been installed for you already.)

To run all tests, execute the following:

```bash
$ cd /path/to/extension
$ export CIVICRM_SETTINGS=/path/to/civicrm.settings.php
$ phpunit4 --group headless
```