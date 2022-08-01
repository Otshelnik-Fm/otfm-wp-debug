## About

OtFm WP-Debug is a MU-WordPress plugin for debug.  
Easy, simple, clean: [screenshot](http://joxi.ru/Q2KdBJLTyzvQdA)  
And Left Query Panel: [screenshot](http://joxi.ru/Dr8eO04iKk4NDm)

-----------

## How to install

Create a directory on: <code>site/wp-content/mu-plugins</code> (if not exists)  
Copy a file in directory: <code>site/wp-content/mu-plugin/otfm-wp-debug.php</code>  
Work!

-----------

## Start debugging

1. <code>vd()</code> (like var_dump) - convenient debugging instead of print_r or var_dump
2. <code>vdd()</code> analog of vd, but with a die; at the end. When should I stop further work
3. <code>vda()</code> (var_dump admin) - output to the screen for admin only
4. <code>vdl()</code> (var_dump log) - we write to the server logs. When we can't display it on the screen (or this is
   the ajax request debug, for example).
5. <code>vdx()</code> (var_dump XHR) - for ajax debugging (see incoming POST data on the browser's XHR tab)
   [Example](http://joxi.ru/p27e3MbinQNYlr)

<code>vd( $variable, 1, [ 'post_content', 'post_name' ] )</code>  
First argument: $variable - debugging data  
Second argument: 1 - (default:false) - optional parameter. If you need to output in a fixed window on the top left.
Useful for output bottlenecks. Useless for cycles.  
Fixed block can be hidden (spoiler on details tag)  
Third argument: keys who hide in output. For example, post_content - contains a large amount of data

<code>vd( $variable, [ 'post_content', 'post_name' ] )</code> - this magic: second argument, if you need exclude
key|keys

## Left Query Panel

The query panel shows the running time of php, the number of database queries and the memory consumed.  
The previous request is also indicated in parentheses. Past values of indicators help with debugging - it can be a cache
indicator or a quantitative indicator of the changes you have made.

[Example](http://joxi.ru/Dr8eO04iKk4NDm)

By default, the query panel is disabled. You can show it by adding a GET-request to the :site.com/?wpdbg   
Or find in otfm-wp-debug.php string: <code>$wpdbg_settings['left_panel'] = false;</code>  
Parameters:  
false - disabled;   
true - enabled all (in dev environment);   
'admin' - if visible in admin;  
Set the values you need

Or set <code>site.com/?wpdbg</code> - to show panel

If the panel is enabled in the config and interferes - enter <code>site.com/?panel=off</code> and it will not be output

-----------

## Changelog

**2022-08-01**  
v1.2.0

* If the panel is enabled in the config and interferes - enter <code>site.com/?panel=off</code> and it will not be
  output
* set left panel visible for 'admin'
* line breaks in the code and minor edits

**2022-03-14**  
v1.1.0

* Added left query panel. Find string <code>$wpdbg_settings['left_panel'] = false;</code> and see docblock

**2022-02-28**  
v1.0.0

* Release

-----------

## Author

[**Wladimir Druzhaev**](https://otshelnik-fm.ru/) (Otshelnik-Fm)

-----------

## License

Licensed under the MIT License  