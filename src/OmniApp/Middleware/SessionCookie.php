<?php

namespace OmniApp\Middleware;

use OmniApp\Middleware;

class SessionCookie extends Middleware
{
    protected $options = array();

    public function __construct($options = array())
    {
        $this->options = $options + array(
            'name' => 'sessions',
        );

        ini_set('session.use_cookies', 0);
        session_cache_limiter(false);
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
    }

    public function call($option = array())
    {
        if (session_id() === '') {
            session_start();
        }

        $req = $this->input->env();
        $req['sessions'] = $_SESSION;
        if ($_sessions = (array)$this->input->cookie($this->options['name'])) {
            $_SESSION = $_sessions;
        }

        $this->next();

        if ($_SESSION != $_sessions) {
            $this->output->cookie($this->options['name'], $_SESSION, array('sign' => true));
            $req['sessions'] = array();
        }
        session_destroy();
    }

    /*--------------------
    * Session Handlers
    ---------------------*/

    public function open($path, $name)
    {
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($id)
    {
        return '';
    }

    public function write($id, $data)
    {
        return true;
    }

    public function destroy($id)
    {
        return true;
    }

    public function gc($lifetime)
    {
        return true;
    }
}