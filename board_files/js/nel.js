function doImportantStuff(board_id) {
    setupListeners();
    fillForms(board_id);
}

function setupListeners() {
    var post_elements = document.getElementsByClassName('post-corral');

    for (var i = 0; i < post_elements.length; i++) {
        addListenerIfElementExists(post_elements[i], "click", processPostClicks);
    }
    
    addListenerIfElementExists(document.getElementById("top-styles-div"), "click", processPostClicks);
    addListenerIfElementExists(document.getElementById("bottom-styles-div"), "click", processPostClicks);
    addListenerIfElementExists(document.getElementById("posting-form"), "click", processPostClicks);
}

function addListenerIfElementExists(element, event, event_handler)
{
    if(element !== null)
    {
        element.addEventListener(event, event_handler);
    }
}

function processPostClicks(event) {
    if (event.target.hasAttribute("data-command")) {
        var id1 = this.id.replace("post-id-", "");
        var thread_id = id1.split("_")[0];
        var post_id = id1.split("_")[1];
        var command = event.target.getAttribute("data-command");

        if (command === "expand-thread") {
            expandCollapseThread(thread_id, "expand");
        } else if (command === "collapse-thread") {
            expandCollapseThread(thread_id, "collapse");
        } else if (command === "change-style") {
            changeBoardStyle(event.target.getAttribute("data-id"));
        } else if (command === "post-quote") {
            postQuote(post_id);
        } else if (command === "show-file-meta" || command === "hide-file-meta") {
            showHideFileMeta(event.target, command);
        } else if (command === "add-file-meta") {
            addNewFileMeta(event.target, command);
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

function addNewFileMeta(element, command)
{
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

function postQuote(num) {
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