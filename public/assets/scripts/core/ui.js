nelliel.ui.hideShowThread = function(element, command, content_id) {
	if (element == null && content_id == null) {
		return;
	}

	var store_update = true;

	if (content_id == null) {
		content_id = nelliel.core.contentID(element.getAttribute("data-content-id"));
	}

	var post_container = document.getElementById("post-container-" + content_id.id_string);

	if (post_container == null) {
		return;
	}

	var thread_header_options = post_container.querySelector(".thread-header-options");

	if (element == null) {
		element = thread_header_options.querySelector(".toggle-thread");
	}

	var post_header_options = post_container.querySelector(".post-header-options");
	var thread_expand = document.getElementById("thread-expand-" + "cid_" + content_id.thread_id + "_0_0");
	var hide_thread_elements = post_container.querySelectorAll(".js-hide-thread");
	
	for (let element of hide_thread_elements) {
		nelliel.ui.toggleHidden(element);
	}

	if (command === "hide-thread") {
		dataBin.hidden_threads[content_id.id_string] = Date.now();
	} else if (command === "show-thread") {
		delete dataBin.hidden_threads[content_id.id_string];
	} else if (command === "apply") {
		store_update = false;
	} else {
		return;
	}

	if (store_update) {
		nelliel.core.storeInLocalStorage(dataBin.hidden_threads_id, dataBin.hidden_threads);
	}

	// Special case since OP is (presently) considered to be the thread
	if (!dataBin.hidden_posts.hasOwnProperty(content_id.id_string)) {
		nelliel.ui.hideShowPost(null, null, content_id);
	}

	nelliel.ui.swapContentAttribute(element, "data-alt-visual");
	nelliel.ui.switchDataCommand(element, null, null);
	nelliel.ui.toggleHidden(thread_expand);
}

nelliel.ui.hideShowPost = function(element, command, content_id) {
	if (element == null && content_id == null) {
		return;
	}

	var store_update = true;

	if (content_id == null) {
		content_id = nelliel.core.contentID(element.getAttribute("data-content-id"));
	}

	var post_container = document.getElementById("post-container-" + content_id.id_string);

	if (post_container == null) {
		return;
	}
	var post_header_options = post_container.querySelector(".post-header-options");

	if (element == null) {
		element = post_header_options.querySelector(".toggle-post");
	}
	
	var hide_post_elements = post_container.querySelectorAll(".js-hide-post");
	
	for (let element of hide_post_elements) {
		nelliel.ui.toggleHidden(element);
	}

	var content_container = post_container.querySelector(".content-container");
	nelliel.ui.toggleHidden(content_container);

	if (command == "hide-post") {
		dataBin.hidden_posts[content_id.id_string] = Date.now();
	} else if (command == "show-post") {
		delete dataBin.hidden_posts[content_id.id_string];
	} else if (command === "apply") {
		store_update = false;
	} else {
		return;
	}

	if (store_update) {
		nelliel.core.storeInLocalStorage(dataBin.hidden_posts_id, dataBin.hidden_posts);
	}

	nelliel.ui.switchDataCommand(element, null, null);
	nelliel.ui.swapContentAttribute(element, "data-alt-visual");
}

nelliel.ui.hideShowFile = function(element, command, content_id) {
	if (element == null && content_id == null) {
		return;
	}

	if (content_id == null) {
		content_id = nelliel.core.contentID(element.getAttribute("data-content-id"));
	}

	var file_container = document.getElementById("upload-container-" + content_id.id_string);

	if (file_container == null) {
		return;
	}

	if (element == null) {
		element = file_container.querySelector(".toggle-file");
	}

	var hide_file_elements = file_container.querySelectorAll(".js-hide-file");
	
	for (let element of hide_file_elements) {
		nelliel.ui.toggleHidden(element);
	}

	if (command == "hide-file") {
		dataBin.hidden_files[content_id.id_string] = Date.now();
	} else if (command == "show-file") {
		delete dataBin.hidden_files[content_id.id_string];
	} else if (command === "apply") {
		store_update = false;
	} else {
		return;
	}

	nelliel.core.storeInLocalStorage(dataBin.hidden_files_id, dataBin.hidden_files);
	nelliel.ui.switchDataCommand(element, null, null);
	nelliel.ui.swapContentAttribute(element, "data-alt-visual");
}

