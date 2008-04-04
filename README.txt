Customization

1)in memobox.html

DRAGGABLES_SEL
Set the CSS style selector to match which are the draggable items
Ex: var DRAGGABLES_SEL = '.titre>a';

MEMOBOX_CONTAINER
Set the CSS style selector to match the element you want to append the memobox to
Ex: var MEMOBOX_CONTAINER = "#navigation"; 

MEMOBOX_HEADER
Set the HTML of the memobox header. It must have the id "memobox_heading".
The heading_text can be modified in the language files (lang/memobox_nn.php). 
Ex: var MEMOBOX_HEADER = '<p id="memobox_heading">+heading_text+</p>';

MEMOBOX_MODE
Set the way a page can be added to the memobox. 
Can be "link", to do it with a link, or "drag" to do it dragging an item to the memobox.
After a change in the mode, be sure to delete the SPIP cache or you won't see your change. 
 
2)Define your styles in memobox.css

3)Have fun :)
