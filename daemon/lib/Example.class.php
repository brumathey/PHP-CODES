<?php

require_once(__DIR__ . '/CONF.class.php');

class example
{
    private $secret_key;

    /**
     * Token generator.
     */
	public function __construct()
	{		
		$this->conf = new Conf();
		
	}
	
	public function Run($confFile,$options){
		require_once($this->conf->getSbinDir().'daemonctl.php');
		$daemonctl = new daemonctl();
		declare(ticks=1);
		$daemonctl->pcntlSignal();
		while(true){
			$daemonctl->checkLoad($confFile);
			
			sleep(5);
			$daemonctl->debug('INFO',"Daemon ".$options['dest']." looping...");
			if(array_key_exists('fg', $options)){
				$daemonctl->fg('INFO',"Daemon ".$options['dest']." looping...");
			}
			sleep(5);
			if(isset($options['noloop'])){
				$daemonctl->fg('STOP',"Daemon ".$options['dest']." nollop");
				exit;
			}
			if($GLOBALS["sendKill"]){
				$daemonctl->debug('STOP',"Daemon ".$options['dest']." stop normaly");
				if(array_key_exists('fg', $options)){
					$daemonctl->fg('STOP',"Daemon ".$options['dest']." stop normaly");
				}
				exit;
			}
		}
	}
	
	
}
?>