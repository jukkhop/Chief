Chief
=====
A PHP Framework
---------------

Chief is a PHP framework that makes you create apps faster without holding your hand.
By utilizing the latest PHP has to offer and adding an excellent set of essential tools
at your disposal, Chief will make you fall in love with PHP once again.

It's so easy and simple
-----------------------

With just under 30 files, Chief is stripped of all superfluous content that you probably wouldn't use anyways. A simple set of commands and guidelines are enough to make your life easier. With a structure this small, you'll learn the ins and outs in no time.

Creating your app is intuitive and fast. The file structure is simple: everything you create is a module. A module has a controller, and it can contain multiple views and models. Modules are stored in modules/. In addition to modules, Chief stores the important stuff in system/, plugins in plugins/ and your layout in layout/.

Chief includes an install script that creates the required database structure for you. Installing takes under a minute from clicking the Download button above to being greeted by a lovely Hello World message.

It's blazing fast and light as a feather
----------------------------------------

Because Chief if is void of useless stuff and has only the files you really, really need, performance tends to be pretty good. With under 30 files, deploying is effortless.

Chief weighs only 14kb. With an codebase this small, we just haven't been able to fit that many bugs in. That's a shame, but it also means that you'll spend more time coding your app than dodging malfunctioning parts.

It's extensible
---------------

Because of its simplicity, Chief can be easily extended. The most obvious way is to add modules, but you can also create plugins and add external libraries. For more advanced hackers, feel free to take a stab at the core files – they're designed to allow that.

Chief is by no means a ready framework. You make it your own. It allows you to do the basic stuff just fine, but when you need something a bit more specialized, you are the one who knows best how to do it, not the framework. Chief just helps you do it easier.

In the past, Chief has been used to create multidomain environments, run a distributed CMS, power an enterprise class bookkeeping software, and much more. Chief poses no restrictions to what you can build.

It's helpful
------------

Chief has tools to make your life easier. Built in notification system and a set of often needed functions help you make cooler apps faster.

Chief also handles image resizing and caching automatically. No matter how you store your images, when they are requested through Chief, you can add parameters at the end of the file to affect the output. Requesting IMG_5324.jpg?800x600C automatically resizes the image to 800 by 600 pixels, cropping the overflowing parts out. The result is cached, so this image needs to be generated only once. Pretty damn great, right?

Chief also includes Twitter Bootstrap to get you into speed with building your layout. But you can always delete it if you are hardcore.

It's straightforward
--------------------

Chief uses one to one mapping when translating URLs to actual functions. So, let's say you are accessing a page through the URL www.example.com/blog/article/chief_is_great/. Chief will try to find a module named blog (it's actually just a class), run a function named article in that class, passing rest of the arguments in the URL as parameters.

You never have to define routes or try to figure out where the hell that request is shooting. Some people may not like things this simple, but they'll be busy arguing semantics while you'll be publishing your new shiny app and earning all the glory.

It speaks database
------------------

Chief offers tools for accessing the database in a simple but effective manner. There's no heavy object-relational mapping, just sensible helper functions that make manipulating the database a breeze. What do you think about this:

```php
$articles = $this->db->all("SELECT * FROM news");
```

Or this?

```php
$this->db->insert('news', [
  'title'     => $title,
  'slug'      => Common::slug($title),
  'date'      => date('Y-m-d H:i:s'),
  'published' => isset($_POST['published']),
  'user_id'   => null
]);
```

It has kick ass plugins
-----------------------

Chief has plugins that make creating tables and forms easier than ever. By utilizing method chaining and anonymous functions, defining your tables and forms in PHP actually becomes a viable option. Take a look at this:

```php
$form = $this->plugin('form')
  ->setData($event)
  ->hidden('id')
  ->text('name', 'Event name')->required()
  ->date('date', 'Date')->dateFormat('n/j/Y')
  ->text('price', 'Admission price')->transform(function($p) {
    return number_format($p, 2, ',', '')
  })->width(50)->append('€')
  ->submit('submit', 'Save event');
  
if($form->isSent()) {
  $errors = $form->setValues($_POST)->validate();
  if(empty($errors)) {
    # No errors, I'm gonna save this so hard!
  }
}
	
echo $form;
```

Holy crap that looks neat. Tables can be created just as easily. Chief also has an plugin for handling images. You can also create your own plugins and share them with others.
