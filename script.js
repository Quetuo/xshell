function init(i) {
    if (i == 0) {
        /* Load Open Sans */
        document.getElementById("loading-text").innerHTML = "Loading font Open Sans...";
        var f = document.createElement("link");
        f.setAttribute("rel", "stylesheet");
        f.setAttribute("type", "text/css");
        f.setAttribute("href", "http://fonts.googleapis.com/css?family=Open+Sans:300");
        f.onload = function(){init(1);};
        document.getElementsByTagName("head")[0].appendChild(f);
    } else if (i == 1) {
        /* Load Open Sans Condensed */
        document.getElementById("loading-text").innerHTML = "Loading font Open Sans Condensed...";
        var f = document.createElement("link");
        f.setAttribute("rel", "stylesheet");
        f.setAttribute("type", "text/css");
        f.setAttribute("href", "http://fonts.googleapis.com/css?family=Open+Sans+Condensed:300");
        f.onload = function(){init(2);};
        document.getElementsByTagName("head")[0].appendChild(f);
    } else if (i == 2) {
        /* Load Cutive Mono */
        document.getElementById("loading-text").innerHTML = "Loading font Cutive Mono...";
        var f = document.createElement("link");
        f.setAttribute("rel", "stylesheet");
        f.setAttribute("type", "text/css");
        f.setAttribute("href", "http://fonts.googleapis.com/css?family=Cutive+Mono");
        f.onload = function(){init(3);};
        document.getElementsByTagName("head")[0].appendChild(f);
    } else if (i == 3) {
        /* Load prettify */
        document.getElementById("loading-text").innerHTML = "Loading prettify.js...";
        var f = document.createElement("script");
        f.setAttribute("type", "text/javascript");
        f.setAttribute("src", "https://google-code-prettify.googlecode.com/svn/loader/run_prettify.js");
        f.onload = function(){init(4);};
        document.getElementsByTagName("head")[0].appendChild(f);
    } else if (i == 4) {
        /* Load jQuery */
        document.getElementById("loading-text").innerHTML = "Loading jQuery...";
        var f = document.createElement("script");
        f.setAttribute("type", "text/javascript");
        f.setAttribute("src", "http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js");
        f.onload = function(){init(5);};
        document.getElementsByTagName("head")[0].appendChild(f);
    } else if (i == 5) {
        /* Load jQuery */
        document.getElementById("loading-text").innerHTML = "Loading terminal...";
        var f = document.createElement("script");
        f.setAttribute("type", "text/javascript");
        f.setAttribute("src", "jquery.terminal.js");
        f.onload = ready;
        document.getElementsByTagName("head")[0].appendChild(f);
    }
}

URL = "http://" + window.location.host + window.location.pathname;
TERM = null;

function ready() {
    /* Set up version info */
    $("#xshell-version").html(VERSION);
    $("#xshell-version").get(0).href="http://www.quetuo.net/xshell?" + VERSION;
    /* Get rid of loading screen */
    $("#div-loading").remove();
    /* Set up sidebar */
    $("#sidebar li").each(function(index) {
        /* Change classes depending on active or not */
        if (this.dataset.active == "1") {
            this.className="sidebar-li-active";
        } else if (this.dataset.active == "0") {
            this.className="sidebar-li-inactive";
        }
        /* Add onClick handler */
        $(this).click(function() {
            /* Reset the sidebar */
            $("#sidebar li").each(function() {
                if (this.dataset.active == "1") {
                    this.dataset.active = "0";
                    this.className="sidebar-li-inactive";
                    $("#page-" + this.dataset.link).attr("class", "page-container-hidden");
                }
            });
            /* Activate appropriate option */
            this.dataset.active = "1";
            this.className="sidebar-li-active";
            window.location.hash = this.dataset.link;
            document.title = "xshell » " + this.innerHTML;
            /* Activate page */
            $("#page-" + this.dataset.link).attr("class", "page-container");
            var activateFunction = window[this.dataset.link.replace("-", "") + "_activate"];
            activateFunction();
        });
    });
    /* Check if a sidebar location is already specified in URL */
    $("#sidebar li").each(function(index) {
        if (("#" + this.dataset.link) == window.location.hash) {
            $(this).click();
        }
    });
    /* Set up terminal */
 }
 
function wait(on) {
    if (on) {
        document.body.style.cursor = 'wait';
    } else {
        document.body.style.cursor = 'auto';
    }
}

function go_to(a) {
    $("#sidebar li").each(function(index) {
        if (this.dataset.link == a) {
            $(this).click();
        }
    });
}

