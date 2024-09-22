# Addons Tab Configuration
The addons tab can output information about different plugins that can be installed to extend or enhance the functionality or features of the current plugin or the WordPress site in general that works well with the current plugin.

Currently the addons configuration only accepts one key, `items`, which is a list of the items to display on the addons page.

## Items
The `items` key should contain an array of items that will be output on the addons page as examples of plugins that can be installed to extend the functionality of the current plugin.

An item can contain the following keys:
- `slug` (string) - The slug of the plugin that can be installed.
- `title` (string) - The title of the plugin.
- `image` (array) - An array with the type of image and the source of the image. The type can be either `base64` or `url`. If `base64` is used, the source should be a base64 encoded data tag of an image. If `url` is used, the source should be the URL to the image. The base64 version will not need to make a external request to load a image, so this can improve the load times of the page.
- `description` (array) - An array with the different languages as keys and the description as the value. The language can be any 2 letter ISO 639-1 language code. And will be matched with the current language of the site. Always pass `en` since that will be used as a fallback if the current language is not found.
- `link` (array) - An array with the same properties as the link configuration for the [support](./support.md) page.

The image passed should be in a 16:9 format to fit the container the best.
```php
array(
    'slug'        => 'your-addon-plugin-slug',
    'title'       => 'Addon Plugin Title',
    'image'       => array(
        'type' => 'base64',
        'src'  => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABbklEQVQ4T6WTvUoDQRDGv',
    ),
    'description' => array(
        'en' => 'Your english product description.',
        'sv' => 'Your swedish product description.',
    ),
    'link'        => array(
        'target' => '_blank',
        'text'   => array(
            'en' => 'Learn more',
            'sv' => 'Läs mer',
        ),
        'href'   =>
        array(
            'en' => 'https://example.com/your-addon-plugin-slug/',
            'sv' => 'https://example.se/your-addon-plugin-slug/',
        ),
    ),
),
```

If a plugin is hosted on wordpress.org, you can pass the url to be `/wp-admin/plugin-install.php?tab=plugin-information&plugin={{YOUR-ADDON-SLUG}}&TB_iframe=true&width=772&height=1005'` and the normal WordPress plugin information window will be shown to the user, and they can install and activate the plugin from there. Change the `{{YOUR-ADDON-SLUG}}` to the slug of the plugin you want to show information about. Also you need to add the class `'thickbox open-plugin-details-modal'` to the link to make it open in the modal window.
Example:
```php
array(
    'slug'        => 'your-addon-slug',
    'title'       => 'Your Addon Plugin Title',
    'image'       => array(
        'type' => 'base64',
        'src'  => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAABbklEQVQ4T6WTvUoDQRDGv',
    ),
    'description' => array(
        'en' => 'Your english product description.',
        'sv' => 'Your swedish product description.',
    ),
    'link'        => array(
        'class' => 'thickbox open-plugin-details-modal',
        'text'  => array(
            'en' => 'Learn more',
            'sv' => 'Läs mer',
        ),
        'href'  =>
        array(
            'en' => '/wp-admin/plugin-install.php?tab=plugin-information&plugin=your-addon-slug&TB_iframe=true&width=772&height=1005',
            'sv' => '/wp-admin/plugin-install.php?tab=plugin-information&plugin=your-addon-slug&TB_iframe=true&width=772&height=1005',
        ),
    ),
)
```

If the plugin you are showing is installed or active, a button linking to the plugins page will be shown instead of a link, so the user can manage the plugin from there.
