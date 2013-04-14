/**
 * Global variables
 */

URL = "http://" + window.location.host + window.location.pathname;
TERM = null;
STARTTIME = null;
HISTORY = [];
LOADING_NOTES = (window.innerWidth >= 1166) ? '' : 'Your screen is smaller than 1166 pixels wide. Some pages may not render correctly.'
EDITOR = null;

/**
 * Array holding a list of scripts to dynamically load
 * @type {Array}
 */
var scripts = [
    'http://rawgithub.com/ajaxorg/ace-builds/master/src-noconflict/ace.js',
    'https://raw.github.com/jcubic/jquery.terminal/master/js/jquery.terminal-min.js',
    'http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/jquery-ui.min.js',
    'http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js',
    'https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js'
];

/**
 * Array holding a list of stylesheets to dynamically load
 * @type {Array}
 */
var styles = [
    'https://raw.github.com/jcubic/jquery.terminal/master/css/jquery.terminal.css',
    'https://raw.github.com/FortAwesome/Font-Awesome/master/css/font-awesome.min.css',
    'http://fonts.googleapis.com/css?family=Open+Sans:300'
];

/**
 * Dynamically loads a JavaScript file.
 * @param {String} url The URL of the file to load
 * @param {Function} callback A function to be called when the file has been loaded
 * @return {null}
 */
function loadScript(url, callback){
    console.log('Loading ' + url)
    var script = document.createElement('script');
    script.setAttribute('type', 'text/javascript');
    script.setAttribute('src', url);
    script.onload = callback;
    document.getElementsByTagName('head')[0].appendChild(script);
    return;
}

/**
 * Dynamically loads a stylesheet
 * @param {String} url The URL of the file to load
 * @param {Function} callback A function to be called when the file has been loaded
 * @return {null}
 */
function loadStyle(url, callback) {
    console.log('Loading ' + url);
    var style = document.createElement('link');
    style.setAttribute('rel', 'stylesheet');
    style.setAttribute('type', 'text/css');
    style.setAttribute('href', url);
    style.onload = callback;
    document.getElementsByTagName('head')[0].appendChild(style);
    return;
}

/**
 * Dynamically load all required resources
 * @return {null}
 */
function loadResources() {
    // Check if we're done
    if (styles.length == 0 && scripts.length == 0) {
        document.getElementById('loading-text').innerHTML = 'Done!<br /><small>' + LOADING_NOTES + '</small>';
        setTimeout(ready, 1000);
        return;
    }
    var url;
    // Load stylesheets first
    if (styles.length > 0) {
        url = styles.pop();
        document.getElementById('loading-text').innerHTML = 
            'Loading ' + url + '...<br /><small>' + LOADING_NOTES + '</small>';
        loadStyle(url, loadResources);
        return;
    }
    // Load scripts
    if (scripts.length > 0) {
        url = scripts.pop();
        document.getElementById('loading-text').innerHTML = 
            'Loading ' + url + '...<br /><small>' + LOADING_NOTES + '</small>'
        loadScript(url, loadResources);
        return;
    }
    return;
}

/**
 * Function to be called on the window.onload event
 * @return {null}
 */
function windowLoad(){
    loadResources();
}

/**
 * Anonymous function to be called immediately
 */
(function(){
    STARTTIME = new Date();
    // Load all our resources
    window.onload = windowLoad;
})();

/**
 * Function to be called when all resources have finished loading
 * @return {null}
 */
function ready() {
    /* Set up version info */
    $("#xshell-version").html(VERSION);
    $("#xshell-version").get(0).href="http://www.quetuo.net/xshell?" + VERSION;
    /* Get rid of loading screen */
    $("#div-loading").hide("slide", { direction: "right" }, 200);//.slideUp();
    $("header div a.icon").click(function() {
        go_back();
    });
    /* Set up sidebar */
    $("#sidebar li.li-shortcut").each(function(index) {
        /* Change classes depending on active or not */
        if (this.dataset.active == "1") {
            this.className="sidebar-li-active";
        } else if (this.dataset.active == "0") {
            this.className="sidebar-li-inactive";
        }
        /* Add onClick handler */
        $(this).click(function() {
            go_to(this.dataset.link);
        });
    });
    go_to("dashboard");
    var hash = window.location.hash;
    /* Check if a sidebar location is already specified in URL */
    $("#sidebar li").each(function(index) {
        if (("#" + this.dataset.link) == hash) {
            go_to(this.dataset.link);
        }
    });
    var endtime = new Date();
    xshell_log("Loaded in " + (endtime - STARTTIME) + "ms");
 }
 
function wait(on) {
    if (on) {
        document.body.style.cursor = 'wait';
    } else {
        document.body.style.cursor = 'auto';
    }
}

