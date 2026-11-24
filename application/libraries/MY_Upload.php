<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Perluasan CI_Upload.
 *
 * Banyak controller memakai allowed_types = '*', yang membuat CI_Upload
 * menerima file apa saja (termasuk .php). Class ini menolak ekstensi yang
 * dapat dieksekusi / dirender server meskipun allowed_types = '*'.
 * Semua bagian ekstensi diperiksa (mis. shell.php.jpg) untuk menutup
 * celah double-extension di server Apache.
 */
class MY_Upload extends CI_Upload
{
    /** Ekstensi yang tidak boleh diunggah. */
    protected $blocked_extensions = array(
        'php', 'php2', 'php3', 'php4', 'php5', 'php6', 'php7', 'php8',
        'phtml', 'pht', 'phar', 'phps', 'inc',
        'cgi', 'pl', 'py', 'sh', 'bash', 'rb',
        'exe', 'dll', 'bat', 'cmd', 'com', 'msi', 'vbs', 'ps1', 'jar',
        'jsp', 'jspx', 'asp', 'aspx', 'cfm',
        'htaccess', 'htpasswd', 'ini', 'user',
        'html', 'htm', 'xhtml', 'shtml', 'svg', 'swf',
    );

    public function is_allowed_filetype($ignore_mime = FALSE)
    {
        $name = isset($this->client_name) && $this->client_name !== '' ? $this->client_name : $this->orig_name;
        $parts = explode('.', strtolower((string) $name));

        // Bagian pertama adalah nama file; sisanya dianggap ekstensi.
        array_shift($parts);
        foreach ($parts as $part) {
            if (in_array(trim($part), $this->blocked_extensions, TRUE)) {
                return FALSE;
            }
        }

        return parent::is_allowed_filetype($ignore_mime);
    }
}
