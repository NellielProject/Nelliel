function hideShowThread(element, command) {
    var id = element.getAttribute("data-id");
    var thread_id = id.split('_')[0];
    var post_files = document.getElementById("post-files-container-" + id);
    var post_contents = document.getElementById("post-contents-" + id);
    var thread_container = document.getElementById("thread-expand-" + thread_id);
    var inner = element.innerHTML;
    
    if (command == "hide-thread") {
        if (thread_container !== null) {
            thread_container.className += " hidden";
        }
        
        if (post_files !== null) {
            post_files.className += " hidden";
        }
        
        if (post_contents !== null) {
            post_contents.className += " hidden";
        }

        dataBin.hidden_threads[thread_id] = Date.now();
        nelliel.core.storeInLocalStorage(dataBin.hidden_threads_id, dataBin.hidden_threads);
        element.innerHTML = element.getAttribute("data-alt-visual");
        element.setAttribute("data-alt-visual", inner);
        element.setAttribute("data-command", "show-thread");
    } else if (command == "show-thread") {
        if (thread_container !== null) {
            thread_container.className = thread_container.className.replace(/\hidden\b/g, "");
        }
        
        if (post_files !== null) {
            post_files.className = post_files.className.replace(/\hidden\b/g, "");
        }
        
        if (post_contents !== null) {
            post_contents.className = post_contents.className.replace(/\hidden\b/g, "");
        }
        
        delete dataBin.hidden_threads[thread_id];
        nelliel.core.storeInLocalStorage(dataBin.hidden_threads_id, dataBin.hidden_threads);
        element.innerHTML = element.getAttribute("data-alt-visual");
        element.setAttribute("data-alt-visual", inner);
        element.setAttribute("data-command", "hide-thread");
    }
}

function hideShowPost(element, command) {
    var id = element.getAttribute("data-id");
    var post_files = document.getElementById("post-files-container-" + id);
    var post_contents = document.getElementById("post-contents-" + id);
    var thread_container = document.getElementById("thread-expand-" + id);
    var inner = element.innerHTML;
    
    if (command == "hide-post") {
        if (post_files !== null) {
            post_files.className += " hidden";
        }
        
        if (post_contents !== null) {
            post_contents.className += " hidden";
        }

        dataBin.hidden_posts[id] = Date.now();
        nelliel.core.storeInLocalStorage(dataBin.hidden_posts_id, dataBin.hidden_posts);
        element.setAttribute("data-command", "show-post");
        element.innerHTML = element.getAttribute("data-alt-visual");
        element.setAttribute("data-alt-visual", inner);
    } else if (command == "show-post") {
        if (post_files !== null) {
            post_files.className = post_files.className.replace(/\hidden\b/g, "");
        }
        
        if (post_contents !== null) {
            post_contents.className = post_contents.className.replace(/\hidden\b/g, "");
        }

        delete dataBin.hidden_posts[id];
        nelliel.core.storeInLocalStorage(dataBin.hidden_posts_id, dataBin.hidden_posts);
        element.setAttribute("data-command", "hide-post");
        element.innerHTML = element.getAttribute("data-alt-visual");
        element.setAttribute("data-alt-visual", inner);
    }

}

function showHideFileMeta(element, command) {
    var full_id = element.getAttribute("data-id");
    var inner = element.innerHTML;
    var meta_element = document.getElementById("file-meta-" + full_id);
    element.innerHTML = element.getAttribute("data-alt-visual");
    element.setAttribute("data-alt-visual", inner);
    toggleHidden(meta_element);

    if (command === "show-file-meta") {
        element.setAttribute("data-command", "hide-file-meta");
    }

    if (command === "hide-file-meta") {
        element.setAttribute("data-command", "show-file-meta");
    }
}

