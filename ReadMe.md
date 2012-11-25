
# Voodoo

---

Name: Voodoo

License: MIT

Design: Modular MVC

Requirements: PHP 5.4

Compliance: PSR-0, PSR-1, PSR-2

Author: [Mardix](http://github.com/mardix)

Copyright: Mardix and other contributors

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
- Routing
- [VoodOrm](https://github.com/mardix/VoodOrm)
- [Annotation](https://github.com/mardix/AnnotationReader)
- [Pagination](https://github.com/mardix/Paginator)
- Bootstrap CSS/JS framework
- AddOns
- HTTP Request
- Voodooist (generate code on the fly)


---
## Clone & Download

If you are interested at Voodo, you can clone the repo or download as zip or tar.gz

Git Clone (notice the dot at the end)

	git clone git://github.com/VoodooPHP/Voodoo.git .


[Download Zip](https://github.com/VoodooPHP/Voodoo/zipball/master)

[Download Tar](https://github.com/VoodooPHP/Voodoo/tarball/master)
    
---

## Setup

After you download Voodoo, you will need to set it up via command line with the Voodooist. 

Enter the command below

		> cd $WHERE_IT_IS/src/Voodoo/Voodooist
 		> php do-voodoo.php

---

## FileSystem

Once Voodoo has been setup, you will have a filesystem that looks like this:


	|
	+-- App/
		|
		+-- .htaccess
		|
		+-- Config
			|
			+-- 
		|
		+-- Www
			|
			+-- Main
				|
				+-- Config.ini
				|
				+-- Controller/
				|
				+-- Model/
				|
				+-- View/
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
		+-- jQuery/
	|
	+-- Voodoo
		|
		+-- Core/
		|
		+-- Voodooist/
		|
		+-- init.php
		|
		+-- System.ini
		|
		+-- .htaccess
	|
	+-- index.php
	|
	+-- .htaccess

---

## Voodooist

Voodooist is a command line tool that generates controllers, controllers' actions, views, models. Technically it setup your MVC application. 

Voodooist requires App/Config/app-schema.json for it to setup your MVC environment. (Read more below). If app-schema.json doesn't exist, Voodooist will create one.

This code execute the Voodooist

		> cd $VOODOO_PATH/src/Voodoo/Voodooist
 		> php do-voodoo.php


#### - App/Config/app-schema.json

App/Config/app-schema.json is a JSON file that contains the layout of your application, including modules, controllers, controller's action etc. It is ran by Voodooist to setup your appliaction. A simple app-schema.json looks like this:

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
	                            "actions" : ["index", "login", "logout", "about"]
	                        },
	                        {
	                            "name" : "Account",
	                            "actions" : ["index", "info", "preferences"]
	                        } 						
	                    ],
	                
	                    "models" : [
	                        {
	                            "name" : "MySampleModel",
	                            "dbAlias" : "MyDB",
	                            "table" : "my_table_name",
	                            "primaryKey" : "id",
	                            "foreignKey" : "%s_id"
	                        }
	                    ]
	                }           
	            ]
	        }  
	    ]
	}

Running the code below will execute and create, if not exist, the files and paths

		> cd $VOODOO_PATH/src/Voodoo/Voodooist
 		> php do-voodoo.php

	|
	+-- App/
		|
		+-- .htaccess
		|
		+-- Www
			|
			+-- Main
				|
				+-- Config.ini
				|
				+-- Controller/
					|
					+-- Index.php
							::action_index()
							::action_login()
							::action_logout()
							::action_about()
					|
					+-- Account.php
							::action_index()
							::action_info()
							::action_preferences()					
				|
				+-- Model/
					|
					+-- MySampleModel.php
				|
				+-- View/
					|
					+-- Index/
						|
						+-- index.html
						|
						+-- login.html
						|
						+-- logout.html
						|
						+-- about.html
					|
					+-- Account/
						|
						+-- index.html
						|
						+-- info.html
						|
						+-- preferences.html			
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
						+-- container.html
						|
						+-- flash-message.html
						|
						+-- footer.html
						|
						+-- header.html
						|
						+-- pagination.html
	|
	+-- assets/ [+]
	|
	+-- Voodoo/ [+]
	|
	+-- index.php
	|
	+-- .htaccess  