nelliel.ui.hideShowEmbed = function(element, command, content_id) {
	if (element == null && content_id == null) {
		return;
	}

	if (content_id == null) {
		content_id = nelliel.core.contentID(element.getAttribute("data-content-id"));
	}

	var embed_container = document.getElementById("upload-container-" + content_id.id_string);
	var embed_frame = embed_container.querySelector(".embed-frame");

	if (element == null) {
		element = embed_container.querySelector(".toggle-embed");
	}

	var hide_embed_elements = embed_container.querySelectorAll(".js-hide-embed");
	
	for (let element of hide_embed_elements) {
		nelliel.ui.toggleHidden(element);
	}
	
	nelliel.ui.toggleHidden(embed_frame);

	if (command == "hide-embed") {
		dataBin.hidden_embeds[content_id.id_string] = Date.now();
	} else if (command == "show-embed") {
		delete dataBin.hidden_embeds[content_id.id_string];
	} else if (command === "apply") {
		store_update = false;
	} else {
		return;
	}

	nelliel.core.storeInLocalStorage(dataBin.hidden_embeds_id, dataBin.hidden_embeds);
	nelliel.ui.switchDataCommand(element, "hide-embed", "show-embed");
	nelliel.ui.swapContentAttribute(element, "data-alt-visual");
}

nelliel.ui.applyHideContent = function() {
	for (var id in dataBin.hidden_threads) {
		nelliel.ui.hideShowThread(null, "apply", nelliel.core.contentID(id));
	}

	for (var id in dataBin.hidden_posts) {
		nelliel.ui.hideShowPost(null, "apply", nelliel.core.contentID(id));
	}

	for (var id in dataBin.hidden_files) {
		nelliel.ui.hideShowFile(null, "apply", nelliel.core.contentID(id));
	}

	for (var id in dataBin.hidden_embeds) {
		nelliel.ui.hideShowEmbed(null, "apply", nelliel.core.contentID(id));
	}
}

nelliel.ui.showHideUploadMeta = function(element) {
	if (element === null) {
		return;
	}

	var content_id = nelliel.core.contentID(element.getAttribute("data-content-id"))
	var upload_container = document.getElementById("upload-container-" + content_id.id_string);
	var meta_element = upload_container.querySelector(".upload-meta");
	nelliel.ui.swapContentAttribute(element, "data-alt-visual");
	nelliel.ui.toggleHidden(meta_element);
	nelliel.ui.switchDataCommand(element, null, null);
}

nelliel.ui.expandCollapseThread = function(element, command, dynamic = false) {
	if (element === null) {
		return;
	}

	var content_id = nelliel.core.contentID(element.getAttribute("data-content-id"));
	var thread_url = element.getAttribute("data-url");
	var target_element = document.getElementById("thread-expand-" + content_id.id_string);

	if (target_element === null) {
		return;
	}

	if (command === "expand-thread") {
		dataBin.collapsedThreads[content_id.id_string] = target_element.innerHTML;
		var request = new XMLHttpRequest();
		request.open('GET', thread_url);
		request.responseType = "document";
		request.onload = function() {
			if (request.status === 200) {
				var expandHTML = request.response.getElementById("thread-expand-" + content_id.id_string).innerHTML;
				target_element.innerHTML = expandHTML;
				nelliel.core.unhideJSonly(target_element);
			}
		};

		request.send();
	}

	if (command === "collapse-thread") {
		target_element.innerHTML = dataBin.collapsedThreads[content_id.id_string];
	}

	nelliel.ui.swapContentAttribute(element, "data-alt-visual");
	nelliel.ui.switchDataCommand(element, null, null);
	nelliel.ui.switchDataURL(element);
	nelliel.ui.applyHideContent();
}

