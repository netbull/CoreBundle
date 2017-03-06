CoreBundle
==========
[![Build Status](https://travis-ci.org/netbull/AuthBundle.svg?branch=master)](https://travis-ci.org/netbull/AuthBundle)<br>
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/f91df530-6930-44c3-b300-0ac712498063/big.png)](https://insight.sensiolabs.com/projects/f91df530-6930-44c3-b300-0ac712498063)

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require netbull/core-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...

            new Netbull\CoreBundle\NetbullCoreBundle(),
        ];

        // ...
    }

    // ...
}
```

Step 3: Configure bundle
-----------------------------
```yaml
    netbull_core:
        js_routing_path: <ASSETS_FOLDER>/<JS_FILE_NAME>.js
```

Step 4: Configure some routes
-----------------------------
Add `exposed: true` option to every route which you want to be available in the JS

```yaml
    some_route:
        path:     /
        options:
            expose: true
```

Step 5: Optionally add this line to allow dev mode of the urls
-----------------------------
```twig
    <head>
        // ...
        
        <script>
            window.DEBUG = {% if app.environment == 'dev'%}'/app_dev.php'{% else %}''{% endif %};
        </script>
        
        // ...
    </head>
```