---

## Routing & Execution

Voodoo cleverly routes to the right module of the application by using the URL schema 

		site.com/Module/Controller/Action/Segments/?paramName=paramValue

* Module: The container of the MVC application

* Controller: A class accepting the user request

* Action: A method of the controller's class

* Segments: extra parameters that are passed in the action

* ParamName/ParamValue : Parameters from the query url

##### *How does Voodoo excute your application?*

It first checks for the **Module**, if found, it accesses the directory 

Then checks the **Controller**, which is a class, if exists it loads it 

Then checks the **Action**, a method in the Controller. If exists it executed the method

But if the Action doesn't exist, it will fall back to the *Controller::action_index()* method

If Controller doesn't exist, it will fall back to the *Index.php* controller

If Module doesn't exist, it will fall back to *Main/* module

#### Application Routes (Customed Routes)

If you want to alter the way your urls are displayed: 

	site.com/Module/Controller/Action/Segments/?paramName=paramValue

you need to alter your application `routes`. The routes are located in your application Config.ini file. 

Voodoo lets you create customed url to where ever you want.

Let's say you have this url path:

	site.com/Main/Profile/Info/mardix

But you want to short it to: `site.com/profile/mardix`

You can create a new route path in your Config.ini file like this

	[routes]
		path["/profile/(:any)"] = "/Main/Profile/Info/$1"

Now when someoneone enters `site.com/profile/mardix` , the visitor will be re-routed to `/Main/Profile/Info/mardix`. 

The routes can have as many directives as you want and they support regex for more advanced matching.

Stuff to understand:

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

## Understanding Voodoo Application

Voodoo applications are divided into Applications & Modules

---

### Application (multi-applications)

Application is the upper level of your program. Everything must be in an application. An application can be another site sharing the same Voodoo code base of other application. Hence making Voodoo multi-sites enabled. 

 You can have *Site1.com*, *Site2.com*, *SiteX.com* all under the same environment sharing the same front controller, same Voodoo code base. And since your application is namespaced PSR-0, it's easy to re-use code from other applications. 

Inside of each applications are the applications modules. By default Voodoo creates the **WWW** which is the default entry point of your site. In large, Applications are sets of Modules.

Once an application has been selected, all urls follow the schema:  `/Module/Controller/Action/Segments/?paramName=paramValue` 


That's how multiple applications looks like: 

	|
	+-- App/
		|
		+-- .htaccess
		|
		+-- Site1
			|
			+-- Main
				|
				+-- Config.ini
				|
				+-- Controller/
				|
				+-- Model/
				|
				+-- View/
		|
		+-- Site2
			|
			+-- Main
				|
				+-- Config.ini
				|
				+-- Controller/
				|
				+-- Model/
				|
				+-- View/
		|
		+-- SiteX
			|
			+-- Main
				|
				+-- Config.ini
				|
				+-- Controller/
				|
				+-- Model/
				|
				+-- View/			
	|
	+-- assets/ [+]
	|
	+-- Voodoo/ [+]
	|
	+-- index.php
	|
	+-- .htaccess




Now you understand how an application is setup in Voodoo, let's take a look at bootstrap file.

---

## Voodoo Bootstrap

Voodoo use the front controller **/index.php** to access your application's MVC.

That's how it looks like:

	<?php
	// index.php

	use Voodoo;
	
	$VoodooPHP_Dir = __DIR__;
	
	require($VoodooPHP_Dir."/Voodoo/init.php");
	
    /**
     * Set the default app to access. By default it's Www
     * @type string
     */
    $application = "www";

    /**
     * The URI
     * @type string
     */
    $path = implode("/",Voodoo\Core\Http\Request::getUrlSegments());

    /**
     * Let's do it!
     */
    (new Voodoo\Core\Voodoo($application, $path))->doMagic();
	
With this file in place, when people accesses your application, they are sent to this file. It this is how it is read: 

	http://site.com/index.php?/Module/Controller/Action/Segments/?paramName=paramValue

