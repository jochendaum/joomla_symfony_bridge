<?php

class BridgeComponentHelper{

	function switchRawMode(){

		if (stristr(JRequest::getVar('uri'),'jsonList') ||
		strstr(JRequest::getVar('uri'),'sfDependentSelectAuto') ||
		stristr(JRequest::getVar('uri'),'rawOutput')){
			$document = &JFactory::getDocument();
			$doc = &JDocument::getInstance('raw');
			$document = $doc;
		}

	}

	function loadBridgeAssets(){

        $mainframe =& JFactory::getApplication();
	    $document = JFactory::getDocument();

	    if ($mainframe->isAdmin()){
            $document->addScript(JURI::root().'/components/com_bridge/js/jquery-1.5.1.js');
            $document->addScriptDeclaration('jQuery.noConflict();');
	    }


	    $document->addScript(JURI::root().'/components/com_bridge/sfFormExtraPlugin/js/jquery.autocompleter.js');
	    $document->addScript(JURI::root().'/components/com_bridge/sfFormExtraPlugin/js/double_list.js');
	    $document->addStyleSheet(JURI::root().'/components/com_bridge/sfFormExtraPlugin/css/jquery.autocompleter.css');

	    $document->addScript(JURI::root().'/components/com_bridge/sfDependentSelectPlugin/web/js/SelectDependiente.min.js');
	    $document->addScript(JURI::root().'/plugins/editors/tinymce/jscripts/tiny_mce/tiny_mce.js');

	    if ($mainframe->isAdmin()){

	        $document->addStyleSheet(JURI::root().'/msystem/web/css/reset.css');
	        $document->addStyleSheet(JURI::root().'/msystem/web/css/main.css');
	        $document->addStyleSheet(JURI::root().'/msystem/web/css/../csDoctrineActAsSortablePlugin/css/sortable.css');
	        $document->addStyleSheet(JURI::root().'/msystem/web/css/print.css', 'print');
	    }

	}

	function buildRequestUri($uri, $path = ''){

	    if (JRequest::getInt('sfdbg',0) == 1){
		    if (!strstr($uri,'frontend_dev.php/') && !strstr($uri,'index.php/')){
			    $debug_part = 'frontend_dev.php/';
		    }else{
		        $debug_part = '';
		    }
			//$debug_part2 = 'XDEBUG_SESSION_START=2564572562';
		}else{
			if (!strstr($uri,'index.php/') && !strstr($uri,'frontend_dev.php/')){
			    $debug_part = 'index.php/';
			}else{
			    $debug_part = '';
			}
			$debug_part2 = '';
		}
		$finalSfUrl = 'http://'.$_SERVER['SERVER_NAME'].'/msystem/web/'. $debug_part.$uri.
        (strstr($uri,'?')?'&':'?').$debug_part2;

	    $parts = parse_url($finalSfUrl);
		$p = array();
		if (isset($parts['query'])){
		    parse_str($parts['query'], $p);
		}
		if (!is_array($p)){
            $p = array();
		}
		$user =& JFactory::getUser();
        $session =& JFactory::getSession();
        if (!$session->get( 'symfonycookie')){
            $p['jUser'] = $user->id;
        }
        $p['time'] = time();
		if(JRequest::getMethod() == 'GET'){
			$query_params = array();
		    foreach(array_merge($p,JRequest::get('GET')) as $k => $v){
    			if (!in_array($k,array(/*'id',*/'option','uri','Itemid','symfony','__utmz','__utma','ys-orderstatuspanel-active'))){
    		        $query_params[] = $k.'='.urlencode($v);
    			}
    		}
    		if (!isset($parts['fragment'])){
    		    $parts['fragment'] = '';
    		}
		    $query_string = '?'.implode('&', $query_params);
		    $finalSfUrl = $parts['scheme'].'://'.$parts['host'].$parts['port'].
		    $parts['path'].$query_string.$parts['fragment'];

		}
        //print $finalSfUrl;
		return $finalSfUrl;

	}

	function arraysToBrackets($array, $prefix = '', $level=0){

	    $resarr = array();
	    if (0 && $prefix && $level > 1){
	        $prefix = '['.$prefix.']';
	    }
	    foreach ($array as $field => $data){
            if (is_array($data)){
                $resarr = array_merge($resarr, BridgeComponentHelper::arraysToBrackets($data,
                        ($prefix?$prefix.'['.$field.']':$field),$level+1));
            }else{
                if ($level > 0){
                    $resarr[$prefix.'['.$field.']'] = $data;
                }else{
                    $resarr[$prefix.$field] = $data;
                }
            }
	    }
	    return $resarr;
	}

	function requestAndFollow($path = ''){
	    $document = JFactory::getDocument();
        $session =& JFactory::getSession();
        global $sym_headers;
        $c = 0;$first = 1;$httpcode = 0;
        $uri = JRequest::getVar('uri');
	    parse_str($uri);
        if (isset($outside) && $outside == 'true'){
            JApplication::redirect(trim(urldecode($uri)));
            exit;
        }

		while ($c <= 4 && ($first || $httpcode == 302) || $reLogin){
            $first = 0;$reLogin = 0;
    	    $finalSfUrl = BridgeComponentHelper::buildRequestUri($uri, $path);

    		$ch = curl_init($finalSfUrl);
            //var_dump($finalSfUrl);

            // Get sf content
    		curl_setopt($ch, CURLOPT_REFERER, JURI::getInstance()->root().$path);
            //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64; rv:5.0) Gecko/20100101 Firefox/5.0');
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'readHeader');
    		curl_setopt($ch, CURLOPT_HEADER, 0);
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);






