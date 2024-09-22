# Support Tab Configuration
The support tab can output information based on different types of items passed inside it.
Right now the support is limited to different types of links, but can be extended going forward with new types as the requirement for them is added.

The support configuration should be an array with the optional properties `links` and `link_texts`.
Please note that if none of the optional properties are set, the support page will not have any content except the sidebar.

## Links
The `links` key should contain an array of links that will be output on the support page.
These will be output in the order they are passed in the array.

The link array should contain the following keys:
- `text` (string) - The title of the link.
- `target` (string) - The target attribute for the link. For example `_blank` or `_self`. For `_blank` targets the plugin will automatically add a icon showing its an external link that will open in a new tab.
- `class` (string) - The class attribute for the link if you want to add a custom class. You can pass the class 'no-external-icon' to override the default external link icon that the plugin adds.
- `href` (array) - An array with the different languages as keys and the link as the value. The language can be any 2 letter ISO 639-1 language code. And will be matched with the current language of the site. Always pass `en` since that will be used as a fallback if the current language is not found.
```php
array(
    array(
        'text'   => 'Your link title',
        'target' => '_blank',
        'class'  => 'my-link-class my-other-link-class',
        'href'   => array(
            'en' => 'https://example.com',
            'sv' => 'https://example.se',
        ),
    ),
)
```

## Link Texts
The `link_texts` key should contain an array of texts that will be output on the support page.
These will be output in the order they are passed in the array.

The link text array should contain the following keys:
- `text` (array) - An array with the different languages as keys and the text as the value. The language can be any 2 letter ISO 639-1 language code. And will be matched with the current language of the site. Always pass `en` since that will be used as a fallback if the current language is not found.
- `link` (array) - An array with the link configuration for the text. The same as the link configuration above.

```php
array(
    'text' => array(
        'en' => 'Your english text that should contain a link %s.',
        'sv' => 'Your swedish text that should contain a link %s.',
    ),
    'link' => array(
        'text'   => 'Your link title',
        'target' => '_blank',
        'class'  => 'no-external-icon',
        'href'   =>
        array(
            'en' => 'https://example.com',
            'sv' => 'https://example.se',
        ),
    ),
),
