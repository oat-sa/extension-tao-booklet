extension-tao-booklet
=====================

An extension for TAO to create test booklets (publishable in MS-Word and PDF along with Answer Sheets)

## Requirements

This extension needs a third-party tool to generate the PDF files.
So to be able to generate the booklet, you should install `wkhtmltopdf` on your server.

If you are using Ubuntu you can use these commands:

```
sudo apt-get update
sudo apt-get install wkhtmltopdf
```

However, depending of the version of your system, the installed version of `wkhtmltopdf` may not fully comply with the requirements, as there is some issues with QT when trying to render header and footers.
If you encounter errors when generating the document, you should install the tool using these commands:

```
sudo apt-get update
sudo apt-get install libxrender1 fontconfig xvfb
wget http://download.gna.org/wkhtmltopdf/0.12/0.12.4/wkhtmltox-0.12.4_linux-generic-amd64.tar.xz -P /tmp/
cd /usr/share/
sudo tar xf /tmp/wkhtmltox-0.12.4_linux-generic-amd64.tar.xz
sudo ln -s /usr/share/wkhtmltox/bin/wkhtmltopdf /usr/bin/wkhtmltopdf
```
