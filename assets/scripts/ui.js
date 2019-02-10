nelliel.ui.hideShowThread = function(element, command) {
    if(element === null) {
        return;
    }

    var content_id = nelliel.core.contentID(element.getAttribute("data-content-id"));
    var post_files = document.getElementById("files-" + content_id.id_string);
    var post_contents = document.getElementById("post-contents-" + content_id.id_string);
    var post_header_options = document.getElementById("post-header-options-" + content_id.id_string);
    var thread_container = document.getElementById("thread-expand-" + "cid_" + content_id.thread_id + "_0_0");

    if (command === "hide-thread") {
        dataBin.hidden_threads[content_id.id_string] = Date.now();
    } else if (command === "show-thread") {    
        delete dataBin.hidden_threads[content_id.id_string];
    }

    nelliel.ui.toggleHidden(thread_container);
    nelliel.ui.toggleHidden(post_files);
    nelliel.ui.toggleHidden(post_contents);
    nelliel.ui.toggleHidden(post_header_options);
    nelliel.core.storeInLocalStorage(dataBin.hidden_threads_id, dataBin.hidden_threads);
    nelliel.ui.switchDataCommand(element, "hide-thread", "show-thread");
    nelliel.ui.swapContentAttribute(element, "data-alt-visual");
}

nelliel.ui.hideShowPost = function(element, command) {
    if(element === null) {
        return;
    }

    var content_id = nelliel.core.contentID(element.getAttribute("data-content-id"))
    var post_files = document.getElementById("files-" + content_id.id_string);
    var post_contents = document.getElementById("post-contents-" + content_id.id_string);


    if (command == "hide-post") {
        dataBin.hidden_posts[content_id.id_string] = Date.now();
    } else if (command == "show-post") {
        delete dataBin.hidden_posts[content_id.id_string];
    }

    nelliel.ui.toggleHidden(post_files);
    nelliel.ui.toggleHidden(post_contents);
    nelliel.core.storeInLocalStorage(dataBin.hidden_posts_id, dataBin.hidden_posts);
    nelliel.ui.switchDataCommand(element, "hide-post", "show-post");
    nelliel.ui.swapContentAttribute(element, "data-alt-visual");
}

nelliel.ui.applyHidePostThread = function() {
    var cids = [];

    for (var id in dataBin.hidden_threads) {
        var content_id = nelliel.core.contentID(id);
        var post_files = document.getElementById("files-" + content_id.id_string);
        var post_contents = document.getElementById("post-contents-" + content_id.id_string);
        var post_header_options = document.getElementById("post-header-options-" + content_id.id_string);
        var thread_container = document.getElementById("thread-expand-" + "cid_" + content_id.thread_id + "_0_0");
        var element = document.getElementById("hide-thread-" + "cid_" + content_id.thread_id + "_0_0");
        nelliel.ui.switchDataCommand(element, "hide-thread", "show-thread");
        nelliel.ui.swapContentAttribute(element, "data-alt-visual");

        if (cids.includes(id)) {
            continue;
        } else {
            cids.push(id);
        }

        nelliel.ui.toggleHidden(thread_container);
        nelliel.ui.toggleHidden(post_files);
        nelliel.ui.toggleHidden(post_contents);
        nelliel.ui.toggleHidden(post_header_options);
    }

    for (var id in dataBin.hidden_posts) {
        var content_id = nelliel.core.contentID(id);
        var post_files = document.getElementById("files-" + content_id.id_string);
        var post_contents = document.getElementById("post-contents-" + content_id.id_string);
        var element = document.getElementById("hide-post-" + content_id.id_string);

        nelliel.ui.switchDataCommand(element, "hide-post", "show-post");
        nelliel.ui.swapContentAttribute(element, "data-alt-visual");

        if (cids.includes(id)) {
            continue;
        } else {
            cids.push(id);
        }

        nelliel.ui.toggleHidden(post_files);
        nelliel.ui.toggleHidden(post_contents);
    }
}

