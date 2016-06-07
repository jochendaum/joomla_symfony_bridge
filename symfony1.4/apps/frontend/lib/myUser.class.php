<?php

class myUser extends sfBasicSecurityUser{

    public function initialize(sfEventDispatcher $dispatcher, sfStorage $storage, $options = array()){

        parent::initialize($dispatcher, $storage, $options);

        /*if (isset($_GET['logout']) && $_GET['logout'] == '1'){
        	$this->setAuthenticated(false);//does this!:  $this->getUser()->isAuthenticated()
            $this->setAttribute('userId',0);
        }else */if (!$this->isAuthenticated() ||
        	(isset($_GET['jUser']) && intval($_GET['jUser']) != $this->getAttribute('userId'))

        ){
            if (isset($_GET['jUser']) && intval($_GET['jUser']) != $this->getAttribute('userId')){
                $this->setAuthenticated(false);//does this!:  $this->getUser()->isAuthenticated()
                
            }
            if (isset($_GET['jUser']) && intval($_GET['jUser'])){

                $data = Doctrine::getTable('JosUser')->findoneById(intval($_GET['jUser']));
                if (count($data)){
                    $group = $data->getAccessControlObject()->getGroup();
                    $groupLft = $group[0]->getLft();
                }else{
                    $groupLft = 3;
                }

                $q = Doctrine::getTable('JosCoreAclAroGroup')->createQuery('g')->
	            addWhere('g.lft<=?',$groupLft);
	            $groups = $q->execute();
	            foreach ($groups as $group){
	                $this->AddCredential($group->getName());
	            }


	            $this->setAttribute('userId',intval($_GET['jUser']));
                $this->setAuthenticated(true);

	            sfContext::getInstance()->setUser($this);


            }else{
            	$this->setAuthenticated(true);
            	$this->AddCredential('Public Frontend');
            	$this->setAttribute('userId',0);
            	
                $groupLft = 3;
            }


        }



    }



}
