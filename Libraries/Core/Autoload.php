<?php
spl_autoload_register(function ($class) {
    $paths = [
        "Libraries/Core/",
        "Helpers/",
        "Models/",
        "Controllers/"
    ];

    foreach ($paths as $path) {
        $file = $path . $class . ".php";
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});