nelliel.ui.showHideFileMeta = function(element) {
    if(element === null) {
        return;
    }

    var content_id = nelliel.core.contentID(element.getAttribute("data-content-id"))
    var meta_element = document.getElementById("file-meta-" + content_id.id_string);
    nelliel.ui.swapContentAttribute(element, "data-alt-visual");
    nelliel.ui.toggleHidden(meta_element);
    nelliel.ui.switchDataCommand(element, "show-file-meta", "hide-file-meta");
}

nelliel.ui.expandCollapseThread = function(element, command, dynamic = false) {
    if(element === null) {
        return;
    }

    var content_id = nelliel.core.contentID(element.getAttribute("data-content-id"));
    var target_element = document.getElementById("thread-expand-" + content_id.id_string);

    if (!target_element) {
        return;
    }

    if (dynamic) {
        var url = "imgboard.php?module=render&action=" + command.split('-')[0] + "-thread&board_id=" + dataBin.board_id + "&thread=" + content_id.thread_id;
        
        if(dataBin.is_modmode) {
            url = url + "&modmode=true";
        }
        
        var command1 = "expand-thread-render";
        var command2 = "collapse-thread-render";
    } else {
        var url = "threads/" + content_id.thread_id + "/thread-" + content_id.thread_id + "-" + command.split('-')[0] + ".html";
        var command1 = "expand-thread";
        var command2 = "collapse-thread";
    }

    var request = new XMLHttpRequest();
    request.open('GET', url);
    request.onload = function() {
        if (request.status === 200) {
            nelliel.ui.swapContentAttribute(element, "data-alt-visual");
            nelliel.ui.switchDataCommand(element, command1, command2);
            target_element.innerHTML = request.responseText;
            nelliel.ui.applyHidePostThread();
        }
    };

    request.send();
}

nelliel.ui.highlightPost = function(content_id) {
    var post_container = document.getElementById("post-container-" + content_id.id_string);
    
    if (post_container !== null) {
        if (post_container.className.indexOf('post-hightlight') === -1) {
            post_container.className += " post-highlight";
        } else {
            post_container.className = post_container.className.replace(/\post-highlight\b/g, "");
        }
    }
}

nelliel.ui.inlineExpandReduce = function(element, command) {
    if(element === null) {
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
    if(element === null) {
        return;
    }

    var href = element.getAttribute("href");
    var anchor_matches = href.match(/#t([0-9]+)p([0-9]+)/);
    var post_id = "cid_" + anchor_matches[1] + "_" + anchor_matches[2] + "_0";

    if (document.getElementById("post-quote-popup-" + post_id) !== null) {
        return;
    }

    var offsetY = window.pageYOffset || document.documentElement.scrollTop;
    var offsetX = window.pageXOffset || document.documentElement.scrollLeft;
    var popup_div = document.createElement("div");
    popup_div.id = "post-quote-popup-" + post_id;
    popup_div.setAttribute("class", "post-quote-popup");
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
    if(element === null) {
        return;
    }

    var href = element.getAttribute("href");
    var anchor_matches = href.match(/#t([0-9]+)p([0-9]+)/);
    var post_id = "cid_" + anchor_matches[1] + "_" + anchor_matches[2] + "_0";
    var target_popup = document.getElementById("post-quote-popup-" + post_id);

    if (target_popup !== null) {
        target_popup.remove();
    }
}

nelliel.ui.linkPost = function(element) {
    if(element === null) {
        return;
    }

    var content_id = nelliel.core.contentID(element.getAttribute("data-content-id"))
    document.postingform.wordswordswords.value = document.postingform.wordswordswords.value + '>>' + content_id.post_id + '\n';
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
    if(element === null) {
        return;
    }

    var inner = element.innerHTML;
    element.innerHTML = element.getAttribute(attribute_name);
    element.setAttribute(attribute_name, inner);
}

nelliel.ui.switchDataCommand = function(element, option_one, option_two) {
    if(element === null) {
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
