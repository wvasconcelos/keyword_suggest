KEYWORD SUGGEST
Get product suggestions as soon as you start typing keywords into the search box.
This customizable plug-in runs product searches on the background and will start offering products to your customers as soon as a sufficient number of characters is available in the keyword search box (3 characters by default). The feedback is similar to what users get from larger retail stores or from search engines like Google, Bing and Yahoo.
This plug-in offers keyboard navigation. The up and down arrow keys, enter and esc keys allow users to move up and down the list, open the selected product (enter) or close the list (esc).
An intuitive interface packaged in a plug-in that is easy to add and remove from your online store (no database modifications or core file rewrites).

Installation:
1. Rename the YOUR_TEMPLATE folder based on your store's template name.

2. Copy the includes folder into your web store's root folder (3 files, no rewrites to core files should be required)
Files:
./includes/templates/YOUR_TEMPLATE/jscript/keyword_sugggest.js
./includes/templates/YOUR_TEMPLATE/css/keyword_suggest.css
./includes/classes/ajax/zcProductSuggest.php

3. Add a function call to your keyword search box's input tag (see an example below).
File Name and Location
	./includes/templates/YOUR_TEMPLATE/sideboxes/tpl_search_header.php

Note: if you cannot find that file in your template, edit the file under the template_default folder.

Add (at the top, before the <?php directive):
	<link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_TEMPLATE; ?>css/keyword_suggest.css" />
	<script type="text/javascript" src="<?php echo DIR_WS_TEMPLATE; ?>jscript/keyword_suggest.js"></script>

Replace (in 2 places):
this.value = \'\';"

With:
this.value = \'\';" onKeyUp="ProductSuggest(this);"

4. Make any adjustment, as needed.
--

TROUBLESHOOTING:
1. Images not loading: if you are using a different name for your images folder, edit the following line on the plugin's JavaScript file:
img.setAttribute("src", "images/" + image);

2. Suggestion box not aligning correctly with my keyword search box: edit the following lines on the plugin's JavaScript file to reposition the keyword suggest box:
const posHorizontalOffset = 0;
const posVerticalOffset = 25;

3. I want to temporarily disable the plugin: Open the JavaScript file and remove the following from the input tag:
onKeyUp="ProductSuggest(this);"
