# Sidebar configuration
A Sidebar will be added to both the support and addons page if the sidebar configuration is passed to the `register` method. The sidebar configuration should be an array with the following keys:
- `plugin_resources` (array) - An array with the configuration for the plugin resources.
- `additional_resources` (array) - An array with the configuration for additional resources.

Both of these takes the exact same `links` configuration as the [support](./support.md) page, and will output the links in the order they are passed in the array.

```php
array(
    'plugin_resources' => array(
        array(
            'text'   => 'Your link title',
            'target' => '_blank',
            'class'  => 'my-link-class my-other-link-class',
            'href'   => array(
                'en' => 'https://example.com',
                'sv' => 'https://example.se',
            ),
        ),
    ),
    'additional_resources' => array(
        array(
            'text'   => 'Your link title',
            'target' => '_blank',
            'class'  => 'my-link-class my-other-link-class',
            'href'   => array(
                'en' => 'https://example.com',
                'sv' => 'https://example.se',
            ),
        ),
    ),
)
```

The plugin resources is used for links to the plugin documentation, support, and other resources related to the plugin. The additional resources is used for links to other resources that can be useful for the user, but is not directly related to the plugin. For example the blog page, or general FAQ page.
