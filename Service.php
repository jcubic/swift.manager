<?php


require('apps/terminal/leash/lib/Service.php');

class Swift extends Leash {
    public function configure($settings, $repo_path) {
        $this->copy(
            null,
            $this->path . '/apps/terminal/Config.php',
            $this->path . '/apps/terminal/leash/plugins/Config.php'
        );
        parent::configure($settings, $repo_path);
    }

    // -------------------------------------------------------------------------
    public function copy($token, $src, $dest) {
        if ($this->installed() && !$this->valid_token($token)) {
            throw new Exception("Access Denied: Invalid Token");
        }
        return copy($src, $dest);
    }

    // -------------------------------------------------------------------------
    public function unlink($token, $path) {
        if ($this->installed() && !$this->valid_token($token)) {
            throw new Exception("Access Denied: Invalid Token");
        }
        return unlink($path);
    }
}

?>