On Apache, by adding the following in your .htaccess

	#.htaccess
	Options +FollowSymlinks
	RewriteEngine on

	RewriteBase /
	
	# Route everything to index.php
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteCond %{REQUEST_FILENAME} !\.(js|css|gif|jpg|jpeg|png|ico|swf|pdf)$
	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteRule ^(.*)$ index.php?/$1 [L,NC]
 
All the urls will be rerouted to index.php unless the file or directory exist.

	http://site.com/Module/Controller/Action/Segments/?paramName=paramValue


Let's break the url above down relative to our index.php bootstrap file.

In the bootsrap file, we set `$application = "www";` `www` could be `site1`, `site2` or any other application's name. 

Then we set `$path = implode("/",Voodoo\Core\Http\Request::getUrlSegments());` 

`Voodoo\Core\Http\Request::getUrlSegments()` returns `$_SERVER["QUERY_STRING"]` into an array.

So if you have a url: `http://site.com/profile/mardix`  it will return: `profile/mardix`

And from there, Voodoo is going to do it's magic

We have the application and the boostrap set, let's get to know about the modules.

---

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
			+-- Main
				|
				+-- Config.ini
				|
				+-- Controller/
					|
					+-- Index.php
							::action_index()
							::action_about()				
				|
				+-- Model/
					|
					+-- MySampleModel.php
				|
				+-- View/
					|
					+-- Index/
						|
						+-- index.html
						|
						+-- about.html		
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
						+-- container.html
						|
						+-- flash-message.html
						|
						+-- footer.html
						|
						+-- header.html
						|
						+-- pagination.html
	|
	+-- assets/ [+]
	|
	+-- Voodoo/ [+]
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

## Model-View-Controller

Now you understand Voodoo's modular system, let's get to know MVC. I will be brief about MVC, sorry, if you don't know MVC, please do a quick google, you will find many help. 

So, you have your application's module ready. It contains of set of MVC structure. 

Let's say we are using the `Www` application and the `Main` module.

**App/Www/Main/Model** : contains your application's model. [VoodOrm](https://github.com/mardix/VoodOrm) is used with Voodoo, but you can use whathever you want, like Doctrine etc. 

	<?php
	
	namespace App\Www\Main\Model;
	
	use Voodoo;
	
	class Articles extends Voodoo\Core\Model
	{
	    
	    protected $tableName = "my_table_name";
	
	    protected $primaryKeyName = "id";
	  
	    protected $foreignKeyName = "%s_id";
	      
	    protected $dbAlias = "MyDB";    
	
	}

Since Voodoo uses VoodOrm, so automatically we can use all of VoodOrm methods like, `App\Www\Main\Model\Articles::find()`, `App\Www\Main\Model\Articles::count()` etc...


**App/Www/Main/Controller** : contains all the controllers class. Controllers essentially control the flow of the application.

	<?php
	// App/Www/Main/Controller/Index.php
	
	namespace App\Www\Main\Controller;
	
	use Voodoo;
	
	class Index extends Voodoo\Core\Controller
	{

	    public function action_index()
	    {
			$this->view()->setPagetTitle("Hello World");
	    }


	    public function action_aboutus()
	    {

	    }
		//...
		
	}

All controllers are extended by `Voodoo\Core\Controller` which contains the methods needed to get params, load views, get models, etc... 


