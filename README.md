# Membership by Relationship

## Problem Description

When Chorus America's membership configuration was set up long ago, the chorus
membership types were configured to confer membership to individuals based on
the existence of a "Primary Contact" relationship between them.

The need to extend membership benefits to individuals with other relationships
(e.g., board members, artistic directors, etc.) has resulted in several
workarounds and hacks which shall finally be laid to rest by reconfiguration of
the membership types.

## Usage

On installation, this extension modifies membership types:

- Chorus Member (budgets up to $87,499)
- Chorus Member (budget of $1 million+)
- Chorus Member (budget of $87,500 - $999,999)

setting their conferment relationship types to the following selection:

- Employee Of
- Administrative Staff Member of
- Board Member of
- Chief Administrative Director of
- Chief Artistic Leader of
- Board President/Chairman of
- Artistic Staff Member of
- Primary Contact

### Bulk Actions

Updating the membership types is not a sufficient trigger for creating the
membership records that would exist if the memberships types had always been
configured per above. This extension supplies two APIs (available as scheduled
jobs) to assist with the creation of membership records.

#### api.MemRelSync.createqueue

Prepares a queue of relationships to be evaluated later for membership
conferment. Because it is expected to be a memory-intensive process, it is
recommended for use on the command line interface only. This API should not need
to be used except when membership type configuration is changed in a way not
currently supported by CiviCRM (e.g., after installation of this extension).

See the API explorer for usage details.

#### api.MemRelSync.processqueue

Triggers the creation of indirect memberships where conferment relationships
exist. This API takes a parameter `max_run_time` which limits the amount of time
(default: 30 sec) it will spend processing queued items; a system administrator
can set this below the timeout threshold, making it safe for use as a scheduled
job.

See the API explorer for usage details.

### Uninstallation

This extension may be safely disabled or uninstalled once:

- the on-installation changes to the membership types are completed
- api.MemRelSync.createqueue has been used to enqueue relationships of the types
  affected by the new membership type configuration
- api.MemRelSync.processqueue has processed the queue to completion

# Development

## Architecture

### Creating Memberships

Updating the membership types is not a sufficient trigger for creating the
membership records that would exist if the memberships types had always been
configured per above. In order to do so, method
`CRM_Contact_BAO_Relationship::relatedMemberships()` needs to be triggered
for each affected relationship.

This extension does so by loading and saving a given relationship (without
changes). This indirect approach is preferable to invoking the static method
directly because:

1. `CRM_Contact_BAO_Relationship::relatedMemberships()` is not a supported
   extension point, and we have no "contract" with CiviCRM that it will not
   change or even be removed.
2. The parameters for `CRM_Contact_BAO_Relationship::relatedMemberships()` are
   not well documented. "Kicking" the relationship with an otherwise
   pointless update is easier.

### Queue Processing

As it expects to deal with a large number of records that need not be processed
all at once, and which the system may not be able to process all at once without
running out of memory (or timing out), this extension makes use of CiviCRM's
[queue processing system](https://wiki.civicrm.org/confluence/display/CRMDOC/Howto+use+the+Queue+mechanism+in+your+extension).

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