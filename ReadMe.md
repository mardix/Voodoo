
# Voodoo 1.x.x

#### A Simple, Easy, Intuitive PHP Framework for Those Who Keep It Simple

---

Name: Voodoo

License: MIT

Design: Modular MVC

Requirements: PHP 5.4

Compliance: PSR-2

Author: [Mardix](http://github.com/mardix)

Copyright: This Year - Mardix

[VoodooPHP.org](http://voodoophp.org)

[Forums](https://groups.google.com/d/forum/voodoophp/)

*This is an overview of Voodoo, please go to [VoodooPHP.org](http://voodoophp.org) for a complete documentation of the framework*

---

## About Voodoo!


Voodoo is Modular MVC framework written in PHP. Its main goal is to ensure separation of concerns by keeping your code organized and avoid mixing database calls, HTML tags and business logic in the same script.

Voodoo, first, organize your application into subset of MVC structure which are independent from other one. These subsets are called Modules. Modules can be an admin section, the main site, intranet, or can be used for A/B testing.

Modules improve maintainability by enforcing logical boundaries between components. By being Modular, Voodoo allows developers/designers to work on individual module at the same time, thus making development of program faster. New features or new sections can be implemented quickly without changing other sections. And when it's no longer needed, the module can be deleted and everything is still gravy.

---
### Features Highlight


- Slim
- RESTful
- API ready
- MVC
- Shared Application
- Modular MVC
- Namespaced
- PSR compliant
- Front Controller
- Templates
- Mustache Template
- Smart Routing
- [VoodOrm](https://github.com/mardix/VoodOrm)
- Annotation
- [Paginator](https://github.com/mardix/Paginator)
- Bootstrap CSS/JS framework
- HTTP Request
- Voodooist (generate code on the fly)


---
## Install Voodoo with Composer

To install Voodoo, create or edit the **composer.json** file, and add the code below in the 'require':

	"voodoophp/voodoo": "1.*"

Your composer.json file should look something similar to this:

	{
	    "name": "voodoophp/myapp",
	    "description": "My awesome Voodoo App",
	    "require": {
	        "voodoophp/voodoo": "1.*"
	    }
	}

then run the command below to download Voodoo and its dependencies

	composer install

Assuming that your composer installed the packages in your `/vendor` directory, you should see at least the following when you go inside of /vendor

		|
		+-- composer.json
		|
		+-- vendor/
			|
			+-- composer/
			|
			+-- voodoophp/

---

## Setup & Filesystem

Now the Voodoo is installed, it's time to set it up so it creates the directories for your applications. 

Assuming that composer installed packages in your `/vendor` directory, the Voodoo framework will be in: `/vendor/voodoophp/voodoo` 

Enter the command below

	cd vendor/voodoophp/voodoo/src/Voodoo/Voodooist
	php ./setup.php

Once the setup is done, you should have a filesystem similar to this:

	|
	+-- App/
		|
		+-- .htaccess
		|
		+-- Conf/			
		|
		+-- Www/
			|
			+-- Config.ini
			|
			+-- Main/
				|
				+-- Controller/
				|
				+-- Model/
				|
				+-- Views/
	|
	+-- assets/
		|
		+-- bootstrap/
		|
		+-- css/
		|
		+-- img/
		|
		+-- js/
		|
		+-- jquery/
	|
	+-- vendor
			|
			+-- composer/
			|
			+-- voodoophp/
	|
	+-- composer.json
	|
	+-- .htaccess
	|
	+-- index.php
	|
	+-- robots.txt

**/App** : Contains your application's Modular MVC

**/App/Conf** : Contains the configurations, ie: DB.ini for database settings, System.ini for system wide settings, app.json your application schema to create your MVC files on the fly.

**/App/Www**: That's your default application which contains your MVC files. When someone accesses your application that's where he/she will land, unless you change it to another application. You can also have */App/Site1*, */App/Site2*, */App/Api* and so on to create multi applications which share the same Voodoo code base.

**/assets/**: That's your application shared assets. For your convenience, we added Bootstrap and JQuery.

**/vendor/**: Where composer installed the packages

**/composer.json**: The composer file

**/index.php**: The front-controller file which redirect everything to Voodoo to do its magic!

---

## The Voodooist 

The Voodooist is our servant, it will help us create directories and files properly namespaced per PSR-0 in our Voodoo application. 

Once our application has been setup, Voodooist requires: /App/Conf/app.json and /App/voodooist.php. These files were created during the initial setup.


#### - App/Conf/app.json

**App/Conf/app.json** is a JSON file that contains the layout of your application, including modules, controllers, controller's action etc. It is ran by Voodooist to setup your application. Below is a  basic **app.json**

	{
	    "createPublicAssets" : true,
	    "applications" : [
	        {
	            "name" : "www",
	            "modules" : [
	                {
	                    "name" : "Main",
	                    "template" : "Default",
	                    "isApi" : false,
	                    "omitViews" : false,

	                    "controllers" : [
	                        {
	                            "name" : "Index",
	                            "actions" : ["index", "login", "logout", "about-us"]
	                        },
	                        {
	                            "name" : "Account",
	                            "actions" : ["index", "info", "preferences"]
	                        }
	                    ],

	                    "models" : [
	                        {
	                            "name" : "User",
	                            "dbAlias" : "MyDB",
	                            "table" : "user",
	                            "primaryKey" : "id",
	                            "foreignKey" : "%s_id"
	                        },
	                        {
	                            "name" : "User/Preference",
	                            "dbAlias" : "MyDB",
	                            "table" : "user_preference",
	                            "primaryKey" : "id",
	                            "foreignKey" : "%s_id"
	                        }
	                    ]
	                }
	            ]
	        }            
	    ]
	} 

#### - /App/Voodoist.php

Once Voodoo is setup, you won't need to go the `/vendor/voodoophp/voodoo/src/Voodoo/Voodooist/setup.php` to create your application's files. Voodoo created `/App/voodooist.php` to serve the same purpose. You will need it to create your application's files on the fly based on your /App/Conf/app.json. All files will be properly namespaced per PSR-0, and placed inside of /App under their respective application.

Running the code below, will execute the `/App/Conf/app.json` set above:

	cd /App
	php ./voodooist.php

Upon execution of /App/voodooist.php you will should see a filesystem similar to this:

	|
	+-- App/
		|
		+-- .htaccess
		|
		+-- Www
            |
            +-- Config.ini
			|
			+-- Main
				|
				+-- Controller/
                    |
                    +-- BaseController.php (extends Voodoo\Core\Controller)
					|
					+-- Index.php (extends BaseController)
							::actionIndex()
							::actionLogin()
							::actionLogout()
							::actionAboutUs()
					|
					+-- Account.php (extends BaseController)
							::actionIndex()
							::actionInfo()
							::actionPreferences()
				|
				+-- Model/
					|
					+-- User.php
                    |
                    +-- User/Preference.php
				|
				+-- Views/
					|
					+-- Index/
						|
						+-- Index.html
						|
						+-- Login.html
						|
						+-- Logout.html
						|
						+-- AboutUs.html
					|
					+-- Account/
						|
						+-- Index.html
						|
						+-- Info.html
						|
						+-- Preferences.html
					|
					+-- _assets/
						|
						+-- js/
						|
						+-- css/
						|
						+-- img/
					|
					+-- _includes/
						|
						+-- flash-message.html
						|
						+-- pagination.html
					|
					+-- _layouts/
						|
						+-- default.html
						|
						+-- head-tag.html
						|
						+-- footer.html
						|
						+-- header.html
	|
	+-- assets/ [+]
	|
	+-- vendor/ [+]
	|
	+-- index.php
	|
	+-- .htaccess


### Application (Multi-Applications)

Application is the upper level of your program. Everything must be in an application. An application can be another site sharing the same Voodoo code base of other application. Hence making Voodoo multi-sites , multi-applications ready.

 You can have *Site1.com*, *Site2.com*, *SiteX.com* all under the same environment sharing the same front controller, same Voodoo code base. And since your application is namespaced PSR-0, it's easy to re-use code from other applications.

Inside of each applications are the applications modules. By default Voodoo creates the **WWW** which is the default entry point of your site. In large, Applications are sets of Modules. A more advance app.json looks like that with three applications: *Www, AnotherApp, Api*

/App/Conf/app.json

	{
	    "createPublicAssets" : true,
	    "applications" : [
	        {
	            "name" : "www",
	            "modules" : [
	                {
	                    "name" : "Main",
	                    "template" : "Default",
	                    "isApi" : false,
	                    "omitViews" : false,

	                    "controllers" : [
	                        {
	                            "name" : "Index",
	                            "actions" : ["index", "login", "logout", "about-us"]
	                        },
	                        {
	                            "name" : "Account",
	                            "actions" : ["index", "info", "preferences"]
	                        }
	                    ],

	                    "models" : [
	                        {
	                            "name" : "User",
	                            "dbAlias" : "MyDB",
	                            "table" : "user",
	                            "primaryKey" : "id",
	                            "foreignKey" : "%s_id"
	                        },
	                        {
	                            "name" : "User/Preference",
	                            "dbAlias" : "MyDB",
	                            "table" : "user_preference",
	                            "primaryKey" : "id",
	                            "foreignKey" : "%s_id"
	                        }
	                    ]
	                }
	            ]
	        },
	        {
	            "name" : "AnotherApp",
	            "modules" : [
	                {
	                    "name" : "Main",
	                    "template" : "Default",
	                    "isApi" : false,
	                    "omitViews" : false,

	                    "controllers" : [
	                        {
	                            "name" : "Index",
	                            "actions" : ["index", "info"]
	                        }
	                    ]
	                },
	                {
	                    "name" : "Admin",
	                    "template" : "Default",
	                    "isApi" : false,
	                    "omitViews" : false,

	                    "controllers" : [
	                        {
	                            "name" : "Index",
	                            "actions" : ["index", "login"]
	                        }
	                    ]
	                }                    
	            ]
	        },
	        {
	            "name" : "Api",
	            "modules" : [
	                {
	                    "name" : "Main",
	                    "template" : "",
	                    "isApi" : true,
	                    "omitViews" : true,

	                    "controllers" : [
	                        {
	                            "name" : "Index",
	                            "actions" : ["index", "status"]
	                        }
	                    ]
	                }
	            ]
	        }             
	    ]
	} 

After running /App/voodooist.php you will get the following structure

	|
	+-- App/
		|
		+-- .htaccess
		|
		+-- Www
            |
            +-- Config.ini
			|
			+-- Main
				|
				+-- Controller/
                    |
                    +-- BaseController.php (extends Voodoo\Core\Controller)
					|
					+-- Index.php (extends BaseController)
							::actionIndex()
							::actionLogin()
							::actionLogout()
							::actionAboutUs()
					|
					+-- Account.php (extends BaseController)
							::actionIndex()
							::actionInfo()
							::actionPreferences()
				|
				+-- Model/
					|
					+-- User.php
                    |
                    +-- User/Preference.php
				|
				+-- Views/
					|
					+-- Index/
						|
						+-- Index.html
						|
						+-- Login.html
						|
						+-- Logout.html
						|
						+-- AboutUs.html
					|
					+-- Account/
						|
						+-- Index.html
						|
						+-- Info.html
						|
						+-- Preferences.html
					|
					+-- _assets/
						|
						+-- js/
						|
						+-- css/
						|
						+-- img/
					|
					+-- _includes/
						|
						+-- flash-message.html
						|
						+-- pagination.html
					|
					+-- _layouts/
						|
						+-- default.html
						|
						+-- head-tag.html
						|
						+-- footer.html
						|
						+-- header.html
		|
		+-- AnotherApp
            |
            +-- Config.ini
			|
			+-- Main
				|
				+-- Controller/
                    |
                    +-- BaseController.php (extends Voodoo\Core\Controller)
					|
					+-- Index.php (extends BaseController)
							::actionIndex()
							::actionInfo()
				|
				+-- Views/
					|
					+-- Index/
						|
						+-- Index.html
						|
						+-- Info.html
					|
					+-- _assets/[+]
					|
					+-- _includes/ [+]
					|
					+-- _layouts/ [+]
			|
			+-- Admin
				|
				+-- Controller/
                    |
                    +-- BaseController.php (extends Voodoo\Core\Controller)
					|
					+-- Index.php (extends BaseController)
							::actionIndex()
							::actionLogin()
				|
				+-- Views/
					|
					+-- Index/
						|
						+-- Index.html
						|
						+-- Login.html
					|
					+-- _assets/[+]
					|
					+-- _includes/ [+]
					|
					+-- _layouts/ [+]
		|
		+-- Api
            |
            +-- Config.ini
			|
			+-- Main
				|
				+-- Controller/
                    |
                    +-- BaseController.php (extends Voodoo\Core\Controller\Api)
					|
					+-- Index.php (extends BaseController)
							::actionIndex()
							::actionStatus()
	|
	+-- assets/ [+]
	|
	+-- vendor/ [+]
	|
	+-- index.php
	|
	+-- .htaccess



### Modules

Modules are the second level of your application. Application are sets of Modules and Modules are set of MVC application.

Each module contains one set of MVC. By default Voodoo will fall back to `Main` module if a module is not specified.

That's how the module **Main** looks like in the **Www** application.

	|
	+-- App/
		|
		+-- .htaccess
		|
		+-- Www
			|
			+-- Config.ini
			|
			+-- Main
				|
				+-- Controller/
					|
					+-- Index.php
							::actionIndex()
							::actionAbout()
				|
				+-- Model/
					|
					+-- MySampleModel.php
				|
				+-- View/
					|
					+-- Index/
						|
						+-- Index.html
						|
						+-- About.html
					|
					+-- _assets/
						|
						+-- js/
						|
						+-- css/
						|
						+-- img/
					|
					+-- _includes/
						|
						+-- flash-message.html
						|
						+-- pagination.html
					|
					+-- _layouts/
						|
						+-- default.html
						|
						+-- head-tag.html
						|
						+-- footer.html
						|
						+-- header.html
	|
	+-- assets/ [+]
	|
	+-- vendor/ [+]
	|
	+-- index.php
	|
	+-- .htaccess

All classes are namespaced per PSR-0. So the main namespace for Www/Main are as follow:

	App\Www\Main
	App\Www\Main\Controller
	App\Www\Main\Controller\Index
	App\Www\Main\Model
	App\Www\Main\Model\MySampleModel

Substitute `Www` for any other application name you would have the same structure.


#### What can modules be used for?

Modules can be other sections of your site separated from the main site, let's say `App\Www\Admin` for an admin section or `App\Www\Api` to serve an API. It can be anything you want it. It can be used for A/B testing... etc...



---

Now you understand how Voodoo is structured let's get to know about how everything works

---

## Front-Controller: /index.php

At the root of your application there is the **index.php**. Its job, as a front-controller, is to encapsulate the typical request/route/dispatch/response for the Voodoo application, by invoking  `Voodoo\Core\Application` 

/index.php

	<?php

	require_once __DIR__."/App/bootstrap.php";

    /**
     * Set the application name to use. By default it's Www
     * @type string
     */
    $appName = "www";

    (new Voodoo\Core\Application(APP_ROOT_DIR, $appName))->doVoodoo();


Everythings comes here first then go to where they need to be


### Front-Controller serving multiple application based on URL

The example below illustrates how you can have multi-sites using the same code base. 

Based on the hostname, each site will use a different application directory. You will have to point all the domains to the same IP address for it to work.  

Let's use the /App/Conf/app.json above for our multi-sites

/index.php

	<?php

	require_once __DIR__."/App/bootstrap.php";

    $hostName = Voodoo\Core\Env::getHostName();
    switch($hostName) {
        case "api.mysite.com" :
            $appName = "Api";
            break;
        case "crazyapps.com" :
            $appName = "AnotherApp";
            break;
        case "site1.com" :
            $appName = "Site1";
            break;
        default: // mydefaultsite.com
            $appName = "Www";
    }
    
    (new Voodoo\Core\Application(APP_ROOT_DIR, $appName))->doVoodoo();

So now you have a multi-sites application. Ayibobo!

---

## Smart Routing & Application Execution

Voodoo cleverly routes to the right module of the application by using the URL schema

		site.com/Module/Controller/Action/Segments/?paramName=paramValue

* Module: The container of the MVC application

* Controller: A class accepting the user request

* Action: A method of the controller's class

* Segments: extra parameters that are passed in the action

* ParamName/ParamValue : Parameters from the query url

### How does Voodoo excute your application?

The very first thing Voodoo requires is an App name. The app name contains all the modules for the application. This is set in the front-controller.

Then it checks for the **Module**, if found, it accesses the directory

Then checks the **Controller**, which is a class, if exists it loads it

Then checks the **Action**, a method in the Controller. If exists it executed the method

But if the Action doesn't exist, it will fall back to the *Controller::actionIndex()* method

If Controller doesn't exist, it will fall back to the *Index.php* controller

If Module doesn't exist, it will fall back to *Main* module


### Application Routes (Customed Routes)

Sometimes, you may want to change the way the URL is displayed. Maybe because of SEO purposes, or something has changed, or whatever... you will have to alter the routes in the application config fille at: /App/$AppName/Config.ini

Each application contains a `Config.ini` file. It is loaded by `Voodoo\Core\Application` upon initialization, and contains the necessary settings such as which controller to load by default, the views settings etc. It also contains your application's routes. 

If you open /App/Www/Config.ini, look for the routes key, and you should fine something that looks like this: 
 
/App/Www/Config.ini

	[routes]
	    path["(:any)"] = "$1"

It, by default, uses the basic route schema

	site.com/Module/Controller/Action/Segments/?paramName=paramValue

Let's say you have this url path:

	site.com/users/profile/info/mardix

- users = module
- profile = controller
- info = action
- mardix = segment

But you want to short it to: `site.com/profile/mardix`

You can create a new route path in your Config.ini file like this

	[routes]
		path["/profile/(:any)"] = "/Users/Profile/Info/$1"
		path["(:any)"] = "$1"

Now when someoneone enters `site.com/profile/mardix` , the application will be re-routed to `/Users/Profile/Info/mardix`.

You can add as many routes as you want. Also, the routes can have as many directives as you want and they support regex for more advanced matching.

	[routes]
		path["/profile/(:any)"] = "/Users/Profile/Info/$1"
		path["/music/([rap|techno|compas]+)/(:num)"] = "/Music/Selection/genre/$1/song/$2"
		path["/blog/(:alphanum)/(:num)/(:any)"] = "Main/Blog/Category/$1/post/$2/$3/"
		path["(:any)"] = "$1"

The /music route: site.com/music/rap/1526 -> /Music/Selection/genre/{rap}/song/{1526}
	
	Music = module
	Selection = controller
	genre = action
	rap = segement #1, accessed in the controller $this->getSegment(1)
	song = segement #2, accessed in the controller $this->getSegment(2)
	1526 = segement #3, accessed in the controller $this->getSegment(3)
		

#### Stuff to understand about routes:

- To reduce overhead, Application's routes are run before the call to any MVC application

- Re-routing is not a redirect. It routes a path to a new path without changing the URL in the address bar

- Voodoo routes are case incensitive. So accessing */Profile/info/mardix* and */pRofile/inFo/mardix* will result to the same place

- Only alphanumeric [AZ-09] characters are accepted. All the other one will be ignored when routing. So */pro-file/info/mardix* will still result to */Profile/Info/mardix/*. Same as */about-us/* which result to */aboutus/*. So you can cleverly write your SEO friendly url and still keep your application running with no headache.


*It is important to understand that routes are matched in the order they are added, and as soon as a URL matches a route, routing is essentially "stopped" and the remaining routes are never tried. Because the default route matches almost anything, including an empty url, new routes must be place before it.*

	[routes]
		path[/article/(:num)] = "/Blog/Article/Read/$1"
		path["/profile/(:any)"] = "/Main/Profile/Info/$1"
		path["(:any)"] = "$1"

---

Now you see how Voodoo works with Front-Controller and Routes, let's get in the sauce of the meat. Ayibobo!

---

## Model-View-Controller

As you already notice above, your MVC application will reside under an $AppName and a $ModuleName.

- Models: `App\$AppName\$ModuleName\Model`

- Controllers: `App\$AppName\$ModuleName\Controller`

- Views: `App/$AppName/$ModuleName/Views`


Let's say we are using the `Www` application and the `Main` module.


**Controller**

All controllers will reside under the namespace: `App\Www\Main\Controller`

App/Www/Main/Controller/Index.php

	<?php

	namespace App\Www\Main\Controller;

	use Voodoo;

	class Index extends BaseController
	{

	    public function actionIndex()
	    {
			$this->view()->setPagetTitle("Hello World");
	    }


	    public function actionAboutUs()
	    {

	    }
		//...

	}

All controllers are extended by `BaseController` which is extended by `Voodoo\Core\Controller` which contains the methods needed to get params, load views, get models, etc... BaseController is set so you can share functionnalities with other controllers

**Views**

All views a placed at: `App/Www/Main/Views`

Views, by default, are HTML template files. Based on the separation of concerns principle, the views role are to display data. No logic whatsoevet. To accomplish that, Voodoo uses *[Mustache.php](https://github.com/bobthecow/mustache.php/tree/cd6bfaefd30e13082780b3711c2aa4b92d146b1e)* as the template engine to render HTML. 

Other engines, such as Twig can be used instead of Mustache. 

But if you are thinking about using PHP as template system itself, like  `<title><?= $this->title; ?></title>` , well my friend, I'm not sure Voodoo is the right tool for you. Anyway...

That's how a template file looks like with mustache template:

	<html>
		<head>
			<title>{{this.title}}</title>
		</head>

		<body>
			<ul>
				{{#articles}}
					<li>{title}</li>
				{{/articles}}
			</ul>
		</body>
	</html>



**Model**

By default, all models based on our app.json will reside under the namespace: `App\Www\Main\Model`

Also by default, Voodoo uses [VoodOrm](https://github.com/mardix/VoodOrm), a micro-ORM which functions as both a fluent select query API and a CRUD model class. It is extended in `\Voodoo\Core\Model`.

When models are created in the app.json for example

	{
		...

        "models" : [
            {
                "name" : "User",
                "dbAlias" : "MyDB",
                "table" : "user",
                "primaryKey" : "id",
                "foreignKey" : "%s_id"
            }
        ]
		...
	}


- **name**: the class name

- **dbAlias** : the db alias name. We'll get back on it a little down.

- **table** : the table name to associate with this class

- **primaryKey** : the table primary key

- **foreignKey** : the foreign key

When the model is created, these settings will be added in the class so it can connect to the DB via PDO


**/App/Cong/DB.ini** and **dbAlias** 

Voodoo requires that you store your DB settings (dbname, username, password, host) in `/App/Conf/DB.ini` . Each settings is associated to an alias. 

Database connections are managed by  `Voodoo\Core\ConnectionManager()` which makes sure the connection is connected once per alias, even if it's called 20 billions times (I hope not, lol). 

This is how the /App/Conf/DB.ini looks like:

	[MyDB] 
	    type       = "mysql"
	    host       = "localhost" 
	    port       = 3306
	    user       = "root" 
	    password   = "my_password" 
	    dbname     = "my_db_name"  

Where `MyDB` is the alias name. 

So in the app.json `"dbAlias" : "MyDB"` refers to the the above settings, and the ConnectionManager will use it to connect to the DB.

So having the dbAlias, allows you to have your settings in one place, and if you change anything in it, like host or dbName, all the classes that use `MyDB` will still work fine. 

By extending the model to `Voodoo\Core\Model`  the connection is cleverly handled by Voodoo, you don't need to do jack. 

So once our App/Conf/app.json is ran by App/voodooist.php you should have a model class similar to this:

/App/Www/Main/Model/User.php

	<?php

	namespace App\Www\Main\Model;

	use Voodoo;

	class User extends Voodoo\Core\Model
	{
	    protected $tableName = "user";

	    protected $primaryKeyName = "id";

	    protected $foreignKeyName = "%s_id";

	    protected $dbAlias = "MyDB";

	}

Since Voodoo uses VoodOrm, so automatically we can use all of VoodOrm methods like, `App\Www\Main\Model\User::find()`, `App\Www\Main\Model\User::count()` etc...

As you can see, Voodoo avoids mixing database calls, HTML tags and business logic in the same script. That is why we separate each part of our application, and this practice is called Separation of Concerns.

---


## First Voodoo App

Now that you have learned how Voodoo works, let's create a simple application: Hello World at the path: `site.com/hello-world/` and `site.com/articles`

To do so, we will use Voodooist to setup our files.

Edit the file: `App/Conf/app.json `

	{
	    "createPublicAssets" : true,
	    "applications" : [
	        {
	            "name" : "www",
	            "modules" : [
	                {
	                    "name" : "Main",
	                    "template" : "Default",
	                    "isApi" : false,
	                    "omitViews" : false,

	                    "controllers" : [
	                        {
	                            "name" : "Index",
	                            "actions" : ["index", "hello-world", "articles"]
	                        }
	                    ],

	                    "models" : [
	                        {
	                            "name" : "Articles",
	                            "dbAlias" : "MyDB",
	                            "table" : "articles",
	                            "primaryKey" : "id",
	                            "foreignKey" : "%s_id"
	                        }
	                    ]
	                }
	            ]
	        }
	    ]
	}


Run this below

		cd /App
 		php ./voodooist.php


And you should get the following filesystem:


	|
	+-- App/
		|
		+-- .htaccess
		|
		+-- Www
			|
			+-- Config.ini
			|
			+-- Main
				|
				+-- Controller/
					|
					+-- Index.php
							::actionIndex()
							::actionHelloWorld()
							::actionArticles()
				|
				+-- View/
					|
					+-- Index/
						|
						+-- Index.html
						|
						+-- HelloWorld.html
						|
						+-- Articles.html
					|
					+-- _assets/
						|
						+-- js/
						|
						+-- css/
						|
						+-- img/
					|
					+-- _includes/
						|
						+-- flash-message.html
						|
						+-- pagination.html
					|
					+-- _layouts/
						|
						+-- default.html
						|
						+-- head-tag.html
						|
						+-- footer.html
						|
						+-- header.html
	|
	+-- assets/ [+]
	|
	+-- vendor/ [+]
	|
	+-- index.php
	|
	+-- .htaccess


App/Www/Main/Controller/Index.php


	<?php

	namespace App\Www\Main\Controller;

	use App\Ww\Main\Model;

	class Index extends BaseController
	{

		/**
		 * The default action.
		 **/
		public function actionIndex(){
			$this->view()->setPageTitle("Welcome to my Site");
		}

		/**
		* @action hello-world
		*/
		public function actionHelloWorld(){
			$this->view()->setPageTitle("Hello World");
		}


		/**
		* @action articles
		*/
		public function actionArticles(){

			$this->view()->setPageTitle("Latest Articles");

			$articles = (new Model\Articles)->find();

			$articlesData = [];
			foreach ($articles as $article) {
				$articlesData[] = [
					"id" => $article->getPK(),
					"title" => $article->title,
					"date" => $this->formatDate($article->published_date)
				];
			}

			$this->view()->assign("articles", $articlesData);
		}

	}

App/Www/Main/Model/Articles.php

	<?php

	namespace App\Www\Main\Model;

	use Voodoo;

	class Articles extends Voodoo\Core\Model
	{

	    protected $tableName = "articles";

	    protected $primaryKeyName = "id";

	    protected $foreignKeyName = "%s_id";

	    protected $dbAlias = "MyDB";

	}



#### Create the Views:



App/Www/Main/Views/Index/Index.html

	<div>
		<h3>Welcome to my site</h3>

		Blah blah blah
	</div>


App/Www/Main/Views/Index/HelloWorld.html

	<div>
		<h3>Hello World</h3>

		Blah blah blah
	</div>



App/Www/Main/Views/Index/Articles.html

	<div>
		<h3>Latest Articles</h3>

		<ul>
			{{#articles}}
				<li><a href='{{this.controller.url}}/read/{{id}}'>{{title}}</a></li>
			{{/articles}}
		</ul>

	</div>



**App/Www/Main/Views/_layout/default.html**

Voodoo requires a container template file. The container is a place holder for the action's view. The container may contain  header, footer, sidebar etc.. but must include the tag below to include the action's view page

		{{%include @actionView}}

Technically, the container is the layout of your application, and your action files are files to be included in the layout.

So let's create the container. It is placed at:

/App/Www/Main/Views/_layout/default.html

	<html>
		<head>
			<title>{{this.title}}</title>
		</head>

		<body>
			{{%include _layouts/header}}

				{{%include @actionView}}

			{{%include _layouts/footer}}
		</body>
	</html>



Now everything is setup and ready to go.

If someone accesses `site.com/articles/`, he/she will see the list of all articles.

---

That's pretty much it.

Go to [VoodooPHP.org](http://voodoophp.org) for a more in depth documentation.

---

VoodooPHP was created by Mardix and released under the MIT License.

Enjoy!

(c) This Year  Mardix

(oof! that was a lot of writing, June 9 2013)

---
