# ACF PRO Installer

[![Packagist](https://img.shields.io/packagist/v/pivvenit/acf-pro-installer.svg?maxAge=3600)](https://packagist.org/packages/pivvenit/acf-pro-installer)[![Packagist](https://img.shields.io/packagist/l/pivvenit/acf-pro-installer.svg?maxAge=2592000)](https://github.com/pivvenit/acf-pro-installer/blob/master/LICENSE)[![Build Status](https://travis-ci.org/pivvenit/acf-pro-installer.svg?branch=master)](https://travis-ci.org/pivvenit/acf-pro-installer)

A composer plugin that makes installing [ACF PRO] with [composer] easier. 

It reads your :key: ACF PRO key from the **environment** or a **.env file**.

[ACF PRO]: https://www.advancedcustomfields.com/pro/
[composer]: https://github.com/composer/composer

## Usage

**1. Add our [Advanced Custom Fields Composer Bridge](https://github.com/pivvenit/acf-composer-bridge) repository to the [`repositories`][composer-repositories] field in `composer.json`**
> This repository simply provides a periodically updated [packages.json](https://pivvenit.github.io/acf-composer-bridge/composer/v1/packages.json), that redirects composer to the ACF provided downloads. 
Note that this repository **does not** provide any Advanced Custom Fields Pro packages itself, it only tells Composer where it can find ACF Pro packages.
Secondly it is important to note that **your license key is not submitted to the repository**, since the installer downloads the Advanced Custom Fields Pro zip files directly from ACF's servers.

**Why this repository?**

Since it enables you to use `advanced-custom-fields/advanced-custom-fields-pro` package with version constraints like any normal Packagist package.
You no longer have to update the version manually as you had to with `philippbaschke/acf-pro-installer` (and tools like dependabot will also work for ACF).

```json
{
  "type": "composer",
  "url": "https://pivvenit.github.io/acf-composer-bridge/composer/v1/"
}
```

**2. Make your ACF PRO key available**

Set the environment variable **`ACF_PRO_KEY`** to your [ACF PRO key][acf-account].

Alternatively you can add an entry to your **`.env`** file:

```ini
# .env (same directory as composer.json)
ACF_PRO_KEY=Your-Key-Here
```

**3. Require ACF PRO**

```sh
composer require advanced-custom-fields/advanced-custom-fields-pro
```

[composer-repositories]: https://getcomposer.org/doc/04-schema.md#repositories
[package-gist]: https://gist.github.com/fThues/705da4c6574a4441b488
[acf-account]: https://www.advancedcustomfields.com/my-account/