    		if(JRequest::getMethod() == 'POST' && $httpcode != 302){


    		    $postThrough = array_merge_recursive(JRequest::get($_POST),array('_csrf_token' => $_POST['_csrf_token']));

                $postThrough = BridgeComponentHelper::arraysToBrackets($postThrough);

    			$files = array();
    			if ($_FILES){

    		        foreach ($_FILES as $file => $details){
    		            //handle symfony style admin generator forms
    		            if (is_array($details['name'])){
    		                foreach ($_FILES[$file]['name'] as $file2 => $details2){
    		                    if ($_FILES[$file]['tmp_name'][$file2]){
    		                        //var_dump('@'.$_FILES[$file]['tmp_name'][$file2]);
    		                        $postThrough[$file.'['.$file2.']'] = '@'.$_FILES[$file]['tmp_name'][$file2];
    		                    }
    		                }
    		            }else{
    		                if ($details['tmp_file']){
    		                    $postThrough[$file] = '@'.$details['tmp_file'];
    		                }
    		            }
    		        }

    		    }
                unset($postThrough['uri']);
                unset($postThrough['option']);
                unset($postThrough['symfony']);
                unset($postThrough['_csrf_token']);

                if ($session->get( 'symfonycookie')){
                    //$postThrough['symfony'] = trim($session->get( 'symfonycookie'));
                }

                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postThrough);
    		    //curl_setopt($ch, CURLOPT_POSTFIELDS, unserialize('a:9:{s:6:"option";s:16:"com_bridge";s:3:"uri";s:19:"/exam/filter/action";s:23:"exam_filters[person_id]";s:0:"";s:21:"exam_filters[code_id]";s:0:"";s:22:"exam_filters[complete]";s:1:"1";s:20:"exam_filters[passed]";s:0:"";s:22:"exam_filters[approved]";s:0:"";s:36:"autocomplete_exam_filters[person_id]";s:0:"";s:11:"_csrf_token";s:0:"";}')
                //$postThrough);
                //curl_setopt($ch, CURLOPT_POSTFIELDS,  curl_setopt ($ch, CURLOPT_POSTFIELDS, array ('hello' => 'testing', 'world' => 'it works'));
                //var_dump($postThrough);

    		}else{
                //print "post off";
                //curl_setopt($ch, CURLOPT_POST, 0);
            }





    		// Get sf content
    		curl_setopt($ch, CURLOPT_REFERER, JURI::getInstance()->root().$path);
            //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64; rv:5.0) Gecko/20100101 Firefox/5.0');
            curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'readHeader');
    		curl_setopt($ch, CURLOPT_HEADER, 0);
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Expect:' ) );

            if ($session->get( 'symfonycookie')){
                curl_setopt($ch, CURLOPT_COOKIE, 'symfony='.trim($session->get( 'symfonycookie')));
                //print "cookie to symfony: ".$session->get( 'symfonycookie');
            }

            //curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:8080');
            //curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);


    		$return = curl_exec($ch);

            //print_r($sym_headers);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);


    		if (!$return){
    			$error = curl_error($ch);
    		}
            //$session->set( 'symfonycookie', null);

            if(isset($sym_headers['symfony']) && $sym_headers['symfony']){
                $session->set( 'symfonycookie', $sym_headers['symfony']);
                //print "<br>JSession set: ".$sym_headers['symfony'];
                //JResponse::setHeader('Set-Cookie', $sym_headers['Set-Cookie']);
            }

		    if ($httpcode >= 400){
    		    $return = "There was an <!--$finalSfUrl-->  error ";
    		    mail ('log@automatem.co.nz', 'symfony wrapper error',
    		    $finalSfUrl."\r\n".
    		    $httpcode."\r\n".
    		    $return."\r\n".
                print_r($_SERVER,1)
    		    );

    		}else if ($httpcode == 302){
                if ($sym_headers['Location']){
                    $query = parse_url(trim($sym_headers['Location']), PHP_URL_QUERY);
                    parse_str($query);
                    parse_str($uri);
                    if ($outside == 'true'){
                        JApplication::redirect(trim(urldecode($uri)));
                    }
                }else{
                    $httpcode = 200;
                }
            }

            if (strstr($uri,'rawOutput')){
                //var_dump($sym_headers);die();

                foreach ($sym_headers['replay'] as $header){
                    if (strstr($header,'Content-Type')){
                        header ($header);
                    }
                }
                foreach ($sym_headers['replay'] as $header){
                    header ($header);

                }
                echo $return;die();
            }

            if (strstr($return,'Credentials Required')){
                $session->clear( 'symfonycookie');
                $reLogin = 1;
            }
            curl_close($ch);
            $c++;
		}
		return $return;

	}

}
if (!function_exists('readHeader')){
    function readHeader($ch, $header){

        global $sym_headers;
        $document = JFactory::getDocument();
		if($pos = strpos($header, 'symfony')/* && strstr($header,'symfony')*/){
			$sym_headers['symfony'] = substr($header, $pos+8);
            //print $header.$pos;
      	}
        if (strstr($header, 'Location: ')){
            $sym_headers['Location'] = trim(substr($header, strlen('Location: ')));
        }

        if (strstr($header,'Content-Type:') ||
        strstr($header,'Content-Disposition:') ||
        strstr($header,'Content-Transfer-Encoding:')){
            $sym_headers['replay'][] = $header;
        }

      	return strlen($header);
    }
}
