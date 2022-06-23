var nelliel = {};
nelliel.setup = {};
nelliel.core = {};
nelliel.events = {};
nelliel.new_post_form = {};
nelliel.ui = {};

function dataBin() {
    ;
}

nelliel.setup.infoTransfer = function(info) {
    dataBin.board_id = info.domain_id;
    dataBin.src_directory = info.src_directory;
    dataBin.preview_directory = info.preview_directory;
    dataBin.page_directory = info.page_directory;
    dataBin.is_modmode = info.is_modmode;
}

nelliel.setup.doImportantStuff = function() {
    dataBin.style_override = nelliel.core.retrieveFromLocalStorage("style_override", false);
    setStyle(dataBin.style_override);
    dataBin.hidden_threads_id = "hidden_threads_" + dataBin.board_id;
    dataBin.hidden_posts_id = "hidden_posts_" + dataBin.board_id;
    dataBin.hidden_files_id = "hidden_files_" + dataBin.board_id;
    dataBin.hidden_embeds_id = "hidden_embeds_" + dataBin.board_id;
    nelliel.setup.localStorageInitCheck();
    dataBin.hidden_threads = nelliel.core.retrieveFromLocalStorage(dataBin.hidden_threads_id, true);
    dataBin.hidden_posts = nelliel.core.retrieveFromLocalStorage(dataBin.hidden_posts_id, true);
    dataBin.hidden_files = nelliel.core.retrieveFromLocalStorage(dataBin.hidden_files_id, true);
    dataBin.hidden_embeds = nelliel.core.retrieveFromLocalStorage(dataBin.hidden_embeds_id, true);
    dataBin.collapsedThreads = [];
    nelliel.setup.setupListeners();
    nelliel.core.hashHandler(null);
    
    if (dataBin.board_id !== "") {
        nelliel.setup.fillForms(dataBin.board_id);
        nelliel.ui.applyHideContent();
    }

    nelliel.core.unhideJSonly(document);
}

nelliel.setup.localStorageInitCheck = function() {
    if (!localStorage[dataBin.hidden_threads_id]) {
        localStorage[dataBin.hidden_threads_id] = '{}';
    }
    
    if (!localStorage[dataBin.hidden_posts_id]) {
        localStorage[dataBin.hidden_posts_id] = '{}';
    }
    
    if (!localStorage[dataBin.hidden_files_id]) {
        localStorage[dataBin.hidden_files_id] = '{}';
    }
    
    if (!localStorage[dataBin.hidden_embeds_id]) {
        localStorage[dataBin.hidden_embeds_id] = '{}';
    }
}

nelliel.setup.setupListeners = function() {
    var jsevents_elements = document.querySelectorAll("[data-jsevents]");
    var elements_length = jsevents_elements.length;

    for (var i = 0; i < elements_length; i++) {
        var events_list = jsevents_elements[i].getAttribute("data-jsevents").split("|");
        var list_length = events_list.length;

        for (var j = 0; j < list_length; j++) {
            if (events_list[j].includes("click")) {
                nelliel.setup.addListenerIfElementExists(jsevents_elements[i], "click", nelliel.events.processPostClick);
            }

            if (events_list[j].includes("mouseover")) {
                nelliel.setup.addListenerIfElementExists(jsevents_elements[i], "mouseover", nelliel.events.processMouseOver);
            }

            if (events_list[j].includes("mouseout")) {
                nelliel.setup.addListenerIfElementExists(jsevents_elements[i], "mouseout", nelliel.events.processMouseOut);
            }

            if (events_list[j].includes("change")) {
                nelliel.setup.addListenerIfElementExists(jsevents_elements[i], "change", nelliel.events.processChange);
            }
            
            if (events_list[j].includes("input")) {
                nelliel.setup.addListenerIfElementExists(jsevents_elements[i], "input", nelliel.events.processInput);
            }
        }
    }

    window.addEventListener("hashchange", nelliel.events.processHashchange);
}

nelliel.setup.addListenerIfElementExists = function(element, event, event_handler) {
    if (element !== null) {
        element.addEventListener(event, event_handler);
    }
}

nelliel.setup.fillForms = function(board) {
    var post_password = nelliel.core.retrieveFromLocalStorage("post_password", false);

    if (post_password !== null) {
        if (document.getElementById("new-post-form-sekrit") !== null) {
            document.getElementById("new-post-form-sekrit").value = post_password;
        }

        if (document.getElementById("update-sekrit") !== null) {
            document.getElementById("update-sekrit").value = post_password;
        }
    }
}

