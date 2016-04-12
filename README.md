# CMB2 Related Links

Special CMB2 Field that allows users to add a related links repeating field group. This is not a standard field type, but instead a function you use in combination with `CMB2::add_field()`. Each link can be populated with existing WordPress content by clicking on the search button.

The only required parameter is the `'id'` parameter, though you can override almost all of the arguments by passing them in.

This field requires the [CMB2 Post Search field](https://github.com/WebDevStudios/CMB2-Post-Search-field).

### Example
```php
// Add a related links field.
$cmb->add_field( cmb2_related_links_field( array( 'id' => 'yourprefix_related_links' ) ) );
```

If you are looking to bundle this field in your plugin or theme, you will need to pass the second parameter which is an array of all the translateable strings:
```php
$translateable = array(
	'description' => __( 'Add links, or select from related content by clicking the search icon.', 'yourtextdomain' ),
	'group_title' => __( 'Link {#}', 'yourtextdomain' ),
	'link_title'  => __( 'Title', 'yourtextdomain' ),
	'link_url'    => __( 'URL', 'yourtextdomain' ),
	'find_text'   => __( 'Find/Select related content', 'yourtextdomain' ),
);
$cmb->add_field( cmb2_related_links_field(
	array( 'id' => 'yourprefix_related_links' ),
	$translateable
) );
```