nelliel.ui.highlightPost = function(content_id) {
	var post_container = document.getElementById("post-container-" + content_id.id_string);

	if (post_container != null) {
		if (post_container.className.indexOf('post-highlight') === -1) {
			post_container.className += " post-highlight";
		}
	}
}

nelliel.ui.unhighlightPost = function(content_id) {
	var post_container = document.getElementById("post-container-" + content_id.id_string);

	if (post_container != null) {
		post_container.className = post_container.className.replace(/\post-highlight\b/g, "");
	}
}

nelliel.ui.inlineExpand = function(element) {
	if (element === null || !element.hasAttribute("data-alt-tag")) {
		return;
	}

	var expanded_id = element.id + "-expanded";
	var expanded_element = document.getElementById(expanded_id);

	if (expanded_element == null) {
		var new_tag = element.getAttribute("data-alt-tag");
		var new_element = document.createElement(new_tag);
		new_element.id = expanded_id;
		var new_location = "";

		if (element.hasAttribute("data-alt-src")) {
			new_location = element.getAttribute("data-alt-src");
		}

		if (new_tag == "video") {
			var new_source = document.createElement("source");
			new_source.setAttribute("src", new_location);
			new_element.appendChild(new_source);
			new_element.setAttribute("controls", "");
		}

		if (new_tag == "img") {
			new_element.setAttribute("src", new_location);
			new_element.setAttribute("data-command", "inline-reduce");
		}

		if (element.hasAttribute("data-alt-dims")) {
			var alt_dims = element.getAttribute("data-alt-dims");
			var new_width = alt_dims.match(/w([0-9]+)/)[1];
			var new_height = alt_dims.match(/h([0-9]+)/)[1];
			new_element.setAttribute("width", new_width);
			new_element.setAttribute("height", new_height);
		}

		new_element.setAttribute("data-preview-id", element.id);
		element.parentNode.parentNode.appendChild(new_element);
		expanded_element = new_element;
	} else {
		nelliel.ui.toggleHidden(expanded_element);
	}

	var hide_element = document.createElement("a");
	hide_element.setAttribute("src", "");
	hide_element.setAttribute("data-command", "inline-reduce");
	hide_element.setAttribute("data-expanded-id", expanded_id);
	hide_element.innerText = "[-]";
	expanded_element.parentNode.insertBefore(hide_element, expanded_element);
	nelliel.ui.toggleHidden(element);
}

nelliel.ui.inlineReduce = function(element) {
	if (element === null) {
		return;
	}

	var hide_element = element.parentNode.querySelector('[data-expanded-id]');

	if (hide_element != null) {
		var expanded_id = hide_element.getAttribute("data-expanded-id");
		hide_element.remove();
	}

	var expanded_element = document.getElementById(expanded_id);

	if (!expanded_element.hasAttribute("data-preview-id")) {
		return;
	}

	if (expanded_element.getName == "video") {
		expanded_element.pause();
	}

	nelliel.ui.toggleHidden(expanded_element);
	var original_id = expanded_element.getAttribute("data-preview-id");
	var original_element = document.getElementById(original_id);
	nelliel.ui.toggleHidden(original_element);
}

