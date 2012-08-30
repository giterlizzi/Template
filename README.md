Template
========

Basic Usage
-----------

* Step 1 - Create a template file (eg. layout.php)

```html
<html>
  <head>
    <title><?php echo $TITLE; ?></title>
  </head>
  <body>
    <h1><?php echo $TITLE; ?></h1>
    <p>Hello! My name is <?php echo $NAME; ?></p>
  </body>
</html>
```

* Step 2 - Create a PHP page (eg. page.php)

```php
<?php
  include('Template.php');

  $Template = new Template('/path/to/templates');

  $vars = array('TITLE' => 'First Page',
                'NAME'  => 'John);
              
  $Template->process('layout.php', $vars);
?>
```

* Step 3 - Result

```html
<html>
  <head>
    <title>First Page</title>
  </head>
  <body>
    <h1>First Page</h1>
    <p>Hello! My name is John</p>
  </body>
</html>
```
