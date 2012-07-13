================================================================================
                                VoodooPHP 
version: 2.0.a

Author: Mardix
Copyright: Mardix and other contributors
License: MIT
Source: https://github.com/VoodooPHP/Voodoo
Methodology: Modular MVC
PSR-0 & PSR-1 Compatible
================================================================================

1. About Voodoo
2. Download
3. Install 
4. File Structure
5. Using Chef to create your modules, models, controllers, view automatically
    a) via Web
    b) via command line
6. Manual creation: 
    a) Models
    b) Modules
    c) Controllers & Views

Advanced 
- Most used methods in your controller
- DRY : Use methods from other modules/controllers

================================================================================
1 - ABOUT VOODOO
================================================================================

Voodoo is a micro Modular-MVC PHP 5.3 framework, that helps
developers rapidly and efficiently create and deploy ready to work application.

[

Voodoo is not your typical framework. It is built to ease development for both developers 
and designers, 1) by using Separation of Concerns, where each elements are placed into their 
respective place; 2) b
Therefore to achieve this, Voodoo, by default requires 

]
Voodoo is fully namespaced.

Voodoo is a micro Modular-MVC, it highly follows the Convention over Configuration pattern, 
and the Separation of Concerns process. Voodoo comes with the following features:
DataMapper for database layer, Mustache template engine, Bootstrap template,
allow multiple modules.

* Voodoo is micro
Voodoo doesn't feature everything out of the box, but only offers stuff 
that is essential to quickly develop and deploy your application. And by the way, 
most people don't use all the stuff that come in the full fledge framework anyway...
... just saying! And if you want to add your own library, just add it and voila!

* Voodoo is Modular MVC
Voodoo uses the Modular MVC pattern. IT creates and isolates
 each modules to work on their own, while at the same time can share some code.

* Voodoo is Convention over Configuration

To keep everything consistent in Voodoo, files are placed within their concerns.
Config directories will have .INI files, Controller directories will contain
only Controllers, Model directories will contain only Models. _assets will contains
your application assets. Views will contain only .HTML files, etc...

* Voodoo is Separation of Concerns:
To ease the development for PHP developers and Designers, Voodoo, right out of the box
uses Mustache, a logic less template system, which separates the logic from the presentation.

* Ready to use templates
Instantly Voodoo comes with many templates that will help you ahead and start developing
your application.

* What Voodoo does 
- Front Controller
- Database access
- Mustache Template
- HTTP Request
- Modular-MVC
- Routes
- Bootstrap CSS/JS framework
- Templates
- Friendly URL
- AddOns
- No wrappers for functions that are already available in PHP 5+

================================================================================
2. DOWNLOAD
================================================================================
Voodoo is hosted on Github. You can clone the repo or download as zip or tar.gz

Git Clone (notice the dot at the end)
    git clone git://github.com/VoodooPHP/Voodoo.git .


Download zip
    https://github.com/VoodooPHP/Voodoo/zipball/master


Download
    https://github.com/VoodooPHP/Voodoo/tarball/master



================================================================================
3. INSTALLATION
================================================================================
Run this file:

-> Command line
     cd Voodoo/Magician
     php cli.php

-> Browser
     http://YOUR-SITE.com/Voodoo/Magician/setup.php


