var nelliel = {};
nelliel.setup = {};
nelliel.core = {};
nelliel.posting_form = {};
nelliel.ui = {};


function dataBin() {
    ;
}

nelliel.setup.doImportantStuff = function(board_id) {
    dataBin.board_id = board_id;
    dataBin.hidden_threads_id = "hidden_threads_" + board_id;
    dataBin.hidden_posts_id = "hidden_posts_" + board_id;
    nelliel.setup.localStorageInitCheck();
    dataBin.hidden_threads = nelliel.core.retrieveFromLocalStorage(dataBin.hidden_threads_id, true);
    dataBin.hidden_posts = nelliel.core.retrieveFromLocalStorage(dataBin.hidden_posts_id, true);
    nelliel.setup.setupListeners();
    nelliel.core.hashHandler();
    
    if(board_id !== "") {
        nelliel.setup.fillForms(board_id);
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
        nelliel.setup.addListenerIfElementExists(post_elements[i], "click", nelliel.core.processPostClicks);
        nelliel.setup.addListenerIfElementExists(post_elements[i], "mouseover", nelliel.core.processMouseOver);
        nelliel.setup.addListenerIfElementExists(post_elements[i], "mouseout", nelliel.core.processMouseOut);
    }

    nelliel.setup.addListenerIfElementExists(document.getElementById("top-styles-div"), "click", nelliel.core.processPostClicks);
    nelliel.setup.addListenerIfElementExists(document.getElementById("bottom-styles-div"), "click", nelliel.core.processPostClicks);
    nelliel.setup.addListenerIfElementExists(document.getElementById("posting-form"), "click", nelliel.core.processPostClicks);
    nelliel.setup.addListenerIfElementExists(document.getElementById("posting-form"), "change", nelliel.core.processChanges);
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

nelliel.core.processPostClicks = function(event) {
    if (event.target.hasAttribute("data-command")) {
        if (event.target.hasAttribute("data-id")) {
            var id_set = event.target.getAttribute("data-id").split("_");
            var thread_id = id_set[0];
            var post_id = id_set[1];
            var file_id = id_set[2];
        }

        var command = event.target.getAttribute("data-command");
        if (command === "expand-thread" || command === "collapse-thread") {
            expandCollapseThread(thread_id, command, event.target);
        } else if (command === "change-style") {
            changeBoardStyle(dataBin.board_id, event.target.getAttribute("data-id"));
        } else if (command === "link-post") {
            nelliel.ui.linkPost(post_id);
        } else if (command === "show-file-meta" || command === "hide-file-meta") {
            showHideFileMeta(event.target, command);
        } else if (command === "add-file-meta") {
            addNewFileMeta(event.target, command);
        } else if (command === "inline-expand" || command === "inline-reduce") {
            inlineExpandReduce(event.target, command);
            event.preventDefault();
        } else if (command === "hide-post" || command === "show-post" ) {
            hideShowPost(event.target, command);
        } else if (command === "hide-thread" || command === "show-thread" ) {
            hideShowThread(event.target, command);
        }

        if (event.target.hasAttribute("href") && event.target.getAttribute("href").match(/^#$/) !== null) {
            event.preventDefault();
        }
    }
}

nelliel.core.processMouseOver = function(event) {
    if (event.target.hasAttribute("data-command")) {
        var command = event.target.getAttribute("data-command");

        if (command === "show-linked-post") {
            nelliel.ui.showLinkedPost(event.target, event);
        }
    }
}

nelliel.core.processMouseOut = function(event) {
    if (event.target.hasAttribute("data-command")) {
        var command = event.target.getAttribute("data-command");

        if (command === "show-linked-post") {
            nelliel.ui.hideLinkedPost(event.target, event);
        }
    }
}

nelliel.core.processChanges = function(event) {
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
    if (location.hash.match(/#p[0-9_]+/)) {
        highlightPost(location.hash.replace("#p", ""));
    }
}

function addNewFileMeta(element, command) {
    var target_id = element.id.replace("add-", "form-");
    var target_element = document.getElementById(target_id);
    target_element.className = target_element.className.replace(/\bhidden\b/g, "");
    element.className += " hidden";
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

function setStyle(style) {
    if(style == null) {
        return;
    }

    var allstyles = document.getElementsByTagName("link");

    for (i = 0; i < allstyles.length; i++) {
        allstyles[i].disabled = true;
        
        if (allstyles[i].getAttribute("data-id") == "style-board") {
            if (allstyles[i].title == style) {
                allstyles[i].disabled = false;
            }
        }
    }
}

function changeBoardStyle(board_id, style) {
    if(style == null) {
        return;
    }
    
    var allstyles = document.getElementsByTagName("link");
    
    if(board_id == "") {
        style_cookie = "base-style";
    } else {
        style_cookie = "style-" + board_id;
    }
    
    setStyle(style);
    nelliel.core.setCookie(style_cookie, style, 9001);
}