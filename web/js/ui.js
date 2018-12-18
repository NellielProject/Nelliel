nelliel.ui.hideShowThread = function(element, command) {
    var content_id = nelliel.core.contentID(element.getAttribute("data-content-id"))
    var post_files = document.getElementById("files-" + content_id.id_string);
    var post_contents = document.getElementById("post-contents-" + content_id.id_string);
    var thread_container = document.getElementById("thread-expand-" + "nci_" + content_id.thread_id + "_0_0");

    if (command === "hide-thread") {
        dataBin.hidden_threads[content_id.id_string] = Date.now();
    } else if (command === "show-thread") {    
        delete dataBin.hidden_threads[content_id.id_string];
    }

    if (thread_container !== null) {
        nelliel.ui.toggleHidden(thread_container);
    }
    
    if (post_files !== null) {
        nelliel.ui.toggleHidden(post_files);
    }
    
    if (post_contents !== null) {
        nelliel.ui.toggleHidden(post_contents);
    }

    nelliel.core.storeInLocalStorage(dataBin.hidden_threads_id, dataBin.hidden_threads);
    nelliel.ui.switchDataCommand(element, "hide-thread", "show-thread");
    nelliel.ui.swapContentAttribute(element, "data-alt-visual");
}

nelliel.ui.hideShowPost = function(element, command) {
    var content_id = nelliel.core.contentID(element.getAttribute("data-content-id"))
    var post_files = document.getElementById("files-" + content_id.id_string);
    var post_contents = document.getElementById("post-contents-" + content_id.id_string);
    
    if (command == "hide-post") {
        dataBin.hidden_posts[content_id.id_string] = Date.now();
    } else if (command == "show-post") {
        delete dataBin.hidden_posts[content_id.id_string];
    }
    
    if (post_files !== null) {
        nelliel.ui.toggleHidden(post_files);
    }
    
    if (post_contents !== null) {
        nelliel.ui.toggleHidden(post_contents);
    }
    
    nelliel.core.storeInLocalStorage(dataBin.hidden_posts_id, dataBin.hidden_posts);
    nelliel.ui.switchDataCommand(element, "hide-post", "show-post");
    nelliel.ui.swapContentAttribute(element, "data-alt-visual");
}

nelliel.ui.applyHidePostThread = function() {
    for (var id in dataBin.hidden_threads) {
        var thread_id = id.split('_')[0];
        var post_files = document.getElementById("files-" + id);
        var post_contents = document.getElementById("post-contents-" + id);
        var thread_container = document.getElementById("thread-expand-" + id);
        var element = document.getElementById("hide-thread-" + id);
        
        if (thread_container !== null) {
            nelliel.ui.toggleHidden(thread_container);
        }
        
        if (post_files !== null) {
            nelliel.ui.toggleHidden(post_files);
        }
        
        if (post_contents !== null) {
            nelliel.ui.toggleHidden(post_contents);
        }
        
        nelliel.ui.switchDataCommand(element, "hide-thread", "show-thread");
        nelliel.ui.swapContentAttribute(element, "data-alt-visual");
    }
    
    for (var id in dataBin.hidden_posts) {
        var post_files = document.getElementById("files-" + id);
        var post_contents = document.getElementById("post-contents-" + id);
        var element = document.getElementById("hide-post-" + id);

        if (post_files !== null) {
            nelliel.ui.toggleHidden(post_files);
        }
        
        if (post_contents !== null) {
            nelliel.ui.toggleHidden(post_contents);
        }
        
        nelliel.ui.switchDataCommand(element, "hide-post", "show-post");
        nelliel.ui.swapContentAttribute(element, "data-alt-visual");
    }
}

nelliel.ui.showHideFileMeta = function(element) {
    var content_id = nelliel.core.contentID(element.getAttribute("data-content-id"))
    var meta_element = document.getElementById("file-meta-" + content_id.id_string);
    nelliel.ui.swapContentAttribute(element, "data-alt-visual");
    nelliel.ui.toggleHidden(meta_element);
    nelliel.ui.switchDataCommand(element, "show-file-meta", "hide-file-meta");
}