================================================================================
4. FILE SYSTEM
================================================================================

    /Voodoo                       : The Voodoo's directory contains all the core files of the framework
        |
        |_ init.php             : Initialize the Voodoo
        |
        |_ Config               : Holds the Voodoo Config files
                |
                |_ Config.ini   : The default config of Voodoo
                |
                |_ define.php   : Contains the constants for path and settings of Voodoo
        |
        |_ Core                 : Contains the core libraries under the Voodoo\Core namespace
                |
                |_ HTTP         : Libraries for Curl, URI, etc
        |
        |_ DB                   : Contains 3rd party DB layer
                |
                |_ NotORM/
                |
                |_ Redisent/
                |
                | Table.php
                |
                | Redis.php
                |
                | MongoDB.php
                |
                | Mapper.php    : An abstract class to map database to object (It's not an ORM)

        |
        |_ Magician                : Contains applications to help you build your models, modules, controllers, routes etc..
        
    |
    |
    |
    |
    /Application                : Contains files created for your application, including modules, controllers, models, views etc...
        |
        |_ init.php             : Initialize the application   
        |
        |_ Config               : Application's config files
            |
            |_ Config.ini       : The global config file
            |
            |_ DB.ini           : Contains DB configurations
            |
            |_ Routes.ini       : Constains routes configuration
        |
        |_ Lib                  : Holds some application's class
        |
        |_ Model                : Contains your application's models. Usually created by Magician 
            |
            |_ $DBAlias
                |
                |_ $Model1.php
                |
                |_ $Modeln.php
        |
        |_ Module               : Contains the modules
            |
            |_ $ModuleName
                    |
                    |_ Controller               : Controllers for this module
                        |
                        |_ $Controller1.php
                        |
                        |_ $Controller-n.php
                    |
                    |_ Model                    : Models for this module
                    |
                    |_ Views                    : Views for this module
                        |
                        |_ $Controller1
                            |
                            |_ index-action.html
                            |
                            |_ another-action.html
                        |
                        |_ $Controller-n
                            |
                            |_ index-action-n.html
                            |
                            |_ another-action-n.html
                                            |
                                            |_ _assets : contains the assests for this module
                                                    |
                                                    |_ js
                                                    |
                                                    |_ css
                                                    |
                                                    |_ images
                                            |
                                            |_ _includes : files to includes 
                                                    |
                                                    |_ container.html : The container where view files will be placed and rendered
        |
        |_ Var           : Holds variable files, such as TMP, CACHE etc...
            |
            |_ cache
            |
            |_ db
            |
            |_ tmp
    |
    |
    |
    |
    |
    /SharedAssets : Holds the base JS, css, or other front end libraries.
        |
        |_ js
        |
        |_ css
        |
        |_ images
        |
        |_ bootstrap    : Twitter framework
    |
    |
    |
    |
    |
    /AddOn          : Contains components or third party application to extend Voodoo



================================================================================
5. USING MAGICIAN
================================================================================
Magician is the web or command line application that allows you to create programmatically
Models, Modules, Controllers, Views, Routes etc.

[More Doc to be added]


================================================================================
4. TEMPLATE PRE-MADE VARIABLES
================================================================================
Voodoo is built for rapid development therefor, to facilitate your task, we already 
assigned some variables to be used in your template files.

- {{App.Page.Title}} = Page Title

    PHP: 
        $this->view()->setPageTitle("My Awesome Page");

    Template: 
        {{App.Page.Title}}

- {{App.Page.Description}} Page Description

    PHP: 
        $this->view()->setPageDescription("My Coolest Page Description");

    Template: 
        {{App.Page.Description}}


- {{App.Page.MetaTags}} Page Meta Tags

    PHP: 
        $this->view()->setMetaTag("keywords","my,page,redbull,drink");

    Template: 
        {{#App.Page.OpenGraphaTags}}
            {{{.}}}
        {{/App.Page.OpenGraphaTags}}



- {{App.Page.OpenGraphaTags}} Page Open Graph

    PHP: 
        $this->view()->setOpenGraphTag("My Coolest Page Description");

    Template: 
        {{#App.Page.OpenGraphaTags}}
            {{{.}}}
        {{/App.Page.OpenGraphaTags}}


- {{App.Pagination}} - Pagination

    {{#App.Pagination}}

    {{/App.Pagination}}


- {{App.Errors}} 
      {{#System.Errors}}
          {{#Messages}}
               {{.}}
          {{/Messages}}
      {{/App.Errors}}

Success
      {{#App.Success}}
          {{#Messages}}
               {{.}}
          {{/Messages}}
      {{/App.Success}}

Url
- {{App.Url.Root}}
- {{App.Url.Module}}
- {{App.Url.Site}}

Path
- {{App.Path.SharedAssets}}
- {{App.Path.Assets}}


- {{App.Year}}


-------
Error 404
