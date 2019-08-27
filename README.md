## sysPass Decrypter

**Current status: BETA**

This is an standalone tool for decrypting sysPass exported XML files. It supports either encrypted or non encrypted files.

The purpose of this tool is for using in DR (Disaster Recovery) scenarios where you cannot access to your sysPass instance. It would be useful to store the exported XML file and a copy of this tool within a secure location, though all the data is decrypted on-the-fly, nevertheless a secure location adds an additional layer of security, avoiding fire risks, thefts, etc.

This tool requires PHP 7.2 or higher

![](https://raw.githubusercontent.com/sysPass/syspass-decrypter/assets/demo-search.gif)

### Install

#### Using PHAR (recommended)

You can simply download a pre-compiled and ready-to-use version as a Phar to any directory. Simply download the latest `syspass-decrypter.phar` file from our releases page:

[Latest release](https://github.com/syspass/syspass-decrypter/releases/latest)

You can now verify everything works by running this:

```bash
$ php syspass-decrypter.phar --version
```

#### Using Composer

* Download
* Check signature
* Install dependencies

### Usage

`php cli.php spd:search-account "account_name"`   