nelliel.events.processPostClick = function(event) {
    if (event.target.hasAttribute("data-command")) {
        if (event.target.hasAttribute("data-content-id")) {
            var content_id = nelliel.core.contentID(event.target.getAttribute("data-content-id"))
        }

        var command = event.target.getAttribute("data-command");

        if (command === "expand-thread" || command === "collapse-thread") {
            nelliel.ui.expandCollapseThread(event.target, command);
        } else if (command === "expand-thread-render" || command === "collapse-thread-render") {
            nelliel.ui.expandCollapseThread(event.target, command, true);
        } else if (command === "cite-post") {
            nelliel.ui.citePost(nelliel.core.contentID(event.target.getAttribute("data-content-id")));
        } else if (command === "show-file-meta" || command === "hide-file-meta") {
            nelliel.ui.showHideFileMeta(event.target);
        } else if (command === "add-file-meta") {
            addNewFileMeta(event.target, command);
        } else if (command === "inline-expand") {
            nelliel.ui.inlineExpand(event.target, command);
            event.preventDefault();
        } else if (command === "inline-reduce") {
            nelliel.ui.inlineReduce(event.target, command);
            event.preventDefault();
        } else if (command === "hide-thread" || command === "show-thread" ) {
            nelliel.ui.hideShowThread(event.target, command, content_id);
        } else if (command === "hide-post" || command === "show-post" ) {
            nelliel.ui.hideShowPost(event.target, command, content_id);
        } else if (command === "hide-file" || command === "show-file" ) {
            nelliel.ui.hideShowFile(event.target, command, content_id);
        } else if (command === "hide-embed" || command === "show-embed" ) {
            nelliel.ui.hideShowEmbed(event.target, command, content_id);
        } else if (command === "reload-captcha" ) {
            reloadCAPTCHA(event.target, command);
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

nelliel.events.processChange = function(event) {
	if (event.target.hasAttribute("data-command")) {
        var command = event.target.getAttribute("data-command");

        if (command === "reveal-file-input") {
            nelliel.new_post_form.showNextFileInput(event.target);
        } else if (command === "change-style") {
            setStyle(event.target.value, true);
        }
    }
}

nelliel.events.processInput = function(event) {
	if (event.target.id === "new-post-form-sekrit") {
		updatePostPassword(event.target.value);
	}

    if (event.target.hasAttribute("data-command")) {
        var command = event.target.getAttribute("data-command");
    }
}

nelliel.events.processHashchange = function(event) {
	nelliel.core.hashHandler(event);
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

nelliel.new_post_form.showNextFileInput = function (element) {
    var file_num = element.id.replace("up-file-", "");
    file_num++;
    var next_file = document.getElementById("form-file-" + file_num);
    next_file.className = next_file.className.replace(/\bhidden\b/g, "");
}

nelliel.core.hashHandler = function (event) {
    var post_anchor_match = location.hash.match(/#t([0-9]+)p([0-9]+)/);
    var cite_match = location.hash.match(/cite/);

    if (post_anchor_match != null) {
        var content_id = nelliel.core.contentID('cid_' + post_anchor_match[1] + '_' + post_anchor_match[2] + '_0');
        
        if (cite_match != null) {
        	nelliel.ui.citePost(content_id);
        	return;
        }

        if (event != null) {
            var last_anchor_match = event.oldURL.match(/#t([0-9]+)p([0-9]+)/);
            var last_content_id = nelliel.core.contentID('cid_' + last_anchor_match[1] + '_' + last_anchor_match[2] + '_0');
            nelliel.ui.unhighlightPost(last_content_id);
        }

        if (content_id.post_id != content_id.thread_id) {
            nelliel.ui.highlightPost(content_id);
        }
    }
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

nelliel.core.unhideJSonly = function (element) {
    var elements = element.querySelectorAll("[data-jsonly]");
    var element_count = elements.length;
    
    for (i = 0; i < element_count; i++) {
        nelliel.ui.toggleHidden(elements[i]);
    }
}

function updatePostPassword(new_password) {
    nelliel.core.storeInLocalStorage("post_password", new_password);
}

function addNewFileMeta(element, command) {
    var target_id = element.id.replace("add-", "form-");
    var target_element = document.getElementById(target_id);
    target_element.className = target_element.className.replace(/\bhidden\b/g, "");
    element.className += " hidden";
}

function setStyle(style, update = false) {
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

function reloadCAPTCHA(event_target, command) {
    var regen_url = event_target.getAttribute("data-url");
    var request = new XMLHttpRequest();
    request.open('GET', regen_url);
    request.onreadystatechange = function() {
        if (request.readyState == 4) {
            var captchas = document.getElementsByClassName("captcha-image", document);
            var captcha_count = captchas.length;

            for (i = 0; i < captcha_count; i++) {
                var original_url = captchas[i].getAttribute("src");
                
                if (original_url.includes("&time=")) {
                    var new_image_url = captchas[i].getAttribute("src").replace(/&time=[0-9]*/, "&time=" + Date.now());
                } else {
                    var new_image_url = captchas[i].getAttribute("src") + "&time=" + Date.now();
                }

                captchas[i].setAttribute("src", new_image_url);
            }
        }
    };
    
    request.send();
}
