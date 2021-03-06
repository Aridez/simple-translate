- [About simple-translate](#about-simple-translate)
- [Installation](#installation)
- [Basic usage](#basic-usage)
  - [Auto merging](#auto-merging)
- [Customizing the bundles](#customizing-the-bundles)
  - [Child components](#child-components)
  - [Adding additional translations](#adding-additional-translations)
- [Contributing](#contributing)

# About simple-translate
Simple translate is a package that allows to use the Laravel translations on your vue components easily and efficiently. This package creates a bundle of translations in json format with just the ones used for each of the components, only the required information is passed to the view. It's as easy as calling the translations from your vue componens using `{{$attrs.__('<translation_key>')}}` running the `php artisan translate:bundle` command that will automatically find and create the translation files and then adding the `@bundle(<your component>)` directive on your vue component.

# Installation

```sh
composer require aridez/simple-translate
```

# Basic usage

The first step is to go to the Vue components where you want to add translations and call the `$attrs.__('<message>')` everywhere where you want the translations to show up:

```php
//ExampleComponent.vue
```
```html
<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{$attrs.__('welcome.title')}}</div>

                    <div class="card-body">
                        {{$attrs.__('welcome.message')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log(this.$attrs.__("I'm being translated!"))
        }
    }
</script>
```

Once the translations are configured its time to update the bundles:

```
php artisan translate:bundle
```

And finally, call the `@bundle(<name>)` directive, note that the `<name>` parameter is the name of the Vue component file without the extension:


```html
<example-component @bundle('ExampleComponent')></example-component>
```

**That's it!** The bundles will be stored on a new `resources\simple-translate\bundles` folder in case you want to check them out, and remember to run the `php artisan translate:bundle` when you update them.



## Auto merging

Sometimes a project may have more than one Vue component sharing the same file name. In those cases the translations for both files are merged and bundled together. It is better to avoid this whenever it is possible in order to keep the data sent to the client at a minimum.

# Customizing the bundles

There's a few instances where its useful to be able to customize what translations a bundle contains or to create a custom translation bundle from scratch. These bundles can be configures at the `resources\simple-translate\custom-bundles.json` file. It contains an array that can be filled with the following structure:

```json
[
    {
        "name": "CustomBundle",
        "bundles": ["Component1", "Component2"],
        "keys": ["Key1", "Key2"]
    },
    ...
]
```

- _name:_ Defines the name of the bundle, make sure it's unique
- _bundles:_ Defines an array of existing bundle names, all of their translations will be merged
- _keys:_ Defines an array of translations keys, these will be merged inside the custom bundle too


## Child components

To make translations available for a parent and all of its child components, the first thing to do is to create a new custom bundle on the `resources\simple-translate\custom-bundles.json` file. Create a new element in the array and setting a unique *name* and define the *bundles* of the parent and child components:

```json
[
    {
        "name": "CustomComponent",
        "bundles": ["ParentComponent", "ChildComponent"],
        "keys": []
    },
    ...
]
```

Once defined, create the bundles with `php artisan translate:bundle` and use them on the parent component with the @bundle directive specified above:

```html
<parent-component @bundle(CustomComponent)></parent-component>
```

To make all of these available for the chlid component you need to pass down the the attributes by binding them:

```html
<child-component v-bind="$attrs"></child-component>
```
That's it! Now the function and the translations will be available from the child component too!

## Adding additional translations

In case you are dynamically generating the translations to be used, you can add additional translations from your files in a custom bundle as follows:

```json
[
    {
        "name": "CustomComponent",
        "bundles": ["ExampleComponent"],
        "keys": ["auth.throttle"]
    },
    ...
]
```

That way the `auth.throttle` tranlsation together with all the ones found inside `ExampleComponent.vue` will be merged together under the new `CustomComponent`. 

# Contributing

If you are looking to extend or improve the functionality of simple-translate your contribution is welcome! 

To set up this package for development clone this folder into a `packages\aridez` in the root of your Laravel application where you plan to test the new functionality. To import this package locally define a custom repository in your `composer.json` file:

```php
    "repositories": [
        {
          "type": "path",
          "url": "./packages/aridez/simple-translate"
        }
      ]
```

You can now require your local package in the Laravel application using your chosen namespace of the package:

```
composer require aridez/simple-translate
```

You can find more information about setting up this package for development on [larapackage.com](https://laravelpackage.com/).

Once your code is merged, it is available for free to everybody under the MIT License. Publishing your Pull Request on the simple-translate GitHub repository means that you agree with this license for your contribution.

:email: info@skytanet.com

Did you like this package? [Help me continue developing open source projects](https://github.com/sponsors/Aridez)
