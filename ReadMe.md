
# VoodooPHP

---

Name: VoodooPHP (aka Voodoo)

version: 3.1.x.x

License: MIT

Design: Modular MVC

PSR-0 & PSR-1 compliant

Author: [Mardix](http://github.com/mardix)

Copyright: Mardix and other contributors

[VoodooPHP.org](http://voodoophp.org)

[Forums](https://groups.google.com/d/forum/voodoophp/)

---

## Table of Content

1. About Voodoo

2. Versioning

3. Download

4. Setup 

5. Getting started: First Voodoo App


6. Magician: code generation tool to create controllers, views on the fly

    a) via Web

    b) via command line

7. Templates Pre-made variables

8. File System

Go to [VoodooPHP.org](http://voodoophp.org) for the complete documentation

---

## 1 - About Voodoo!


Voodoo is a slim yet powerful Modular MVC PHP 5.3 framework, that helps
developers and designers rapidly and efficiently create and deploy ready to work  robust application. Voodoo can be used to develop full fledge applications, RESTful app and API, or command line application. Voodoo makes you the Magician. You become the Magician. You are the Magician.



##### Voodoo does magic:

Voodoo is really slim (just like its creator), yet powerful to do the following and much more:

- Slim 
- **Modular MVC** 
- Loosely coupled
- Namespaced
- PSR-0 compliant
- Front Controller
- Templates (separation of concerns)
- Mustache Template
- Routing
- DB Mapper: PDO, MongoDB, Redis 
- Bootstrap CSS/JS framework
- AddOns
- HTTP Request
- Magician (generate code on the fly)


##### Modular MVC
One major point of Voodoo is that it is **Modular**. 
Modules are independent, interchangeable MVC applications, isolated from each other.
Modules improve maintainability by enforcing  logical boundaries between components. 
By being Modular, Voodoo allows developers/designers to work on individual module at the same time, thus making development of program faster. 
New features or new sections can be implemented quickly without changing other sections. And when it's no longer needed, the module can be deleted and everything is still gravy. 


##### Separation of Concerns
Unlike other frameworks, Voodoo highly follows the Convention over Configuration pattern, and the Separation of Concerns process. For each controller's action (PHP class' method that contains the logic) that is created, Voodoo will also create an HTML file (containing Mustache template tag) which will contain your presentation. When the action is invoked, Voodoo will parse the data and render the content.


##### Routing
Voodoo cleverly routes to the right module of the application by using the URL schema 

		/Module/Controller/Action/Arguments/?queryName=queryValue

It first checks for the **Module**, if found, it accesses the directory 

Then checks the **Controller**, which is a class, if exists it loads it 

Then checks the **Action**, a method in the Controller. If exists it executed the method

But if the Action doesn't exist, it will fall back to the *Controller::action_index()* method

If Controller doesn't exist, it will fall back to the *Index.php* controller

If Module doesn't exist, it will fall back to *Main/* module

Example:

Let's say you have 3 modules : Account, Admin, Main

	
	Module/
		Account/
			Controller/
				Index.php
					action_index()
					action_friends()
				Profile.php
					action_index()
					action_edit()

		Admin/
			Controller/
				Index.php
					action_index()
				Profile.php
					action_index()
					action_edit()
					
		Main/ (the default module)		
			Controller/
				Index.php
					action_index()
					action_blog()
				Profile.php
					action_index()
					action_friends()


* Accessing: 
 *http://Your-Voodoo-Site.com/profile/friends/Mardix/*

Since there is no module called Profile, Voodoo will access the **Main/** module, then **Profile.php** controller, then execute the method **action_friends()**. And the name Mardix becomes an argument.

* What about 
*http://Your-Voodoo-Site.com/admin/profile/edit/Mardix*

Same mechanism, this time Admin module exists, so Voodoo will access the **Admin/** module, then **Profile.php** controller, then execute the method **action_edit()**. And the name Mardix becomes an argument to be edited.

* And this 
*http://Your-Voodoo-Site.com/account/friends/*

Same mechanism, Account module exists, so Voodoo will access the **Account/** module, but Friends.php is not a controller, so it falls back to the **Index.php** controller. Looking for **action_friends()**, it's found and loads it.

* And this 
*http://Your-Voodoo-Site.com/account/friends/logged-in/*

Same as above, but **logged-in** becomes an argument, and the method that is executed in **action_index()**

Oh, two more things, 

1. Voodoo routes are case incensitive. So accessing /account/friends and /AcCount/FrieNds will result to the same place

2. Only alphanumeric [AZ-09] characters are accepted. All the other one will be ignored when routing. So /account/fri-ends/ will still result to /account/friends. Same as /about-us/ which result to /aboutus/. So you can cleverly write your SEO friendly url

It's really magic.

---

## 2. Versioning

Voodoo will be maintained under the [PHP-Semantic Versioning](https://github.com/mardix/php-semver) specification as much as possible. (The [Semantic Versioning](https://github.com/mojombo/semver) specifications was authored by Tom Preston-Werner. the PHP-Semantic Versioning is a subset of the SemVer)

Releases will be numbered with the following format:

	<PHPMajorVersion>.<PHPMinorVersion>/<VoodooMajor>.<VoodooMinor>.<VodooPatch>


 ie: Voodoo 5.3/1.2.7 

	PHP Major.Minor Version = 5.3 ( 5.3 from PHP 5.3.x)

	Voodoo Major = 1

	Voodoo Minor = 2

	Voodoo Patch = 4

 And constructed withe the following guidelines:

* If new PHP 5.x version and breaking backward compatibility with previous PHP version bumps to the PHP Major.Minor version and reset VoodooMajor to 1, VoodooMinor to  0, VoodooPatch to 0 (ie, from PHP 5.3 to 5.4 => 5.4/1.0.0)

* If breaking backward compatibility bumps Voodoo major and reset voodoo minor and patch (ie: 5.3/1.0.7 => 5.3/2.0.0)

* If new addition without backwards compatibilty bumps Voodoo minor and reset the patch (5.3/2.2.7 => 5.3/2.3.0)

* If bug fixes and misc changes bump the Voodoo patch version (5.3/2.3.5 => 5.3/2.3.6)



---

## 3. Download

Voodoo is hosted on Github. You can clone the repo or download as zip or tar.gz

Git Clone (notice the dot at the end)

	git clone git://github.com/VoodooPHP/Voodoo.git .


[Download Zip](https://github.com/VoodooPHP/Voodoo/zipball/master)

[Download Tar](https://github.com/VoodooPHP/Voodoo/tarball/master)
    

---

## 4. Setup

* Command line setup

		cd Voodoo/Magician
 		php cli.php

* Browser

    	 http://Your-Voodoo-Site.com/Voodoo/Magician/

---


## 5. Getting Started: First Voodoo App

Vodoo is a modular framework. Your application will be placed at 

		/Application/Module/Main

Where Main is the default module and that's where we'll create our first app.

Let's create the controller, which is Index at 

/Application/Module/Main/Controller/Index.php

		<?php

			namespace Application\Module\Main\Controller;

			use Voodoo\Core;

			class Index extends Core\Controller{

				/**
				 * The default action.
				 **/
				public function action_index(){

					$this->view()->setPageTitle("Hello, this is magic!");

					$this->view()->assign("Name","Mardix");
				}


				/**
				 * The aboutus action.
				 **/
				public function action_aboutus(){

					$this->view()->setPageTitle("About Us");


				}

			}

