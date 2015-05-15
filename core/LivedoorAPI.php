<?php
//Quick hack: to be fixed
$path = '/home/alligate/www/pear/PEAR';
set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once 'HTTP/Request2.php';
date_default_timezone_set('Asia/Tokyo');

class LivedoorAPI{
    private $blogId;
    private $user;
    private $key;

    function __construct($blogId, $user, $key){
        $this->blogId = $blogId;
        $this->user = $user;
        $this->key = $key;
    }

    public function postArticle($title, $content, $thumbnail){

return false;

        $created = date('Y-m-d\TH:i:s\Z');
        $nonce = pack('H*', sha1(md5(time())));
        $pass_digest = base64_encode(pack('H*', sha1($nonce.$created.$this->key)));
        $wsse =
            'UsernameToken Username="'.$this->user.'", '.
            'PasswordDigest="'.$pass_digest.'", '.
            'Nonce="'.base64_encode($nonce).'", '.
            'Created="'.$created.'"';

        $decodedContent = htmlspecialchars_decode($content);
        $urlimg= "http://livedoor.blogcms.jp/atom/blog/".$this->blogId.'/image';
        preg_match("/<img([^\>]*)>/", $decodedContent, $imgs);

        if(isset($imgs[0])){
            preg_match("/(http|https):\/\/[\S]+\.(jpg|jpeg|png)/", $imgs[0], $imgPaths);
            if(isset($imgPaths[0])){ 

                $imgfile = $imgPaths[0];
                $imgdata = file_get_contents($imgfile);
                $content_type = image_type_to_mime_type(exif_imagetype($imgfile));

                try{
                    $headersImg = array(
                        'X-WSSE: ' . $wsse,
                        'Content-Type: ' .$content_type,
                        'Expect:'        
                    );
                    $req = new HTTP_Request2();
                    $req->setUrl($urlimg);
                    $req->setConfig(array('ssl_verify_host' => false, //*前記事参照
                                           'ssl_verify_peer' => false
                                     ));
                    $req->setHeader($headersImg);
                    $req->setMethod(HTTP_Request2::METHOD_POST);
                    $req->setAuth($this->blogId, $this->key);
                    $req->setBody($imgdata);
                    $response = $req->send();

                    $xml = simplexml_load_string($response->getBody());
                    $src = $xml->content['src'];

                    $content = htmlspecialchars(str_replace($imgfile, $src, $decodedContent));

                } catch (HTTP_Request2_Exception $e) {
                    echo($e->getMessage());
                } catch (Exception $e) {
                    echo($e->getMessage());
                }
            }
        }

        $headers = array(
            'X-WSSE: ' . $wsse,
            'Expect:'
        );
        $text64= base64_encode($content);
        $rawdata =
            '<?xml version="1.0"?>'.
            '<entry xmlns="http://purl.org/atom/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/">'.
            '<title type="text/html" mode="escaped">'.$title.'</title>'.
            '<content type="application/xhtml+xml" mode="base64">'.$text64.'</content>'.
            '</entry>';

        $url = "http://livedoor.blogcms.jp/atom/blog/".$this->blogId.'/article';
        try{
            $req = new HTTP_Request2();
            $req->setUrl($url);
            $req->setMethod(HTTP_Request2::METHOD_POST);
            $req->setHeader($headers);
            $req->setBody($rawdata);
            $response = $req->send();
            echo("posted on blog ID:".$this->blogId."\n");

        } catch (HTTP_Request2_Exception $e) {
            echo($e->getMessage());
            return false;
        } catch (Exception $e) {
            echo($e->getMessage());
            return false;
        }
        return true;
    }
}

?>