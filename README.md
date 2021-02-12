[![Build Status](https://travis-ci.org/sysPass/syspass-decrypter.svg?branch=master)](https://travis-ci.org/sysPass/syspass-decrypter) [![Maintainability](https://api.codeclimate.com/v1/badges/cf5226d1b832e09a1a91/maintainability)](https://codeclimate.com/github/sysPass/syspass-decrypter/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/cf5226d1b832e09a1a91/test_coverage)](https://codeclimate.com/github/sysPass/syspass-decrypter/test_coverage)


## sysPass Decrypter

**Current status: BETA**

This is an standalone tool for decrypting sysPass exported XML files. It supports either encrypted or non encrypted files.

The purpose of this tool is for using in DR (Disaster Recovery) scenarios where you cannot access to your sysPass instance. It would be useful to store the exported XML file and a copy of this tool within a secure location, though all the data is decrypted on-the-fly, nevertheless a secure location adds an additional layer of security, avoiding fire risks, thefts, etc.

This tool requires PHP 7.2 or higher

![](https://raw.githubusercontent.com/sysPass/syspass-decrypter/assets/demo-search.gif)

### Install

#### Using PHAR (recommended)

You can simply download a pre-compiled and ready-to-use version as a Phar to any directory. Download the latest `syspass-decrypter.phar` file from our releases page:

[Latest release](https://github.com/syspass/syspass-decrypter/releases/latest)

#### Using Composer

**TODO**

### Usage

#### Global Options

* `--xmlpath` Set the XML file path
* `--export` export to JSON and CSV to the root path
* `--password` Set the password for the encrypted XML (it will be asked if not set)
* `--masterPassword` Set the master password for decrypting the accounts' password (it will be asked if not set)
* `--wide=[yes|no]` Do not truncate text fields
* `--help` Commands help

#### Search Account

+ `spd:search-account [name]` Search for an account with the given name. If `[name]` is omitted it will list all the accounts
* `--withCategories=[yes|no]` Include the category column for each result
* `--withTags=[yes|no]` Include the tags column for each result

##### Examples

`syspass-decrypter.phar spd:search-account "GitHub" --xmlpath ./syspass.xml --withCategories=yes --wide`

`syspass-decrypter.phar spd:search-account --xmlpath ./syspass.xml`

`syspass-decrypter.phar spd:search-account --xmlpath ./syspass.xml --export`