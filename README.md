# ACF PRO Installer

[![Packagist](https://img.shields.io/packagist/v/pivvenit/acf-pro-installer.svg?maxAge=3600)](https://packagist.org/packages/pivvenit/acf-pro-installer)[![Packagist](https://img.shields.io/packagist/l/pivvenit/acf-pro-installer.svg?maxAge=2592000)](https://github.com/pivvenit/acf-pro-installer/blob/master/LICENSE)![](https://github.com/pivvenit/acf-pro-installer/workflows/Master%20Build/badge.svg)
[![Dependabot](https://badgen.net/badge/Dependabot/enabled/green?icon=dependabot)](https://dependabot.com/)
[![Coverage Status](https://coveralls.io/repos/github/pivvenit/acf-pro-installer/badge.svg?branch=master)](https://coveralls.io/github/pivvenit/acf-pro-installer?branch=master)

A composer plugin that makes installing [ACF PRO] with [composer] easier. 

It reads your :key: ACF PRO key from the **environment** or a **.env file**.

[ACF PRO]: https://www.advancedcustomfields.com/pro/
[composer]: https://github.com/composer/composer

## Usage

> This plugin is compatible with Both Composer 2.x (latest) and 1.x

**1. Add our [Advanced Custom Fields Composer Bridge](https://github.com/pivvenit/acf-composer-bridge) repository to the [`repositories`][composer-repositories] field in `composer.json`**
> This repository simply provides a periodically updated [packages.json](https://pivvenit.github.io/acf-composer-bridge/composer/v2/packages.json), that redirects composer to the ACF provided downloads. 
Note that this repository **does not** provide any Advanced Custom Fields Pro packages itself, it only tells Composer where it can find ACF Pro packages.
Secondly it is important to note that **your license key is not submitted to the repository**, since the installer downloads the Advanced Custom Fields Pro zip files directly from ACF's servers.

**Why this repository?**

Since it enables you to use `advanced-custom-fields/advanced-custom-fields-pro` package with version constraints like any normal Packagist package.
You no longer have to update the version manually as you had to with `philippbaschke/acf-pro-installer` (and tools like dependabot will also work for ACF).

```json
{
  "type": "composer",
  "url": "https://pivvenit.github.io/acf-composer-bridge/composer/v3/wordpress-plugin/"
}
```

This installs the package as `wordpress-plugin` type, in case you want a different type, use the following URL:

wordpress-muplugin:
> `https://pivvenit.github.io/acf-composer-bridge/composer/v3/wordpress-muplugin/`

wpackagist-plugin:
> `https://pivvenit.github.io/acf-composer-bridge/composer/v3/wpackagist-plugin/`

library:
> `https://pivvenit.github.io/acf-composer-bridge/composer/v3/library/`


**2. Make your ACF PRO key available**

There are 3 ways to make the ACF_PRO_KEY available:
- Using the ACF_PRO_KEY environment variable
- `.env` file
- Setting `acf-pro-key` in `$COMPOSER_HOME/config.json`

Select the one that best matches your setup:

***2.a Using the ACF_PRO_KEY Environment variable***

Set the environment variable **`ACF_PRO_KEY`** to your [ACF PRO key][acf-account].

***2.b Use a .env file***

Alternatively you can add an entry to your **`.env`** file:

```ini
# .env (same directory as composer.json)
ACF_PRO_KEY=Your-Key-Here
```

***2.c. Setting the key in `$COMPOSER_HOME/config.json`***

You specify the `acf-pro-key` in the `config` section of your `$COMPOSER_HOME/config.json`
```json
{
  "config": {
    "acf-pro-key": "Your-Key-Here"
  }
}
```
> `$COMPOSER_HOME` is a hidden, global (per-user on the machine) directory that is shared between all projects.
> By default it points to `C:\Users\<user>\AppData\Roaming\Composer` on Windows and `/Users/\<user\>/.composer` on macOS. 
> On *nix systems that follow the XDG Base Directory Specifications, it points to `$XDG_CONFIG_HOME/composer`. 
> On other *nix systems, it points to `/home/\<user\>/.composer`.

**3. Require ACF PRO**

```sh
composer require advanced-custom-fields/advanced-custom-fields-pro
```

[composer-repositories]: https://getcomposer.org/doc/04-schema.md#repositories
[package-gist]: https://gist.github.com/fThues/705da4c6574a4441b488
[acf-account]: https://www.advancedcustomfields.com/my-account/
