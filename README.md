extension-tao-booklet
=====================

An extension for TAO to create test booklets (publishable in MS-Word and PDF along with Answer Sheets)

## Warning
Due to the move to `ES2015`, some code might not work on legacy browsers. 
Especially for code that use to rely on polyfills, like for the `Promise`.
The polyfills are now linked only when the code is bundled, and are not reachable anymore in development mode.
For that reason, and because `wkhtmltopdf` is not supporting ES2015 and requires polyfills,
the generation of PDF only works with bundled version (aka production mode).

From version `4.2.0` of `taoBooklet`, the page rendered to get the PDF will always use bundles transpiled to ES5. This applies no matter if the mode is set to development or production.
This implies that any change made to the source code will need to pass trough a re-bundling:
```sh
cd tao/views/build
npx grunt taobookletbundle
```

For version `4.1.1` and older, you still need to activate the production mode as follows: 
- open the config file `config/generis.conf.php`, and set the constant `DEBUG_MODE` to `false` (around line 50):
```php
#mode
define('DEBUG_MODE', false);
```

## Requirements

This extension needs a third-party tool to generate the PDF files.
So to be able to generate the booklet, you should install `wkhtmltopdf` on your server.

If you are using Ubuntu you can use these commands:

```
sudo apt-get update
sudo apt-get install wkhtmltopdf
```

However, depending on the version of your system, the installed version of `wkhtmltopdf` may
not fully comply with the requirements, as there is some issues with QT when trying to render
header and footers. If you encounter errors when generating the document, you should install
the tool using these commands:

```
wget https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.5/wkhtmltox_0.12.5-1.jessie_amd64.deb
sudo dpkg -i wkhtmltox_0.12.5-1.jessie_amd64.deb
```
After that you can use `/usr/local/bin/wkhtmltopdf` in your configuration

For Debian-based distributions, you may need to do an additional step to install some
dependencies:

```
sudo apt-get update
sudo apt-get install wkhtmltopdf
sudo apt-get install libxrender1 fontconfig xvfb
sudo apt --fix-broken-install
```

If the previous steps fail, you may try to use a binary, non-packaged distribution instead.

```
wget https://github.com/wkhtmltopdf/wkhtmltopdf/releases/download/0.12.4/wkhtmltox-0.12.4_linux-generic-amd64.tar.xz
tar xf  wkhtmltox-0.12.4_linux-generic-amd64.tar.xz
cd ./wkhtmltox/bin/
sudo cp -R ./* /usr/bin/
sudo cp -R ./* /usr/local/bin/
wkhtmltopdf -V
```

Please refer to https://wkhtmltopdf.org/downloads.html for an updated list of
`wkhtmltopdf` packages for Ubuntu and other distributions. You may find a list of
source, binary and packages for v0.12.5 at [GitHub](https://github.com/wkhtmltopdf/wkhtmltopdf/releases/tag/0.12.5) as well.

Deprecated:
Please note that the version 0.12.4 has a bug which was fixed in the version 0.12.5: sometimes footers and headers not provided in the pdf

```
sudo apt-get update
sudo apt-get install libxrender1 fontconfig xvfb
wget https://downloads.wkhtmltopdf.org/0.12/0.12.4/wkhtmltox-0.12.4_linux-generic-amd64.tar.xz -P /tmp/
cd /usr/share/
sudo tar xf /tmp/wkhtmltox-0.12.4_linux-generic-amd64.tar.xz
sudo rm /usr/bin/wkhtmltopdf
sudo ln -s /usr/share/wkhtmltox/bin/wkhtmltopdf /usr/bin/wkhtmltopdf
```
