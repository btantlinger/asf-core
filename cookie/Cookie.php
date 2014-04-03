<?php namespace asf\cookie;

class Cookie {

    const Session = null;
    const OneDay = 86400;
    const SevenDays = 604800;
    const ThirtyDays = 2592000;
    const SixMonths = 15811200;
    const OneYear = 31536000;
    const Lifetime = -1; // 2030-01-01 00:00:00
    
    private static $store = null;
    
    public static function setCookieStore(CookieStore $store) {
        self::$store = $store;
    }
    
    public static function getCookieStore() {
        if(self::$store === null) {
            self::$store = new PHPCookieStore();
        }
        return self::$store;
    }

    /**
     * Returns true if there is a cookie with this name.
     *
     * @param string $name
     * @return bool
     */

    public static function exists($name) {
        return self::getCookieStore()->exists($name);
    }

    /**
     * Returns true if there no cookie with this name or it's empty, or 0,
     * or a few other things. Check http://php.net/empty for a full list.
     *
     * @param string $name
     * @return bool
     */
    public static function isEmpty($name) {
        return self::getCookieStore()->isEmpty($name);
    }

    /**
     * Get the value of the given cookie. If the cookie does not exist the value
     * of $default will be returned.
     *
     * @param string $name
     * @param string $default
     * @return mixed
     */
    public static function get($name, $default = '') {
        $store = self::getCookieStore();
        return $store->exists($name) ? $store->get($name, $default) : $default;
    }

    /**
     * Set a cookie. Silently does nothing if headers have already been sent.
     *
     * @param string $name
     * @param string $value
     * @param mixed $expiry
     * @param string $path
     * @param string $domain
     * @return bool
     */
    public static function set($name, $value, $expiry = self::OneYear, $path = '/', $domain = false) {
        $retval = false;
        if (self::getCookieStore()->isCookieSetable()) {
            if ($domain === false) {
                $parts = parse_url(BASE_URL);
                if($parts === false) {
                    return $retval;
                }
                $domain = $parts['host'];
            }

            if ($expiry === -1)
                $expiry = 1893456000; // Lifetime = 2030-01-01 00:00:00
            else if (is_numeric($expiry))
                $expiry += time();
            else
                $expiry = strtotime($expiry);
            $retval = self::getCookieStore()->set($name, $value, $expiry, $path, $domain);
        }
        return $retval;
    }

    /**
     * Delete a cookie.
     *
     * @param string $name
     * @param string $path
     * @param string $domain
     * @param bool $remove_from_global Set to true to remove this cookie from this request.
     * @return bool
     */
    public static function delete($name, $path = '/', $domain = false, $remove_from_global = true) {
        $retval = false;
        if (self::getCookieStore()->isCookieSetable()) {
            if ($domain === false) {
                $parts = parse_url(BASE_URL);
                if($parts === false) {
                    return $retval;
                }
                $domain = $parts['host'];
            }
            $retval = self::getCookieStore()->set($name, '', time() - 3600, $path, $domain);
            if ($remove_from_global) {
                self::getCookieStore()->delete($name);
            }
        }
        return $retval;
    }
}