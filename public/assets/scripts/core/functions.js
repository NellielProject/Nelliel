
nelliel.core.hashHandler = function(event) {
	var post_anchor_match = location.hash.match(/#t([0-9]+)p([0-9]+)/);
	var cite_match = location.hash.match(/cite/);

	if (post_anchor_match != null) {
		var content_id = nelliel.core.contentID('cid_' + post_anchor_match[1] + '_' + post_anchor_match[2] + '_0');

		if (cite_match != null) {
			nelliel.ui.citePost(content_id);
			return;
		}

		if (event != null && event.oldURL != event.newURL) {
			var last_anchor_match = event.oldURL.match(/#t([0-9]+)p([0-9]+)/);

			if (last_anchor_match != null) {
				var last_content_id = nelliel.core.contentID('cid_' + last_anchor_match[1] + '_' + last_anchor_match[2] + '_0');
				nelliel.ui.unhighlightPost(last_content_id);
			}
		}

		if (content_id.post_id != content_id.thread_id) {
			nelliel.ui.highlightPost(content_id);
		}
	}
}

nelliel.core.contentID = function(id_string) {
	var content_id = {};
	var segments = id_string.split('_');
	content_id.id_string = id_string;
	content_id.thread_id = segments[1];
	content_id.post_id = segments[2];
	content_id.content_order = segments[3];
	return content_id;
}

nelliel.core.unhideJSonly = function(element) {
	var elements = element.querySelectorAll("[data-jsonly]");
	var element_count = elements.length;

	for (i = 0; i < element_count; i++) {
		nelliel.ui.toggleHidden(elements[i]);
	}
}
nelliel.core.updatePostPassword = function(new_password) {
	nelliel.core.storeInLocalStorage("post_password", new_password);
}

nelliel.core.setStyle = function(style, update = false) {
	var empty_style = style == null || style === "";

	if (update) {
		nelliel.core.storeInLocalStorage("style_override", style);
	}

	var allstyles = document.getElementsByTagName("link");
	var menu_style = null;
	var default_style_attr = null;
	var valid_set = false;

	for (i = 0; i < allstyles.length; i++) {
		if (allstyles[i].getAttribute("data-style-type") == "style-board") {
			var style_id = allstyles[i].getAttribute("data-id");
			allstyles[i].disabled = true;

			if (allstyles[i].getAttribute("rel") === "stylesheet") {
				default_style_attr = allstyles[i];
			}

			if (empty_style) {
				if (allstyles[i].getAttribute("rel") === "stylesheet") {
					allstyles[i].disabled = false;
					menu_style = style_id;
					valid_set = true;
				}
			} else {
				if (style_id === style) {
					allstyles[i].disabled = false;
					menu_style = style_id;
					valid_set = true;
				}
			}
		}
	}

	if (!valid_set) {
		default_style_attr.disabled = false;
		menu_style = default_style_attr.getAttribute("data-id");
	}

	var style_menus = document.getElementsByClassName("styles-menu");

	for (i = 0; i < style_menus.length; i++) {
		style_menus.item(i).value = menu_style;
	}
}

nelliel.core.dawn = function() {
	var date = new Date();

	if (date.getHours() == "05" && date.getMinutes() == "59") {
		var p = document.createElement("p");
		p.innerHTML = '<img src="imgboard.php?special=dawn" width="960" height="720" alt="The Dawn Is Your Enemy">';
		var i = document.querySelector(".interstitial");

		if (i != null) {
			i.insertBefore(p, i.firstChild);
		}
	}
}