function dashboard_activate() {
    console.log("dashboard_activate");
    wait(true);
    $("#tile-files").click(function(){
        go_to("file-system");
    });
    $.getJSON(URL + "?ajax=info", function(data) {
        try {
            $("#p-software").html(data.server_software.replace("Apache", "<strong>Apache</strong>") + "<br />" + data.php_uname.replace("Linux", "<strong>Linux</strong>").replace("Unix", "<strong>Unix</strong>").replace("Windows", "<strong>Windows</strong>"));
            $("#p-environment").html("User: " + data.user + "<br />Root: " + data.document_root + "<br />File: " + data.filename);
            for (var i = 0; i < data.services.length; i++) {
                $("#p-services").html($("#p-services").html() + data.services[i] + "<br />");
            }
        } catch (e) {error_handler(e);}
        wait(false);
    });
}

function information_activate() {
    wait(true);
    $.getJSON(URL + "?ajax=info", function(data) {
        xshell_log("Received information data from server");
        try {
            $("#span-filename").html(data.filename);
            $("#span-php-version").html(data.php_version);
            $("#span-system").html(data.system);
            $("#span-user").html(data.user);
            $("#span-extensions").html("");
            for (var i = 0; i < data.extensions.length; i ++) {
                $("#span-extensions").html($("#span-extensions").html() + data.extensions[i] + (i == data.extensions.length - 1 ? "" : ", "));
            }
            $("#span-ps").html(data.ps);
            $("#span-httpd-root").html(data.httpd_root);
            for (var key in data.config) {
                $("#table-php-config").append("<tr><td>" + key + "</td><td>" + data.config[key] + "</td></tr>");
            }
            $("#pre-server-config").html(data.server_config);
        } catch (e) {error_handler(e);}
        wait(false);
    });
}

function filesystem_navigate(dir) {
    console.log("Navigating to " + dir + " (Current dir is " + $(".file-browser").get(0).dataset.dir + ")");
    wait(true);
    $.getJSON(URL + "?ajax=dir&dir=" + $(".file-browser").get(0).dataset.dir + "&go=" + dir, function(data) {
        xshell_log("Received filesystem data from server for " + data.dir);
        try {
            $(".file-browser").get(0).dataset.dir = data.dir;
            $("#page-file-system h3 small").html(data.dir);
            /* Clear file browser */
            $(".file-browser ul").empty();
            for (var i = 0; i < data.dirs.length; i ++) {
                $(".file-browser ul").append('<li class="file-browser file-directory" data-dir="' + data.dirs[i] + '"><h3>' + data.dirs[i] + '</h3><p>Directory</p></li>');
                $(".file-browser ul li").last().click(function(){
                    filesystem_navigate(this.dataset.dir);
                });
            }
            for (var i = 0; i < data.files.length; i ++) {
                var ft = 'misc';
                var dot = data.files[i].indexOf(".");
                var ext = (dot == -1 ? data.files[i] : data.files[i].substr(dot)).toLowerCase();
                if (ext == ".php") {ft = "php";}
                if (ext == ".html" || ext == ".htm") {ft = "html";}
                if (ext == ".jpg" || ext == ".png" || ext == ".bmp") {ft = "image";}
                $(".file-browser ul").append('<li class="file-browser file-' + ft + '" data-dir="' + data.files[i] + '" data-ft="' + ft + '"><h3>' + data.files[i] + '</h3><p>' + (ft == "misc" ? ext : ft) + ' file</p></li>');
                $(".file-browser ul li").last().click(function(){
                    $("#page-file-editor").get(0).dataset.file = $(".file-browser").get(0).dataset.dir + '/' + this.dataset.dir;
                    go_to('file-editor');
                    /*$(".right-sidebar").show("slide", { direction: "right" }, 200);
                    $(".right-sidebar h3").html(this.dataset.dir);
                    if (this.dataset.ft == "image") {
                        $(".right-sidebar img").show();
                        $(".right-sidebar img").get(0).src = URL + '?ajax=get&get=' + $(".file-browser").get(0).dataset.dir + '/' + this.dataset.dir;
                        $(".right-sidebar pre").hide();
                    } else {
                        $(".right-sidebar pre").show();
                        $(".right-sidebar pre").get(0).innerHTML = 'Loading...';//URL + '?ajax=get&get=' + $(".file-browser").get(0).dataset.dir + '/' + this.dataset.dir;
                        $.get(URL + '?ajax=get&get=' + $(".file-browser").get(0).dataset.dir + '/' + this.dataset.dir, function(data){
                            $(".right-sidebar pre").html(data.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'));
                        })
                        //$(".right-sidebar pre").load(URL + '?ajax=get&get=' + $(".file-browser").get(0).dataset.dir + '/' + this.dataset.dir);
                        $(".right-sidebar img").hide();
                    }*/
                });
                if (ft == "image") {
                    console.log(URL + "?ajax=get&get=" + data.dir + "/" + data.files[i]);
                    $(".file-browser ul li").last().append('<div class="preview" style="background-image:url(' + URL + '?ajax=get&get=' + data.dir + '/' + data.files[i] + ')"></div>');
                }
            }
        } catch (e) {error_handler(e);}
        wait(false);
    });
}

