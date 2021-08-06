nelliel.ui.hideShowThread = function(element, command, content_id) {
    if (element == null && content_id == null) {
        return;
    }
    
    var store_update = true;

    if (content_id == null) {
        content_id = nelliel.core.contentID(element.getAttribute("data-content-id"));
    }
   
    var post_container = document.getElementById("post-container-" + content_id.id_string);
    var thread_header_options = post_container.querySelector(".thread-header-options");
    
    if (element == null) {
        element = thread_header_options.querySelector(".toggle-thread");
    }

    var post_header_options = post_container.querySelector(".post-header-options");
    var expand_thread = thread_header_options.querySelector(".expand-thread");
    var reply_thread = thread_header_options.querySelector(".reply-thread");
    var content_container = post_container.querySelector(".content-container");
    var comment_container = post_container.querySelector(".comment-container");
    var thread_expand = document.getElementById("thread-expand-" + "cid_" + content_id.thread_id + "_0_0");

    nelliel.ui.toggleHidden(post_header_options);
    nelliel.ui.toggleHidden(expand_thread);
    nelliel.ui.toggleHidden(reply_thread);

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
    nelliel.ui.toggleHidden(thread_expand);
    nelliel.ui.switchDataCommand(element, "hide-thread", "show-thread");
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
    var post_header_options = post_container.querySelector(".post-header-options");

    if (element == null) {
        element = post_header_options.querySelector(".toggle-post");
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

    nelliel.ui.switchDataCommand(element, "hide-post", "show-post");
    nelliel.ui.swapContentAttribute(element, "data-alt-visual");
}

nelliel.ui.hideShowFile = function(element, command, content_id) {
    if (element == null && content_id == null) {
        return;
    }

    if (content_id == null) {
        content_id = nelliel.core.contentID(element.getAttribute("data-content-id"));
    }
   
    var file_container = document.getElementById("file-container-" + content_id.id_string);
	var file_preview = file_container.querySelector(".file-preview");

    if (element == null) {
        element = file_container.querySelector(".toggle-file");
    }

    nelliel.ui.toggleHidden(file_preview);

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
    nelliel.ui.switchDataCommand(element, "hide-file", "show-file");
    nelliel.ui.swapContentAttribute(element, "data-alt-visual");
}

nelliel.ui.hideShowEmbed = function(element, command, content_id) {
    if (element == null && content_id == null) {
        return;
    }

    if (content_id == null) {
        content_id = nelliel.core.contentID(element.getAttribute("data-content-id"));
    }

    var embed_container = document.getElementById("embed-container-" + content_id.id_string);
	var embed_frame = embed_container.querySelector(".embed-frame");
    
    if (element == null) {
        element = embed_container.querySelector(".toggle-embed");
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
    var cids = [];

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

nelliel.ui.showHideFileMeta = function(element) {
    if (element === null) {
        return;
    }

    var content_id = nelliel.core.contentID(element.getAttribute("data-content-id"))
    var file_container = document.getElementById("file-container-" + content_id.id_string);
    var meta_element = file_container.querySelector(".file-meta");
    nelliel.ui.swapContentAttribute(element, "data-alt-visual");
    nelliel.ui.toggleHidden(meta_element);
    nelliel.ui.switchDataCommand(element, "show-file-meta", "hide-file-meta");
}

nelliel.ui.expandCollapseThread = function(element, command, dynamic = false) {
    if (element === null) {
        return;
    }

    var content_id = nelliel.core.contentID(element.getAttribute("data-content-id"));
    var thread_page = element.getAttribute("data-thread-page");
    var target_element = document.getElementById("thread-expand-" + content_id.id_string);
    var split_command = command.split("-");
    
    if (!target_element) {
        return;
    }

    if (dynamic) {
        var url = "imgboard.php?module=output&section=thread&actions=view&content-id=" + content_id.id_string + "&board-id=" + dataBin.board_id + "&thread=" + content_id.thread_id;

        if (dataBin.is_modmode) {
            url = url + "&modmode=true";
        }
        
        var command1 = "expand-thread-render";
        var command2 = "collapse-thread-render";
    } else {
        var url = "threads/" + content_id.thread_id + "/" + thread_page;
        var command1 = "expand-thread";
        var command2 = "collapse-thread";
    }

    if (command === "expand-thread" || command === "expand-thread-render") {
        dataBin.collapsedThreads[content_id.id_string] = target_element.innerHTML;
        var request = new XMLHttpRequest();
        request.open('GET', url);
        request.responseType = "document";
        request.onload = function() {
            if (request.status === 200) {
                var expandHTML = request.response.getElementById("thread-expand-" + content_id.id_string).innerHTML;
                target_element.innerHTML = expandHTML;
            }
        };

        request.send();
    }
    
    if (command === "collapse-thread" || command === "collapse-thread-render") {
        target_element.innerHTML = dataBin.collapsedThreads[content_id.id_string];
    }
    
    nelliel.ui.swapContentAttribute(element, "data-alt-visual");
    nelliel.ui.switchDataCommand(element, command1, command2);
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

nelliel.ui.inlineExpandReduce = function(element, command) {
    if (element === null) {
        return;
    }

    if (element.hasAttribute("data-other-dims")) {
        var new_location = element.getAttribute("data-other-loc");
        var old_location = element.getAttribute("src");
        var image_dims = element.getAttribute("data-other-dims");
        var width = image_dims.match(/w([0-9]+)/)[1];
        var height = image_dims.match(/h([0-9]+)/)[1];
        var old_width = element.getAttribute("width");
        var old_height = element.getAttribute("height");
        element.setAttribute("width", width);
        element.setAttribute("height", height);
        element.setAttribute("data-other-dims", 'w' + old_width + 'h' + old_height);
        element.setAttribute("src", new_location);
        element.setAttribute("data-other-loc", old_location);
        nelliel.ui.switchDataCommand(element, "inline-reduce", "inline-expand");
    }
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
            quoted_post.className = quoted_post.className.replace(/\op-post\b/g, "reply-post");
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

    var data_command = element.getAttribute("data-command");

    if (data_command.indexOf(option_one) > -1 ) {
        element.setAttribute("data-command", option_two);
    } else if (data_command.indexOf(option_two) > -1 ) {
        element.setAttribute("data-command", option_one);
    }
}

nelliel.ui.addBoundingClientRectProperties = function(bounding_rect) {
    bounding_rect.width = bounding_rect.width || bounding_rect.right - bounding_rect.left;
    bounding_rect.height = bounding_rect.height || bounding_rect.bottom - bounding_rect.top;
    bounding_rect.x = bounding_rect.x || bounding_rect.left + (bounding_rect.width / 2);
    bounding_rect.y = bounding_rect.y || bounding_rect.top + (bounding_rect.height / 2);
}