Now let's work on the View file. Each controller's action has an html view file and the views will be placed at

/Application/Module/Main/Views/Index/index.html


		<div>
			<h2>Hello {{Name}} and welcome to Voodoo</h2>	
		</div>



/Application/Module/Main/Views/Index/aboutus.html

		<div>
			<h3>About Us</h3>
		</div>


There is one more file we need to create. Voodoo highly follows Separation of Concerns and Don't Repeat Yourself methodology.

By default, Voodoo requires a container template file. The container is a place holder for the action's view. The container may contain  header, footer, sidebar etc.. but must include the tag below to include the action's view page
  
		{{%include @PageBody}}  

Technically, the container is the layout of your application, and your action files are files to be included in the layout. 

So let's create the container. It is placed at:

/Application/Module/Main/Views/_includes/container.html


		<HTML>

			<head>
				<title>{{App.Page.Title}}</title>
			</head>

			<body>

				{{%include @PageBody}}

			</body>

		</HTML>




Accessing *http://Your-Voodoo-Site.com/* will display the following:

		<HTML>

			<head>
				<title>Hello, this is magic!</title>
			</head>

			<body>

				<div>
					<h2>Hello Mardix and welcome to Voodoo</h2>	
				</div>				

			</body>

		</HTML>


Oh yeah, The Magician will setup all of these files for you, so you don't have to that. The Magician will create the controller, the actions in the controller, the container and the actions html file. The Magician is a real magician. Trust him :) 

---

##6. Using Magician


Magician is a tool designed to help you speed up your development by generating MVC code for your application. It will create your Module, Models, Controllers, Views, Routes etc. Properly NAMESPACE your controller classes and put them in the right directory. The Magician can be accessed via command line and the web interface.

* Command line interface

		cd Voodoo/Magician
 		php cli.php


* Web interface

     	http://Your-Voodoo-Site.com/Voodoo/Magician

---

##7. Template Pre-Made Variables

Voodoo is built for rapid development therefor, to facilitate your task, we already 
assigned some variables to be used in your template files.

* **Page Title**

	PHP: 

        $this->view()->setPageTitle("My Awesome Page");

	Template: 

        {{App.Page.Title}}


