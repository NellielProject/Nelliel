function doImportantStuff(board_id) {
    setupListeners();
    fillForms(board_id);
    hashHandler();
}

function setupListeners() {
    var post_elements = document.getElementsByClassName('thread-corral');

    for (var i = 0; i < post_elements.length; i++) {
        addListenerIfElementExists(post_elements[i], "click", processPostClicks);
    }

    addListenerIfElementExists(document.getElementById("top-styles-div"), "click", processPostClicks);
    addListenerIfElementExists(document.getElementById("bottom-styles-div"), "click", processPostClicks);
    addListenerIfElementExists(document.getElementById("posting-form"), "click", processPostClicks);
    window.addEventListener("hashchange", hashHandler);
}

function addListenerIfElementExists(element, event, event_handler) {
    if (element !== null) {
        element.addEventListener(event, event_handler);
    }
}

function processPostClicks(event) {
    if (event.target.hasAttribute("data-command")) {
        if (event.target.hasAttribute("data-id")) {
            var id_set = event.target.getAttribute("data-id").split("_");
            var thread_id = id_set[0];
            var post_id = id_set[1];
            var file_id = id_set[2];
        }

        var command = event.target.getAttribute("data-command");
        if (command === "expand-thread") {
            expandCollapseThread(thread_id, "expand");
        } else if (command === "collapse-thread") {
            expandCollapseThread(thread_id, "collapse");
        } else if (command === "change-style") {
            changeBoardStyle(event.target.getAttribute("data-id"));
        } else if (command === "link-post") {
            linkPost(post_id);
        } else if (command === "show-file-meta" || command === "hide-file-meta") {
            showHideFileMeta(event.target, command);
        } else if (command === "add-file-meta") {
            addNewFileMeta(event.target, command);
        } else if (command === "inline-expand" || command === "inline-reduce") {
            inlineExpandReduce(event.target, command);
            event.preventDefault();
        }

        if (event.target.hasAttribute("href") && event.target.getAttribute("href").match(/^#$/) !== null) {
            event.preventDefault();
        }
    }
}

function setCookie(c_name, value, expiredays) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie = c_name + "=" + escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString())
            + ";path=/";
}

function processCookie(style_cookie) {
    var style = getCookie(style_cookie);

    with (document) {
        if (style != null) {
            changeBoardStyle(style);
        }
    }
}

function getCookie(key) {
    var csplit = document.cookie.split('; ');

    for (var i = 0; i < csplit.length; i++) {
        var s2 = csplit[i].split('=');
        if (s2[0] == key) {
            return s2[1];
        }
    }

    return null;
}

function hashHandler() {
    if (location.hash.match(/#p[0-9_]+/)) {
        highlightPost(location.hash.replace("#p", ""));
    }
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

function addNewFileMeta(element, command) {
    var target_id = element.id.replace("add-", "form-");
    var target_element = document.getElementById(target_id);
    target_element.className = target_element.className.replace(/\bhidden\b/g, "");
    element.className += " hidden";
}

function showHideFileMeta(element, command) {
    if (command === "show-file-meta") {
        var full_id = element.id.replace("show-file-meta-", "");
        var meta_element = document.getElementById("file-meta-" + full_id);
        var hide_meta_element = document.getElementById("hide-file-meta-" + full_id);
        element.parentNode.className += " hidden";
        hide_meta_element.parentNode.className = hide_meta_element.parentNode.className.replace(/\bhidden\b/g, "");
        meta_element.className = meta_element.className.replace(/\bhidden\b/g, "");
    }

    if (command === "hide-file-meta") {
        var full_id = element.id.replace("hide-file-meta-", "");
        var meta_element = document.getElementById("file-meta-" + full_id);
        var show_meta_element = document.getElementById("show-file-meta-" + full_id);
        element.parentNode.className += " hidden";
        show_meta_element.parentNode.className = show_meta_element.parentNode.className.replace(/\bhidden\b/g, "");
        meta_element.className += " hidden";
    }

}

function addMoarInput(inputId, hide) {
    document.getElementById(inputId).className = document.getElementById(inputId).className.replace(' none', '');

    if (hide) {
        document.getElementById('add' + inputId).style.display = 'none';
    }

}

function fillForms(board) {
    var P = getCookie("pwd-" + board);
    var N = getCookie("name-" + board);
    document.getElementById("posting-form-sekrit").value = P;
    document.getElementById("update-sekrit").value = P;
    document.getElementById("not-anonymous").value = N;
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

function addBanDetails(id, num, name, host) {
    var element = document.getElementById(id);
    if (!element) {
        return;
    }

    element.innerHTML = '<table>' + '<tr><td>B& from posting: <input type="checkbox" name="postban' + num + '" value='
            + num + '></td><td class="text-center">Days: <input type="text" name="timedays' + num
            + '" size="4" maxlength="4" value="3">' + ' &nbsp;&nbsp;&nbsp; Hours: <input type="text" name="timehours'
            + num + '" size="4" maxlength="4" value="0"></td></tr>'
            + '<tr><td>B& post message (optional): </td><td><input type="text" name="banmessage' + num
            + '" size="32" maxlength="32" value=""></td></tr>'
            + '<tr><td>B& reason (optional): </td><td><textarea name="banreason' + num
            + '" cols="32" rows="3"></textarea>' + '<input type="hidden" name="banname' + num + '" value="' + name
            + '"><input type="hidden" name="banhost' + num + '" value="' + host + '"></td></tr>' + '</table>';
}

function linkPost(num) {
    document.postingform.wordswordswords.value = document.postingform.wordswordswords.value + '>>' + num + '\n';
}

function changeBoardStyle(style) {
    var allstyles = document.getElementsByTagName("link");
    var style_board = "style-board";

    for (i = 0; i < allstyles.length; i++) {
        if (allstyles[i].getAttribute("data-id") == "style-board") {
            if (allstyles[i].title == style) {
                allstyles[i].disabled = false;
                style_board = allstyles[i].getAttribute("data-id");
            } else {
                allstyles[i].disabled = true;
            }
        }
    }

    setCookie(style_board, style, 9001);
}