function dashboard_activate() {
    console.log("dashboard_activate");
    wait(true);
    $("#tile-files").click(function(){
        go_to("file-system");
    });
    $.getJSON(URL + "?ajax=info", function(data) {
        console.dir(data);
        $("#p-software").html(data.server_software.replace("Apache", "<strong>Apache</strong>") + "<br />" + data.php_uname.replace("Linux", "<strong>Linux</strong>").replace("Unix", "<strong>Unix</strong>").replace("Windows", "<strong>Windows</strong>"));
        $("#p-environment").html("User: " + data.user + "<br />Root: " + data.document_root + "<br />File: " + data.filename);
        for (var i = 0; i < data.services.length; i++) {
            $("#p-services").html($("#p-services").html() + data.services[i] + "<br />");
        }
        wait(false);
    });
}

function information_activate() {
    wait(true);
    $.getJSON(URL + "?ajax=info", function(data) {
        xshell_log("Received information data from server");
        console.dir(data);
        $("#span-filename").html(data.filename);
        $("#span-php-version").html(data.php_version);
        $("#span-system").html(data.system);
        $("#span-user").html(data.user);
        $("#span-extensions").html("");
        for (var i = 0; i < data.extensions.length; i ++) {
            $("#span-extensions").html($("#span-extensions").html() + data.extensions[i] + (i == data.extensions.length - 1 ? "" : ", "));
        }
        wait(false);
    });
}

function filesystem_navigate(dir) {
    console.log("Navigating to " + dir + " (Current dir is " + $(".file-browser").get(0).dataset.dir + ")");
    wait(true);
    $.getJSON(URL + "?ajax=dir&dir=" + $(".file-browser").get(0).dataset.dir + "&go=" + dir, function(data) {
        xshell_log("Received filesystem data from server for " + data.dir);
        $(".file-browser").get(0).dataset.dir = data.dir;
        $("#page-file-system h3 small").html(data.dir);
        console.dir(data);
        /* Clear file browser */
        $(".file-browser ul").empty();
        for (var i = 0; i < data.dirs.length; i ++) {
            $(".file-browser ul").append('<li class="file-browser file-directory" data-dir="' + data.dirs[i] + '"><h3>' + data.dirs[i] + '</h3><p>Directory</p></li>');
            $(".file-browser ul li").last().click(function(){
                filesystem_navigate(this.dataset.dir);
            });
        }
        for (var i = 0; i < data.files.length; i ++) {
            console.log(data.files[i]);
            var ft = 'misc';
            var dot = data.files[i].indexOf(".");
            var ext = (dot == -1 ? data.files[i] : data.files[i].substr(dot)).toLowerCase();
            if (ext == ".php") {ft = "php";}
            if (ext == ".html" || ext == ".htm") {ft = "html";}
            if (ext == ".jpg" || ext == ".png" || ext == ".bmp") {ft = "image";}
            $(".file-browser ul").append('<li class="file-browser file-' + ft + '" data-dir="' + data.files[i] + '" data-ft="' + ft + '"><h3>' + data.files[i] + '</h3><p>' + (ft == "misc" ? ext : ft) + ' file</p></li>');
            $(".file-browser ul li").last().click(function(){
                console.log(this.dataset.dir);
                $(".right-sidebar").fadeIn(250);
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
                }
            });
            if (ft == "image") {
                console.log(URL + "?ajax=get&get=" + data.dir + "/" + data.files[i]);
                $(".file-browser ul li").last().append('<div class="preview" style="background-image:url(' + URL + '?ajax=get&get=' + data.dir + '/' + data.files[i] + ')"></div>');
            }
        }
        wait(false);
    });
}

function filesystem_activate() {
    filesystem_navigate($(".file-browser").get(0).dataset.dir);
}

function console_activate() {
    console.log("console_activate");
    $("#console").terminal(function(command, term) {

        TERM = term;
        term.pause();
        //term.resize(80, 24);
        $.getJSON(URL + "?ajax=passthru&passthru=" + escape(command), function(data){
            xshell_log("Received console data");
            console.dir(data);
            $("#console").get(0).dataset.cwd = data.cwd;
            TERM.echo(data.output);
            TERM.resume();
        })
    }, {prompt: '>', name: 'console', 'greetings': 'xshell v2.0.0a', 'enabled': false});
}

function help_activate() {
    
}

function console_launch() {
    
    /* Set up websocket */
    SOCK = new WebSocket(URL.replace("http://", "ws://") + "?ajax=ws&XDEBUG_SESSION_START=session_name", "soap");
    SOCK.onopen = function() {
        console.log("Web socket opened");
        SOCK.send("Hello, World!");
    }
    SOCK.onerror = function(error) {
        console.log(error);
    }
    SOCK.onclose = function() {
        console.log("Web socket closed");
    }
}

function xshell_log(text) {
    console.log("xshell: " + text);
    $("#p-log").html(text + "<br />" + $("#p-log").html());
}