* **Page Description**

	PHP: 

        $this->view()->setPageDescription("My Coolest Page Description");

	Template: 

        {{App.Page.Description}}


* **Page Meta Tags**

	PHP: 

        $this->view()->setMetaTag("keywords","my,page,redbull,drink");


	Template: 

        {{#App.Page.MetaTags}}

            {{{.}}}

        {{/App.Page.MetaTags}}


* **Page Open Graph**

	PHP: 

        $this->view()->setOpenGraphTag("My Coolest Page Description");


	Template: 

        {{#App.Page.OpenGraphaTags}}

            {{{.}}}

        {{/App.Page.OpenGraphaTags}}


* **Pagination**

    	{{#App.Pagination}}

			<a href="{{Url}}">{{Label}}</a> 

    	{{/App.Pagination}}



* **Error**, to display error message, assign with PHP
	
		$this->view()->setError("Oh nooooo!");


	Rendered in template with

		{{App.Errors}} 

          	{{#Messages}}

               {{.}}

          	{{/Messages}}

      	{{/App.Errors}}


* **Success**, to display success message, assign with PHP
	
		$this->view()->setSuccess("Good");


	Rendered in template with

      	{{#App.Success}}

          	{{#Messages}}

               {{.}}

          	{{/Messages}}

      	{{/App.Success}}


* **Root url**

		{{App.Url.Root}}

* **Module url**

		{{App.Url.Module}}

* **Site's URL**

		{{App.Url.Site}}

* **Shared Assets path**

		{{App.Path.SharedAssets}}

* **Module's Assets path**

		{{App.Path.Assets}}


* **Current Year**
	
		{{App.CurrentYear}}


--- 


## 8. Filesystem



    /Voodoo										: The Voodoo's directory contains all the core files of the framework
        |
        | init.php             					: Initialize the Voodoo
        |
        | Config/               				: Holds the Voodoo Config files
                |
                | Config.ini   					: The default config of Voodoo
                |
                | define.php   					: Contains the constants for path and settings of Voodoo
        |
        | Core/                 				: Contains the core libraries under the Voodoo\Core namespace
                |
                |_ HTTP/         				: Libraries for Curl, URI, etc
				|
				| Interface/					: Voodoo's interfaces
        |
        | DB/                   				: Contains Database mapper
                |
                | NotORM/						: NotOrm library for PDO
                |
                | Redisent/						: Redisent library for Redis
                |
                | Table.php						: PDO Mapper			
                |
                | Redis.php						: Redis Mapper
                |
                | MongoDB.php					: MongoDB Mapper
                |
                | Mapper.php    				: An abstract class to map database to object (It's not an ORM)

        |
        | Magician/                				: Contains applications to help you build your models, modules, controllers, routes etc..
        
    |
    |
    |
    |
    /Application                				: Your application's directory. Contains Modules, Models, Controllers, Views etc..
        |
        | Config/               				: Application's config files
            |
            | Config.ini       					: The global config file
            |
            | DB.ini           					: Contains DB configurations
            |
            | Routes.ini       					: Contains routes configuration
        |
        | Lib/                  				: Holds some application's class
        |
        | Model/                				: Contains your application's models. Usually created by Magician 
            |
            | MyDBAlias/
                |
                | Model.php
                |
                | AnotherModel.php
        |
        | Module/               				: Contains the modules
            |
            | MyModuleName/						: A module
                    |
                    | Controller/               : Controllers for this module
                        |
                        | Index.php				: A controller
                        |
                        | AnotherController.php
                    |
                    | Model/                    : Models for this module
                    |
                    | Views/                    : Views for this module
                        |
                        | Index/ 				: Views map to the Controller
                            |
                            | index.html		: action html in the controller Index
							|
							| anotheraction.html
                        |
                        | AnotherController/
                            |
                            |_ index.html
                        |
                        | _assets/ 				: contains the assests for this module
                                |
                                | js/
                                |
                                | css/
                                |
                                | images/
                        |
                        | _includes/ 			 : files to includes: container.html, header.html, footer.html etc... 
                                |
                                | container.html : The container where view files will be placed and rendered
	|
    |
    | Var/										 : Holds variable files, such as TMP, CACHE etc...
            |
            | cache/
            |
            | db/
            |
            | tmp/
    |
    |
    |
    |
    |
    /SharedAssets 								: Holds the base JS, css, or other front end libraries.
        |
        | js/
        |
        | css/
        |
        | images/
        |
        | bootstrap/    						: Twitter framework
    |
    |
    |
    |
    |
    /AddOn          							: Contains components or third party application to extend Voodoo




---

Go to [VoodooPHP.org](http://voodoophp.org) for the complete documentation

---

VoodooPHP is created by Mardix and released under the MIT License. 

Enjoy!
