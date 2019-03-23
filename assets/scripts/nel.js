var nelliel = {};
nelliel.setup = {};
nelliel.core = {};
nelliel.events = {};
nelliel.posting_form = {};
nelliel.ui = {};

function dataBin() {
    ;
}

nelliel.setup.doImportantStuff = function(board_id, is_modmode) {
    dataBin.board_id = board_id;
    dataBin.is_modmode = is_modmode;
    dataBin.hidden_threads_id = "hidden_threads_" + board_id;
    dataBin.hidden_posts_id = "hidden_posts_" + board_id;
    nelliel.setup.localStorageInitCheck();
    dataBin.hidden_threads = nelliel.core.retrieveFromLocalStorage(dataBin.hidden_threads_id, true);
    dataBin.hidden_posts = nelliel.core.retrieveFromLocalStorage(dataBin.hidden_posts_id, true);

    if(board_id === "") {
        setStyle(nelliel.core.getCookie("base-style"));
    }
    else {
        setStyle(nelliel.core.getCookie("style-" + board_id));
    }

    nelliel.setup.setupListeners();
    nelliel.core.hashHandler();
    
    if(board_id !== "") {
        nelliel.setup.fillForms(board_id);
        nelliel.ui.applyHidePostThread();
    }
}

nelliel.setup.localStorageInitCheck = function() {
    if(!localStorage[dataBin.hidden_threads_id]) {
        localStorage[dataBin.hidden_threads_id] = '{}';
    }
    
    if(!localStorage[dataBin.hidden_posts_id]) {
        localStorage[dataBin.hidden_posts_id] = '{}';
    }
}

nelliel.setup.setupListeners = function() {
    var post_elements = document.getElementsByClassName('thread-corral');

    for (var i = 0; i < post_elements.length; i++) {
        nelliel.setup.addListenerIfElementExists(post_elements[i], "click", nelliel.events.processPostClicks);
        nelliel.setup.addListenerIfElementExists(post_elements[i], "mouseover", nelliel.events.processMouseOver);
        nelliel.setup.addListenerIfElementExists(post_elements[i], "mouseout", nelliel.events.processMouseOut);
    }

    nelliel.setup.addListenerIfElementExists(document.getElementById("top-styles-menu"), "change", nelliel.events.processPostClicks);
    nelliel.setup.addListenerIfElementExists(document.getElementById("bottom-styles-menu"), "change", nelliel.events.processPostClicks);
    nelliel.setup.addListenerIfElementExists(document.getElementById("posting-form"), "click", nelliel.events.processPostClicks);
    nelliel.setup.addListenerIfElementExists(document.getElementById("posting-form"), "change", nelliel.events.processChanges);
    window.addEventListener("hashchange", nelliel.hashHandler);
}

nelliel.setup.addListenerIfElementExists = function(element, event, event_handler) {
    if (element !== null) {
        element.addEventListener(event, event_handler);
    }
}

nelliel.setup.fillForms = function(board) {
    var P = nelliel.core.getCookie("pwd-" + board);
    var N = nelliel.core.getCookie("name-" + board);
    document.getElementById("posting-form-sekrit").value = P;
    document.getElementById("update-sekrit").value = P;
    document.getElementById("not-anonymous").value = N;
}

nelliel.events.processPostClicks = function(event) {
    if (event.target.hasAttribute("data-command")) {
        if (event.target.hasAttribute("data-content-id")) {
            var content_id = nelliel.core.contentID(event.target.getAttribute("data-content-id"))
        }

        var command = event.target.getAttribute("data-command");

        if (command === "expand-thread" || command === "collapse-thread") {
            nelliel.ui.expandCollapseThread(event.target, command);
        } else if (command === "expand-thread-render" || command === "collapse-thread-render") {
            nelliel.ui.expandCollapseThread(event.target, command, true);
        } else if (command === "change-style") {
            setStyle(event.target.value, true);
        } else if (command === "link-post") {
            nelliel.ui.linkPost(event.target);
        } else if (command === "show-file-meta" || command === "hide-file-meta") {
            nelliel.ui.showHideFileMeta(event.target);
        } else if (command === "add-file-meta") {
            addNewFileMeta(event.target, command);
        } else if (command === "inline-expand" || command === "inline-reduce") {
            nelliel.ui.inlineExpandReduce(event.target, command);
            event.preventDefault();
        } else if (command === "hide-post" || command === "show-post" ) {
            nelliel.ui.hideShowPost(event.target, command);
        } else if (command === "hide-thread" || command === "show-thread" ) {
            nelliel.ui.hideShowThread(event.target, command);
        }

        if (event.target.hasAttribute("href") && event.target.getAttribute("href").match(/^#$/) !== null) {
            event.preventDefault();
        }
    }
}

nelliel.events.processMouseOver = function(event) {
    if (event.target.hasAttribute("data-command")) {
        var command = event.target.getAttribute("data-command");

        if (command === "show-linked-post") {
            nelliel.ui.showLinkedPost(event.target, event);
        }
    }
}

nelliel.events.processMouseOut = function(event) {
    if (event.target.hasAttribute("data-command")) {
        var command = event.target.getAttribute("data-command");

        if (command === "show-linked-post") {
            nelliel.ui.hideLinkedPost(event.target, event);
        }
    }
}

nelliel.events.processChanges = function(event) {
    if (event.target.hasAttribute("data-command")) {
        var command = event.target.getAttribute("data-command");

        if (command === "reveal-file-input") {
            nelliel.posting_form.showNextFileInput(event.target);
        }
    }
}

nelliel.core.setCookie = function(c_name, value, expiredays) {
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + expiredays);
    document.cookie = c_name + "=" + escape(value) + ((expiredays == null) ? "" : ";expires=" + exdate.toGMTString())
            + ";path=/";
}

