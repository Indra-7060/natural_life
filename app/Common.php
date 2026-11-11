<?php

/**
 * The goal of this file is to allow developers a location
 * where they can overwrite core procedural functions and
 * replace them with their own. This file is loaded during
 * the bootstrap process and is called during the framework's
 * execution.
 *
 * This can be looked at as a `master helper` file that is
 * loaded early on, and may also contain additional functions
 * that you'd like to use throughout your entire application
 *
 * @see: https://codeigniter.com/user_guide/extending/common.html
 */

if (! function_exists('serverless')) {
    /**
     * True on a host with a read-only disk (Vercel sets VERCEL=1 itself).
     * Sessions, cache and uploaded media then live in the database instead of in files.
     * APP_SERVERLESS=1 switches the same behaviour on anywhere, for testing.
     */
    function serverless(): bool
    {
        return (bool) (getenv('VERCEL') ?: getenv('APP_SERVERLESS'));
    }
}