nelliel.ui.expandCollapseThread = function(element, command) {
    var content_id = nelliel.core.contentID(element.getAttribute("data-content-id"));
    var target_element = document.getElementById("thread-expand-" + content_id.id_string);

    if (!target_element) {
        return;
    }

    var url = "threads/" + content_id.thread_id + "/thread-" + content_id.thread_id + "-" + command.split('-')[0] + ".html";
    var request = new XMLHttpRequest();
    request.open('GET', url);
    request.onload = function() {
        if (request.status === 200) {
            nelliel.ui.swapContentAttribute(element, "data-alt-visual");
            nelliel.ui.switchDataCommand(element, "expand-thread", "collapse-thread");
            target_element.innerHTML = request.responseText;
            nelliel.ui.applyHidePostThread();
        }
    };

    request.send();
}

nelliel.ui.highlightPost = function(content_id) {
    var post_container = document.getElementById("post-container-" + content_id.id_string);
    
    if (post_container !== null) {
        if(post_container.className.indexOf('post-hightlight') === -1) {
            post_container.className += " post-highlight";
        } else {
            post_container.className = post_container.className.replace(/\post-highlight\b/g, "");
        }
    }
}

nelliel.ui.inlineExpandReduce = function(element, command) {
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
    var href = element.getAttribute("href");
    var anchor_matches = href.match(/#t([0-9]+)p([0-9]+)/);
    var post_id = "nci_" + anchor_matches[1] + "_" + anchor_matches[2] + "_0";

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

    if (document.getElementById("post-container-" + post_id) !== null) {
        var quoted_post = document.getElementById("post-container-" + post_id).cloneNode(true);
        quoted_post.className = quoted_post.className.replace(/\op-post\b/g, "reply-post");
        quoted_post.className += " popup-mod";
        popup_div.appendChild(quoted_post);
        element.parentNode.insertBefore(popup_div, element);
        var popup_rect = quoted_post.getBoundingClientRect();
        nelliel.ui.addBoundingClientRectProperties(popup_rect);
        popup_div.style.left = (element_rect.right + offsetX + 10) + 'px';
        popup_div.style.top = ((element_rect.y + offsetY) - (popup_rect.height / 2)) + 'px';
    } else {
        var request = new XMLHttpRequest();
        request.open('GET', element.getAttribute("href"));
        request.responseType = "document";
        request.onload = function() {
            if (request.status === 200) {
                var quoted_post = request.response.getElementById("post-container-" + post_id);
                quoted_post.className = quoted_post.className.replace(/\op-post\b/g, "reply-post");
                quoted_post.className += " reply-post.popup-mod";
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
}

nelliel.ui.hideLinkedPost = function(element, event) {
    var href = element.getAttribute("href");
    var anchor_matches = href.match(/#t([0-9]+)p([0-9]+)/);
    var post_id = "nci_" + anchor_matches[1] + "_" + anchor_matches[2] + "_0";
    var target_popup = document.getElementById("post-quote-popup-" + post_id);

    if (target_popup !== null) {
        target_popup.remove();
    }
}

nelliel.ui.linkPost = function(element) {
    var content_id = nelliel.core.contentID(element.getAttribute("data-content-id"))
    document.postingform.wordswordswords.value = document.postingform.wordswordswords.value + '>>' + content_id.post_id + '\n';
}

nelliel.ui.toggleHidden = function(element) {
    if(element.className.search("hidden") === -1) {
        element.className += " hidden";
    } else {
        element.className = element.className.replace(/\bhidden\b/g, "");
    }
}

nelliel.ui.swapContentAttribute = function(element, attribute_name) {
    var inner = element.innerHTML;
    element.innerHTML = element.getAttribute(attribute_name);
    element.setAttribute(attribute_name, inner);
}

nelliel.ui.switchDataCommand = function(element, option_one, option_two) {
    var data_command = element.getAttribute("data-command");

    if (data_command.indexOf(option_one) > -1 ) {
        element.setAttribute("data-command", option_two);
    }
    else if (data_command.indexOf(option_two) > -1 ) {
        element.setAttribute("data-command", option_one);
    }
}

nelliel.ui.addBoundingClientRectProperties = function(bounding_rect) {
    bounding_rect.width = bounding_rect.width || bounding_rect.right - bounding_rect.left;
    bounding_rect.height = bounding_rect.height || bounding_rect.bottom - bounding_rect.top;
    bounding_rect.x = bounding_rect.x || bounding_rect.left + (bounding_rect.width / 2);
    bounding_rect.y = bounding_rect.y || bounding_rect.top + (bounding_rect.height / 2);
}