function expandCollapseThread(thread_id, command) {
    var target_element = document.getElementById("thread-expand-" + thread_id);
    var expand_element = document.getElementById("expandLink" + thread_id);
    var collapse_element = document.getElementById("collapseLink" + thread_id);

    if (!target_element) {
        return;
    }

    var url = "threads/" + thread_id + "/" + thread_id + "-" + command + ".html";
    var request = new XMLHttpRequest();
    request.open('GET', url);
    request.onload = function() {
        if (request.status === 200) {
            if (command === "expand") {
                target_element.innerHTML = request.responseText;
                expand_element.parentNode.className += " hidden";
                collapse_element.parentNode.className = collapse_element.className.replace(/\bhidden\b/g, "");
            } else if (command === "collapse") {
                target_element.innerHTML = request.responseText;
                collapse_element.parentNode.className += " hidden";
                expand_element.parentNode.className = expand_element.className.replace(/\bhidden\b/g, "");
            }
        }
    };

    request.send();
}

function highlightPost(post_id) {
    var post_elements = document.getElementsByClassName("post-corral");
    var id_sub = post_id.split("_");
    var id_op = id_sub[0] == id_sub[1] ? true : false;

    for (i = 0; i < post_elements.length; i++) {
        var current_id = post_elements[i].id.replace("post-id-", "");
        var post_container = document.getElementById("post-container-" + current_id);

        if (post_elements[i].id == "post-id-" + post_id && !id_op) {
            post_container.className += " post-highlight";
        } else {
            post_container.className = post_container.className.replace(/\post-highlight\b/g, "");
        }
    }
}

function inlineExpandReduce(element, command) {
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

        if (command == "inline-expand") {
            element.setAttribute("data-command", "inline-reduce");
        } else if (command == "inline-reduce") {
            element.setAttribute("data-command", "inline-expand");
        }
    }
}

function showLinkedPost(element, event) {
    var href = element.getAttribute("href");
    var post_id = href.match(/#p([0-9_]+)/)[1];

    if (document.getElementById("post-quote-popup-" + post_id) !== null) {
        return;
    }

    var offsetY = window.pageYOffset || document.documentElement.scrollTop;
    var offsetX = window.pageXOffset || document.documentElement.scrollLeft;
    var popup_div = document.createElement("div");
    popup_div.id = "post-quote-popup-" + post_id;
    popup_div.setAttribute("class", "post-quote-popup");
    var element_rect = element.getBoundingClientRect();
    addBoundingClientRectProperties(element_rect);

    if (document.getElementById("post-container-" + post_id) !== null) {
        var quoted_post = document.getElementById("post-container-" + post_id).cloneNode(true);
        quoted_post.className = quoted_post.className.replace(/\op-post\b/g, "reply-post");
        quoted_post.className += " popup-mod";
        popup_div.appendChild(quoted_post);
        element.parentNode.insertBefore(popup_div, element);
        var popup_rect = quoted_post.getBoundingClientRect();
        addBoundingClientRectProperties(popup_rect);
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
                quoted_post.className += " popup-mod";
                popup_div.appendChild(quoted_post);
                element.parentNode.insertBefore(popup_div, element);
                var popup_rect = quoted_post.getBoundingClientRect();
                addBoundingClientRectProperties(popup_rect);
                popup_div.style.left = (element_rect.right + offsetX + 5) + 'px';
                popup_div.style.top = ((element_rect.y + offsetY) - (popup_rect.height / 2)) + 'px';
            }
        };
        request.send();
    }
}

function hideLinkedPost(element, event) {
    var href = element.getAttribute("href");
    var post_id = href.match(/#p([0-9_]+)/)[1];
    var target_popup = document.getElementById("post-quote-popup-" + post_id);

    if (target_popup !== null) {
        target_popup.remove();
    }
}

function linkPost(num) {
    document.postingform.wordswordswords.value = document.postingform.wordswordswords.value + '>>' + num + '\n';
}

function toggleHidden(element) {
    if(element.className.search("hidden") === -1) {
        element.className += " hidden";
    } else {
        element.className = element.className.replace(/\bhidden\b/g, "");
    }
}

function addBoundingClientRectProperties(bounding_rect) {
    bounding_rect.width = bounding_rect.width || bounding_rect.right - bounding_rect.left;
    bounding_rect.height = bounding_rect.height || bounding_rect.bottom - bounding_rect.top;
    bounding_rect.x = bounding_rect.x || bounding_rect.left + (bounding_rect.width / 2);
    bounding_rect.y = bounding_rect.y || bounding_rect.top + (bounding_rect.height / 2);
}