**App/Www/Main/View** : Contains html template files for your to be displayed when the application is being rendered. By default, Voodoo uses *[Mustache](https://github.com/bobthecow/mustache.php/tree/cd6bfaefd30e13082780b3711c2aa4b92d146b1e)* as the template engine to render HTML. If you want you can use Twig. But if you are thinking about using PHP as template system itself, like  `<title><?= $this->title; ?></title>` , well my friend, I don't know what to tell you. Voodoo may not be for you. 

That's how a template file looks like with mustache template:

	<html>
		<head>
			<title>{{App.Page.Title}}</title>
		</head>
		
		<body>
			<ul>
				{{#articles}}
					<li>{title}</li>
				{{/articles}}
			</ul>
		</body>
	</html>

As you can see, Voodoo avoids mixing database calls, HTML tags and business logic in the same script. That is why we separate each part of our application, and this practice is called Separation of Concerns.


---

## First Voodoo App

Now that you learned how Voodoo works, let's create a simple application: Hello World at the path: `site.com/hello-world/` and `site.com/articles`

To do so, we will use Voodooist to setup our files. 

Edit the file: `App/Config/app-schema.json `

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


This code execute the Voodooist

		> cd $VOODOO_PATH/src/Voodoo/Voodooist
 		> php do-voodoo.php


And you will see the following filesystem:


	|
	+-- App/
		|
		+-- .htaccess
		|
		+-- Www
			|
			+-- Main
				|
				+-- Config.ini
				|
				+-- Controller/
					|
					+-- Index.php
							::action_index()
							::action_helloworld()
							::action_articles()
				|
				+-- View/
					|
					+-- Index/
						|
						+-- index.html
						|
						+-- helloworld.html
						|
						+-- articles.html
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
						+-- container.html
						|
						+-- flash-message.html
						|
						+-- footer.html
						|
						+-- header.html
						|
						+-- pagination.html
	|
	+-- assets/ [+]
	|
	+-- Voodoo/ [+]
	|
	+-- index.php
|
+-- .htaccess         

#### Create the controller: *App/Www/Main/Controller/Index.php*


	<?php

	namespace App\Www\Main\Controller;

	use Voodoo\Core;

	class Index extends Core\Controller{

		/**
		 * The default action.
		 **/
		public function action_index(){
			$this->view()->setPageTitle("Welcome to my Site");
		}

		/**
		* @action hello-world
		*/
		public function action_helloworld(){
			$this->view()->setPageTitle("Hello World");
		}


		/**
		* @action articles
		*/
		public function action_articles(){
		
			$this->view()->setPageTitle("Latest Articles");
			
			$articles = $this->getModel("Articles")->getTop10();

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


#### Create the Model: *App/Www/Main/Model/Articles.php*

	<?php
	
	namespace App\Www\Main\Model;
	
	use Voodoo;
	
	class Articles extends Voodoo\Core\Model
	{
	
	    protected $tableName = "articles";
	
	    protected $primaryKeyName = "id";
	  
	    protected $foreignKeyName = "%s_id";
	    
	    protected $dbAlias = "MyDB";    
	    

		public function getTop10() 
		{
			return (new self())
						->limit(10)
						->orderBy("views DESC");
		}

	}



#### Create the Views: 

##### *App/Www/Main/Views/Index/articles.html*
		
	<div>
		<h3>Latest Articles</h3>
		
		<ul>
			{{#articles}}
				<li>{{title}}</li>
			{{/articles}}
		</ul>
		
	</div>



##### *App/Www/Main/Views/Index/helloworld.html*
		
	<div>
		<h3>Hello World</h3>
		
		Blah blah blah
	</div>


##### *App/Www/Main/Views/Index/index.html*
		
	<div>
		<h3>Welcome to my site</h3>
		
		Blah blah blah
	</div>


##### *App/Www/Main/Views/Index/container.html*
Voodoo requires a container template file. The container is a place holder for the action's view. The container may contain  header, footer, sidebar etc.. but must include the tag below to include the action's view page
  
		{{%include @PageBody}}  

Technically, the container is the layout of your application, and your action files are files to be included in the layout. 

So let's create the container. It is placed at:

/App/Www/Main/Views/_includes/container.html
		
	<html>
		<head>
			<title>{{App.Page.Title}}</title>
		</head>
		
		<body>
			{{%include _includes/header}}
			
				{{%include @PageBody}}
				
			{{%include _includes/footer}}
		</body>
	</html>



Now everything is setup and ready to go.

If someone accesses `site.com/articles/`


Voodoo will load the action: `App/Www/Main/Controller/Index::action_articles()`. 

Upon rendering, Voodoo will load the view: `App/Www/Main/Views/Index/articles.html`. 

`articles.html` will be automatically included in `App/Www/Main/Views/_includes/container.html`

---


Go to [VoodooPHP.org](http://voodoophp.org) for ta more in depth documentation.

---

VoodooPHP was created by Mardix and released under the MIT License. 

Enjoy!

(c) 2012 Mardix 

---
