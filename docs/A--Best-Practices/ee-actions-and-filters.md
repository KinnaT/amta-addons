# EE Actions and Filters

Event Espresso family of plugins make use of the WordPress event driven plugin api via [action and filter hooks](http://codex.wordpress.org/Plugin_API).  Those familiar with this method of connecting and integrating code will love what's possible with EE.  In this document we're highlighting the naming schema we use throughout the code for the hooks

## Naming convention for Actions and Filters within classes or functions.

### Action Hooks: 
`AHEE__{CLASS NAME}{METHOD}{location within method}`

  * `AHEE` = Action Hook Event Espresso
  * `__` = double underscore separator
  * `{CLASS NAME}` = the name of the class the hook is in
  * `{METHOD}` = the name of the method/function the hook is in
  * `{location within method}` = where the hook is within the method, either literally (e.g.: begin or end) or pertaining to what's happening programatically (e.g. before_loading_modules)

Example:

`do_action( ‘AHEE__EE_Config__construct__begin’, $this );`


### Filter Hooks: 

`FHEE__{CLASS NAME}{METHOD}{name of variable being filtered}`

* `FHEE` = Filter Hook Event Espresso
* `__` = double underscore separator
* `{CLASS NAME}` = the name of the class the hook is in
* `{METHOD}` = the name of the class function the hook is in
* `{name of variable being filtered}` = what’s being filtered (ie: if the variable was `$my_array`, then the hook would end with `__my_array` )

Example:

`$CFG = apply_filters( ‘FHEE__Config__get_espresso_config__CFG’, $CFG );`


### Hooks not in a class

For hooks that are not within a class, simply drop the CLASS NAME from the hook name ie:

`do_action( 'AHEE__registration_page_attendee_information__start', $event_queue );`

 ### Handling private or protected methods

Some class methods have extra underscores at the beginning of their name because they are private, so those hooks will have THREE underscores before the method name (two for the separator and then the one that is part of the method name. ie: 

`FHEE__EE_Config___register_shortcodes__installed_shortcodes`

**Exceptions:**

However, for magic methods like `__construct()`, don’t add the extra underscores, cuz that would just be silly. see the example above for action hooks.

## Template Hooks

Follow the same naming schema for action and filter hooks except instead of class name and function name you use the following:

**Action Hooks:** `AHEE__{template_name}template{location within template}`

**Filter Hooks:**  `FHEE__{template_name}template{name of variable being filtered}`