nelliel.core.getCookie = function(key) {
    var csplit = document.cookie.split('; ');

    for (var i = 0; i < csplit.length; i++) {
        var s2 = csplit[i].split('=');
        if (s2[0] == key) {
            return s2[1];
        }
    }

    return null;
}

nelliel.core.storeInLocalStorage = function(data_path, data) {
    data = (typeof data !== "string") ? JSON.stringify(data) : data;
    localStorage[data_path] = data;
}

nelliel.core.storeInSessionStorage = function(data_path, data) {
    data = (typeof data !== "string") ? JSON.stringify(data) : data;
    localStorage[data_path] = data;
}

nelliel.core.retrieveFromLocalStorage = function(data_path, parse) {
    parse = (typeof parse === "undefined") ? false : parse;
    data = localStorage[data_path];
    data = (typeof data !== "undefined" && parse === true) ? JSON.parse(data) : data;
    return data;
}

nelliel.core.retrieveFromSessionStorage = function(data_path, parse) {
    parse = (typeof parse === "undefined") ? false : parse;
    data = sessionStorage[data_path];
    data = (typeof data !== "undefined" && parse === true) ? JSON.parse(data) : data;
    return data;
}

nelliel.posting_form.showNextFileInput = function (element) {
    var file_num = element.id.replace("up-file-", "");
    file_num++;
    var next_file = document.getElementById("form-file-" + file_num);
    next_file.className = next_file.className.replace(/\bhidden\b/g, "");
}

nelliel.core.hashHandler = function () {
    var hash_match = location.hash.match(/#t([0-9]+)p([0-9]+)/);
    
    if (hash_match !== null) {
        var content_id = nelliel.core.contentID('cid_' + hash_match[1] + '_' + hash_match[2] + '_0');
        nelliel.ui.highlightPost(content_id);
    }
}

nelliel.core.getElementsByAttributeName = function (attribute_name, element) {
    return element.querySelectorAll("[" + attribute_name + "]");
}

nelliel.core.contentID = function (id_string) {
    var content_id = {};
    var segments = id_string.split('_');
    content_id.id_string = id_string;
    content_id.thread_id = segments[1];
    content_id.post_id = segments[2];
    content_id.content_order = segments[3];
    return content_id;
}

function addNewFileMeta(element, command) {
    var target_id = element.id.replace("add-", "form-");
    var target_element = document.getElementById(target_id);
    target_element.className = target_element.className.replace(/\bhidden\b/g, "");
    element.className += " hidden";
}

function setStyle(style, update_cookie = false) {
    if(style == null) {
        return;
    }
    
    if(update_cookie) {
        if(dataBin.board_id == "") {
            style_cookie = "base-style";
        } else {
            style_cookie = "style-" + dataBin.board_id;
        }
        
        nelliel.core.setCookie(style_cookie, style, 9001);
    }


    var allstyles = document.getElementsByTagName("link");

    for (i = 0; i < allstyles.length; i++) {
        allstyles[i].disabled = true;

        if (allstyles[i].getAttribute("data-style-type") == "style-board") {
            if (allstyles[i].getAttribute("data-id") == style) {
                allstyles[i].disabled = false;
            }
        }
    }
    
    var top_element = document.getElementById("top-styles-menu");
    var bottom_element = document.getElementById("bottom-styles-menu");
    
    if(top_element !== null) {
        top_element.value = style;
    }
    
    if(bottom_element !== null) {
        bottom_element.value = style;
    }
}
