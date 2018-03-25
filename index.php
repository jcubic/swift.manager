<?php
/**
 *  This file is part of Leash (Browser Shell)
 *  Copyright (C) 2013-2018  Jakub Jankiewicz <http://jcubic.pl/me>
 *
 *  Released under the MIT license
 *
 */
require('Service.php');
$swift = new Swift('config.json', getcwd());
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
    $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
    if ($swift->debug()) {
        error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
        ini_set('display_errors', 'On');
    }
    echo handle_json_rpc($swift);
    exit;
}

?><!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <title></title>
    <meta name="Description" content=""/>
    <link rel="shortcut icon" href=""/>
    <!--[if IE]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <!--
    <link href="css/style.css" rel="stylesheet"/>
    -->
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <link href="https://code.jquery.com/ui/1.12.1/themes/ui-darkness/jquery-ui.css" rel="stylesheet"/>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <style>
     .ui-widget-content {
         background: #000;
     }
     .ui-dialog iframe.ui-dialog-content, .ui-dialog iframe + .mask {
         padding: 0;
         width: 100% !important;
     }
     .ui-dialog iframe + .mask {
         position: absolute;
         top: 40px;
         z-index: 80;
         left: 0;
         right: 0;
         bottom: 0;
     }
     ul.apps {
         list-style: none;
         padding: 0;
         margin: 0;
     }
     a[data-app] {
         display: flex;
         flex-direction: column;
         align-items: center;
         width: 64px;
         margin: 10px;
     }
     a[data-app] img {
         width: 32px;
         height: 32px;
     }
     a[data-app]:hover img {
         opacity: 0.8;
     }
     body {
         min-height: 100vh;
         margin: 0;
     }
     .apps {
         position: absolute;
         left: 0;
         right: 0;
     }
    </style>
    <script src="apps/terminal/leash/lib/json-rpc.js"></script>
    <script>
     var Storage = {
         setItem: function(name, value) {
             if (typeof value !== 'string') {
                 value = JSON.stringify(value);
             }
             localStorage.setItem(name, value);
         },
         getItem: function(name) {
             var value = localStorage.getItem(name);
             try {
                 return JSON.parse(value);
             } catch (e) {
                 return value;
             }
         },
         removeItem: function(name) {
             localStorage.removeItem(name);
         }
     };
     var swift = new Promise(function(resolve) {
         rpc({
             url: '',
             error: function(e) {
                 if (e.error) {
                     alert(e.error.message);
                 } else {
                     alert(e.message);
                 }
             }
         })(function(service) {
             var swift_token_key = 'swift_token';
             var swift_windows_key = 'swift_windows';
             var swift = {
                 apps: {},
                 logout: function() {
                     Storage.removeItem(swift_token_key);
                     this.apps.terminal.logout();
                 }
             };
             jQuery(function($) {
                 var counts = {};
                 var windows = Storage.getItem(swift_windows_key) || [];
                 $.fn.app = function(name, options) {
                     var app_window = {
                         name: name
                     };
                     counts[name] = counts[name] || 0;
                     counts[name]++;
                     var new_window = true;
                     var dimension;
                     for (var i = windows.length; i--;) {
                         if (windows[i].name === name && counts[name] === windows[i].count) {
                             app_window = windows[i];
                             dimension = app_window.dimension;
                             new_window = false;
                             break;
                         }
                     }
                     if (new_window) {
                         app_window.count = counts[name];
                         app_window.dimension = dimension = {};
                         windows.push(app_window);
                     }
                     Storage.setItem(swift_windows_key, windows);
                     var position;
                     if (dimension.position) {
                         position = {
                             my: 'left top',
                             at: 'left+' + app_window.dimension.position.left +
                                 ' top+' + app_window.dimension.position.top,
                             'of': window,
                             'collision': 'none'
                         }
                     }
                     console.log(dimension);
                     var settings = $.extend({}, {
                         position: position
                     }, dimension.size || {}, options, {
                         drag: function(e, ui) {
                             dimension.position = ui.position;
                             console.log(dimension);
                             Storage.setItem(swift_windows_key, windows)
                             if (typeof options.drag === 'function') {
                                 options.drag(e, ui);
                             }
                         },
                         resize: function(e, ui) {
                             dimension.position = ui.position;
                             dimension.size = {
                                 width: Math.round(ui.size.width),
                                 height: Math.round(ui.size.height)
                             };
                             console.log(dimension);
                             Storage.setItem(swift_windows_key, windows);
                             if (typeof options.reisze === 'function') {
                                 options.reisze(e, ui);
                             }
                         }
                     });
                     console.log(settings);
                     return this.dialog(settings);
                 };
                 $(document).on('click', '[data-app]', function() {
                     var app = $(this).data('app');
                     if (swift.apps[app]) {
                         swift.apps[app].run();
                     }
                 });
             });
             service.installed()(function(err, installed) {
                 if (installed) {
                     function run() {
                         swift.register_app = function(name, config) {
                             swift.apps[name] = config;
                             var icon = 'apps/' + name + '/icon.png';
                             $('.apps').append('<li><a data-app="' + name + '">' +
                                               '<img src="' + icon + '"/>' +
                                               '<span>' + config.label + '</span></a></li>');
                         };
                         resolve(swift);
                         setTimeout(function() {
                             (Storage.getItem(swift_windows_key) || []).forEach(function(window) {
                                 if (swift.apps[window.name]) {
                                     swift.apps[window.name].run();
                                 }
                             });
                         }, 400);
                     }
                     swift.token = Storage.getItem(swift_token_key);
                     if (swift.token) {
                         run();
                     } else {
                         service.login('kuba', 'vampire')(function(err, token) {
                             if (err) {
                                 alert(err.message);
                             } else {
                                 swift.token = token;
                                 Storage.setItem(swift_token_key, token);
                                 run();
                             }
                         });
                     }
                 } else {
                     service.configure({
                         shell: 'exec',
                         sudo: false,
                         password: 'vampire',
                         username: 'kuba',
                         guest: false,
                         home: '/home/kuba',
                         server: 'jcubic',
                         root_password: 'PreFixHex'
                     }, '/apps/terminal/leash/')(function(err) {
                         if (err) {
                             console.error(err);
                         } else {
                             alert('ok');
                         }
                     });
                 }
             });
         });
     });
    </script>
    <?php
    $dir = 'apps/';
    if (is_dir($dir)) {
        if ($dh = opendir($dir)) {
            while (($entry = readdir($dh)) !== false) {
                if (is_dir($dir . $entry) && file_exists($dir . $entry . '/init.js')) {
                    echo '    <script src="' . with_hash($dir. $entry . '/init.js') . '"></script>';
              }
            }
            closedir($dh);
        }
    }
    ?>
</head>
<body>
  <ul class="apps"></ul>
</body>
</html>
