<?php
class BridgeTools{

	const dateValidatorRegex = "=(?P<day>[0-9]{1,2})(\.|/|\s)(?P<month>([0-9]{1,2}|Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec))(\.|/|\s)(?P<year>[0-9]{1,4})=is";
    const phoneValidatorRegex = "=.*?(\d.*?){9,}.*?=is";

    static function isActiveReferer(){
        $activeReferers = array('yourlocaldomain.example.com','www.yourlivedomain.org.nz');
        if (isset($_SERVER['HTTP_REFERER'])){
            $host = parse_url($_SERVER['HTTP_REFERER'],PHP_URL_HOST);
            return in_array($host, $activeReferers);
        }else{
            return 0;
        }

    }

    static function getSortOrderSeparator(){

        if (self::isActiveReferer()){
            return '&';
        }else{
            return '?';
        }
    }

    static function makeUriJoomlaCompatible($uri){

        if (isset($_SERVER['HTTP_REFERER'])){
			$origHost = parse_url($uri,PHP_URL_HOST);
			if (parse_url($uri,PHP_URL_SCHEME)){
				$uri = substr($uri,strlen(parse_url($uri,PHP_URL_SCHEME).'://'));
			}
			if ($origHost){
				$uri = substr($uri,strlen($origHost));
			}

			$devPrefix = '/frontend_dev.php';
			$folderPrefix = '/msystem/web';
			$scheme = parse_url($_SERVER['HTTP_REFERER'],PHP_URL_SCHEME);
			$host = parse_url($_SERVER['HTTP_REFERER'],PHP_URL_HOST);
			$path = parse_url($_SERVER['HTTP_REFERER'],PHP_URL_PATH);
			$dbg = 0;
			if (self::isActiveReferer()){

				if (substr($uri,0,strlen($folderPrefix)) == $folderPrefix){
			   		$uri = substr($uri,strlen($folderPrefix));
			   	}
				if (substr($uri,0,strlen($devPrefix)) == $devPrefix){
			   		$uri = substr($uri,strlen($devPrefix));
			   	 	$dbg = 1;
			   	}
			   	$colsmatch = array();
			   	preg_match('#cols/(\d+)#',$uri,$colsmatch);
			   	if (isset($colsmatch[1])){
			   	    $colsUrl = '&cols='.$colsmatch[1];
			   	}else{
			   	    $colsUrl = '';
			   	}

                $uri = $scheme.'://'.$host.$path.'index.php?option=com_bridge&uri='.
			   		urlencode($uri).$colsUrl;
                if ($dbg){
			   		//$uri .= '&sfdbg=1';
			   	}
			}
		}
	    return $uri;
	}



}