function filesystem_activate() {
    filesystem_navigate($(".file-browser").get(0).dataset.dir);
}

function vulnerabilities_activate() {
    wait(true);
    $.getJSON(URL + "?ajax=vulnerabilities", function(data){
        try {
            $("div#page-vulnerabilities div.page").empty();
            for (var id in data) {
                var vuln = $('<div class="tile double orange" data-id="' + id + '">'
                    + '<h3>' + id + '</h3>'
                    + '<p>' + data[id] + '</p>'
                    + '</div>').appendTo("div#page-vulnerabilities div.page");
                vuln.click(function() {
                    window.open('http://www.osvdb.org/show/osvdb/' + this.dataset.id);
                });
            }
        } catch (e) {error_handler(e);}
        wait(false);
    });
}

function console_activate() {
    console.log("console_activate");
    $("#console").terminal(function(command, term) {
        wait(true);
        TERM = term;
        term.pause();
        $.getJSON(URL + "?ajax=passthru&passthru=" + escape(command), function(data){
            xshell_log("Received console data");
            try {
                $("#console").get(0).dataset.cwd = data.cwd;
                TERM.echo(data.output);
                TERM.resume();
            } catch (e) {error_handler(e);}
            wait(false);
        })
    }, {prompt: '$ ', name: 'console', 'greetings': 'xshell v2.0.0a', 'enabled': false});
}

function help_activate() {
    
}

function fileeditor_activate() {
    $("#page-file-editor h3 small").html($("#page-file-editor").get(0).dataset.file);
    if (EDITOR == null) {
        EDITOR = ace.edit("editor");
        EDITOR.setTheme("ace/theme/monokai");
    }
    EDITOR.setReadOnly(true);
    wait(true);
    $.getJSON(URL + "?ajax=getfileinfo&file=" + $("#page-file-editor").get(0).dataset.file, function(data) {
        console.dir(data);
        EDITOR.setValue(data.contents);
        EDITOR.setReadOnly(!data.writeable);
        if (data.writeable == true) {
            $("#tile-save").click(function(){
                console.log("Save");
                EDITOR.setReadOnly(true);
                wait(true);
                $.post(URL + "?ajax=save&save=" + $("#page-file-editor").get(0).dataset.file, {contents: EDITOR.getValue()}, function(data) {
                    console.dir(data);
                    EDITOR.setReadOnly(false);
                    wait(false);
                })
            });
            $("#tile-save").removeClass("inactive");
            $("#tile-delete").click(function(){

            });
            $("#tile-delete").removeClass("inactive");
        }
        else
        {
            $("#tile-save").addClass("inactive");
            $("#tile-save").click(function(){});
            $("#tile-delete").addClass("inactive");
            $("#tile-delete").click(function(){});
        }
        wait(false);
    });
    $("#tile-download").click(function(){
        window.open(URL + "?ajax=download&download=" + $("#page-file-editor").get(0).dataset.file);
    });
}

function xshell_log(text) {
    console.log("xshell: " + text);
    $("#p-log").html(text + "<br />" + $("#p-log").html());
}

function open_page(a) {
    // If it's a sidebar, simulate click
    $("#sidebar li").each(function(index) {
        if (this.dataset.link == a) {
            /* Reset the sidebar */
            $("#sidebar li").each(function() {
                if (this.dataset.active == "1") {
                    this.dataset.active = "0";
                    this.className="sidebar-li-inactive";
                    $("#page-" + this.dataset.link).attr("class", "page-container-hidden");
                }
            });
            this.dataset.active = "1";
            this.className="sidebar-li-active";
            window.location.hash = this.dataset.link;
            document.title = "xshell » " + this.innerHTML;
        }
    });
    // If it's another page, show it
    $("div.page-container-hidden").each(function(index) {
        if (this.id == "page-" + a) {
            console.log(a)
            $("div.page-container").attr("class", "page-container-hidden");
            $("#page-" + a).attr("class", "page-container");
            try {
                console.log(a.replace("-", "") + "_activate");
                var activateFunction = window[a.replace("-", "") + "_activate"];
                activateFunction();
            } catch(e) {
                error_handler(e);
            }
        }
    });
}

function go_to(a) {
    open_page(a);
    HISTORY.push(a);
}

function go_back() {
    if (HISTORY.length < 2) return;
    HISTORY.pop();
    open_page(HISTORY[HISTORY.length - 1]);
}

function error_handler(e) {
    var details = Array();
    details['message'] = e.message;
    details['name'] = e.name
    try {
        details['line_number'] = e.linenumber;
    } catch(e) {
        details['line_number'] = null;
    }
    try {
        details ['stack'] = e.stack;
    } catch(e) {
        details['stack'] = null;
    }
    console.dir(e);
    console.dir(details);
    $("#div-loading h3").html('<span class="icon">&#xf06a;</span> Error');
    $("#loading-text").html("An unexpected JavaScript error occurred :(<br /><small>(" + details['name'] + ")</small>");
    $("#div-loading").show("slide", { direction: "right" }, 200);
}