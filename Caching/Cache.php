<?php
namespace App\Helper;

/**
 * @author Vincent <vincent@allyourmedia.nl>
 * @since 2019-12-6
 */
class Cache 
{
    private $error;

    public function pseudo_cache($domain, $name, $response, $cachetime = 3600 * 2)
    {
        if (!is_dir($domain)) {
            mkdir($domain);
        }
        $cachefile = $domain . '/' . md5($name) . '.json';
        $json = json_encode($response);
        return $this->write_cache($cachefile, $cachetime, $json);
    }

    public function write_cache($cachefile, $cachetime, $json)
    {
        if (file_exists($cachefile) && filemtime($cachefile) + $cachetime >= time() && 3 <= filesize($cachefile)) {
            $file = fopen($cachefile, 'r');
            $content = fread($file, filesize($cachefile));
            fclose($file);
            return json_decode($content);
        } else if (
            file_exists($cachefile) && filemtime($cachefile) + $cachetime < time()
            // || file_exists($cachefile) && 3 >= filesize($cachefile)
        ) {
            $file = fopen($cachefile, 'w+');
            fwrite($file, $json);
            $content = fread($file, filesize($cachefile));
            fclose($file);
            return json_decode($content);
        } else {
            $file = fopen($cachefile, 'w+');
            fwrite($file, $json);
            $content = fread($file, filesize($cachefile));
            fclose($file);
            return json_decode($content);
        }

    }

    public function cachefile_exists($domain, $name)
    {
        $cachefile = $domain . '/' . md5($name) . '.json';
        if (file_exists($cachefile) && 3 <= filesize($cachefile)) {
            return true;
        } else {
            return false;
        }
    }

    public function checkSl($domain, $name) 
    {
        $cachefile = $domain . '/' . md5($name) . '.json';
        if (file_exists($cachefile) && 3 <= filesize($cachefile)) {
            $file = fopen($cachefile, 'r');
            $content = fread($file, filesize($cachefile));
            fclose($file);
            $array = json_decode($content);
            $result = $array->result;
            $error = $array->error;
            
            if ($result == 'true') {
                return true;
            } else {
                $this->error = $error;
                return false;
            }
        }
    }

    public function getError() 
    {
        return $this->error;
    }

}