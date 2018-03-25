<?php
class Config {
    function __construct($service) {
        $this->leash = $service;
    }
    function on_init() {
        $this->leash->config = json_decode(file_get_contents($this->leash->path . "/../../../config.json"));
    }
    function on_destroy() {
        $this->leash->config = null;
    }
}

?>