nelliel.ui.showLinkedPost = function(element, event) {
	if (element === null) {
		return;
	}

	var href = element.getAttribute("href");
	var anchor_matches = href.match(/#t([0-9]+)p([0-9]+)/);

	if (anchor_matches == null) {
		return;
	}

	var post_id = "cid_" + anchor_matches[1] + "_" + anchor_matches[2] + "_0";

	if (document.getElementById("post-cite-popup-" + post_id) !== null) {
		return;
	}

	var offsetY = window.pageYOffset || document.documentElement.scrollTop;
	var offsetX = window.pageXOffset || document.documentElement.scrollLeft;
	var popup_div = document.createElement("div");
	popup_div.id = "post-cite-popup-" + post_id;
	popup_div.setAttribute("class", "post-cite-popup");
	var element_rect = element.getBoundingClientRect();
	nelliel.ui.addBoundingClientRectProperties(element_rect);

	var request = new XMLHttpRequest();
	request.open('GET', element.getAttribute("href"));
	request.responseType = "document";

	request.onload = function() {
		if (request.status === 200) {
			var quoted_post = request.response.getElementById("post-container-" + post_id);
			quoted_post.removeAttribute('id');
			quoted_post.className = quoted_post.className.replace(/\op post\b/g, "reply post");
			popup_div.appendChild(quoted_post);
			element.parentNode.insertBefore(popup_div, element);
			var popup_rect = quoted_post.getBoundingClientRect();
			nelliel.ui.addBoundingClientRectProperties(popup_rect);
			popup_div.style.left = (element_rect.right + offsetX + 5) + 'px';
			popup_div.style.top = ((element_rect.y + offsetY) - (popup_rect.height / 2)) + 'px';
		}
	};

	request.send();
}

nelliel.ui.hideLinkedPost = function(element, event) {
	if (element === null) {
		return;
	}

	var href = element.getAttribute("href");
	var anchor_matches = href.match(/#t([0-9]+)p([0-9]+)/);

	if (anchor_matches == null) {
		return;
	}

	var post_id = "cid_" + anchor_matches[1] + "_" + anchor_matches[2] + "_0";
	var target_popup = document.getElementById("post-cite-popup-" + post_id);

	if (target_popup !== null) {
		target_popup.remove();
	}
}

nelliel.ui.citePost = function(content_id) {
	if (content_id == null) {
		return;
	}

	var wordswordswords = document.getElementById("wordswordswords");
	wordswordswords.value = wordswordswords.value + '>>' + content_id.post_id + '\n';
}

nelliel.ui.toggleHidden = function(element) {
	if (element === null) {
		return;
	}

	if (element.className.search("hidden") === -1) {
		element.className += " hidden";
	} else {
		element.className = element.className.replace(/\bhidden\b/g, "");
	}
}

nelliel.ui.swapContentAttribute = function(element, attribute_name) {
	if (element === null) {
		return;
	}

	var inner = element.innerHTML;
	element.innerHTML = element.getAttribute(attribute_name);
	element.setAttribute(attribute_name, inner);
}

nelliel.ui.switchDataCommand = function(element, option_one, option_two) {
	if (element === null) {
		return;
	}

	if (option_one === null && option_two === null) {
		var command = element.getAttribute("data-command");
		var alt_command = element.getAttribute("data-alt-command");
		element.setAttribute("data-command", alt_command);
		element.setAttribute("data-alt-command", command);
		return;
	}

	var data_command = element.getAttribute("data-command");

	if (data_command.indexOf(option_one) > -1) {
		element.setAttribute("data-command", option_two);
	} else if (data_command.indexOf(option_two) > -1) {
		element.setAttribute("data-command", option_one);
	}
}

nelliel.ui.switchDataURL = function(element) {
	if (element === null) {
		return;
	}

	var url = element.getAttribute("data-url");
	var alt_url = element.getAttribute("data-alt-url");

	if (url === null || alt_url === null) {
		return;
	}

	element.setAttribute("data-url", alt_url);
	element.setAttribute("data-alt-url", url);
}

nelliel.ui.addBoundingClientRectProperties = function(bounding_rect) {
	bounding_rect.width = bounding_rect.width || bounding_rect.right - bounding_rect.left;
	bounding_rect.height = bounding_rect.height || bounding_rect.bottom - bounding_rect.top;
	bounding_rect.x = bounding_rect.x || bounding_rect.left + (bounding_rect.width / 2);
	bounding_rect.y = bounding_rect.y || bounding_rect.top + (bounding_rect.height / 2);
}
