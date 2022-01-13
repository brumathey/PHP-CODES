#!/usr/bin/env php
<?php
/**
 * The bin script.
 * 
 * @category Daemon
 * @package  DaemonCtl
 * @author   Bruno H. Mathey <mathey@eyou.com.br>
 * @license  OpenSource
 * @link    https://github.com/brumathey/Simple-php-daemon
 */

error_reporting(E_ALL);
$GLOBALS["sendKill"]=0;
declare(ticks=1);
require_once("../lib/CONF.class.php");
require_once('../sbin/daemonctl.php');
#Object CONF
$CONF = new Conf();
$daemonctl = new daemonctl();
$daemonctl->pcntlSignal();
$options = getopt('', ['start','stop','restart','status','fg','bg','noloop','debug:','n:','dest:','help']);

if(array_key_exists('help', $options)){
	$daemonctl->help($options);
}
elseif(array_key_exists('status', $options)){
	$daemonctl->status($options);
}
else{
	if($daemonctl->checkCommand($options)){
		if(isset($options['dest']) and $options['dest'] != 'all' and array_key_exists('start', $options) and array_key_exists('fg', $options)){
			if(file_exists($CONF->getEtcDir().$options['dest'])){
				$strJsonFileContents = file_get_contents($CONF->getEtcDir().$options['dest']);
				$confFile = json_decode($strJsonFileContents, true);
				require_once($CONF->getLibDir().$confFile['common']['process']['libRun']);
				$objClass = new $confFile['common']['process']['libClassName']();
				$daemonctl->debug('START',"Daemon ".$options['dest']." Start");
				$daemonctl->fg('START',"Daemon ".$options['dest']." Start");
				$objClass->$confFile['common']['process']['libClassLopping']($confFile,$options);
				exit;			
			}
			else{
				$daemonctl->fg("ERROR","\e[31m\e[1mFile  ".$options['dest']." not exists in ".$CONF->getEtcDir()." \e[0m\n");
				$daemonctl->debug('ERROR',"File  ".$options['dest']." not exists in ".$CONF->getEtcDir()." ");
				exit;
			}
		}
		elseif(isset($options['dest']) and $options['dest'] != 'all' and array_key_exists('start', $options) and array_key_exists('bg', $options)){
			if(file_exists($CONF->getEtcDir().$options['dest'])){
				$strJsonFileContents = file_get_contents($CONF->getEtcDir().$options['dest']);
				$confFile = json_decode($strJsonFileContents, true);
				require_once($CONF->getLibDir().$confFile['common']['process']['libRun']);
				$objClass = new $confFile['common']['process']['libClassName']();
				$daemonctl->debug('START',"Daemon ".$options['dest']." in background, Started");
				$objClass->$confFile['common']['process']['libClassLopping']($confFile,$options);	
			}
			else{
				$daemonctl->debug('ERROR',"File  ".$options['dest']." not exists in ".$CONF->getEtcDir()." ");
				exit;
			}
			
		}
		elseif(isset($options['dest']) and $options['dest'] != 'all' and array_key_exists('start', $options)){
			if(file_exists($CONF->getEtcDir().$options['dest'])){
				$command='';
				$threads=1;
				foreach($options as $key => $value){
					if($value){
						$command.=' --'.$key.'='.$value.' ';
					}
					else{
						$command.=' --'.$key.' ';
					}
				}
				$countPids=count($daemonctl->checkProcess($options));
				if(isset($options['n'])){
					if($countPids >= $options['n'] ){
						$daemonctl->fg('START',"Number of Alread Started threads is ".$countPids.", Nothing to do!");
						$daemonctl->debug('START',"Number of Alread Started threads is ".$countPids.", Nothing to do!");
					}
					else{
						$daemonctl->fg('START',"Starting ".$options['n']." threads for ".$options['dest']."!");
						$daemonctl->debug('START',"Starting ".$options['n']." threads for ".$options['dest']."!");
						$daemonctl->fg('START',"Adding ".($options['n']-$countPids)." threads for ".$options['dest']."!");
						$daemonctl->debug('START',"Adding ".($options['n']-$countPids)." threads for ".$options['dest']."!");
						while(($options['n']-$countPids) >= $threads){
							shell_exec($CONF->getBinDir().'daemon.php '.$command.' --bg  > /dev/null &');
							$daemonctl->fg('START',"Thread n['".$threads."'] for ".$options['dest'].", Started");
							$daemonctl->debug('START',"Thread n['".$threads."'] for ".$options['dest'].", Started");
							$threads++;
						}
					}
				}
				else{
					$strJsonFileContents = file_get_contents($CONF->getEtcDir().$options['dest']);
					$confFile = json_decode($strJsonFileContents, true);
					$options['n']=$confFile['common']['process']['threads'];
					if($countPids >= $options['n'] ){
						$daemonctl->fg('START',"Number of Alread Started threads is ".$countPids.", Nothing to do!");
						$daemonctl->debug('START',"Number of Alread Started threads is ".$countPids.", Nothing to do!");
					}
					else{
						$daemonctl->fg('START',"Starting ".$options['n']." threads for ".$options['dest']."!");
						$daemonctl->debug('START',"Starting ".$options['n']." threads for ".$options['dest']."!");
						$daemonctl->fg('START',"Adding ".($options['n']-$countPids)." threads for ".$options['dest']."!");
						$daemonctl->debug('START',"Adding ".($options['n']-$countPids)." threads for ".$options['dest']."!");
						while(($options['n']-$countPids) >= $threads){
							shell_exec($CONF->getBinDir().'daemon.php '.$command.' --bg  > /dev/null &');
							$daemonctl->fg('START',"Thread n['".$threads."'] for ".$options['dest'].", Started");
							$daemonctl->debug('START',"Thread n['".$threads."'] for ".$options['dest'].", Started");
							$threads++;
						}
					}
				}
				#shell_exec($CONF->getBinDir().'daemon.php '.$command.' --bg  > /dev/null &');
				exit;
			}
			else{
				$daemonctl->fg('ERROR',"File  ".$options['dest']." not exists in ".$CONF->getEtcDir()." ");
				$daemonctl->debug('ERROR',"File  ".$options['dest']." not exists in ".$CONF->getEtcDir()." ");
				exit;
			}
			
		}
		elseif(isset($options['dest']) and $options['dest'] == 'all' and array_key_exists('start', $options) and !array_key_exists('fg', $options) and !array_key_exists('bg', $options)){
			$confFiles=$daemonctl->listConf();
			foreach($confFiles as $key => $file){
				$command='';
				if(isset($file) and $file != ''){
					$options['dest']=$file;
					foreach($options as $key => $value){
						if($value){
							$command.=' --'.$key.'='.$value.' ';
						}
						else{
							$command.=' --'.$key.' ';
						}
					}
					echo $CONF->getBinDir().'daemon.php '.$command."\n" ;
					echo shell_exec($CONF->getBinDir().'daemon.php '.$command.' ');
				}
			}
			exit;
		}
		elseif(isset($options['dest']) and $options['dest'] == 'all' and array_key_exists('stop', $options)){
			$pids=$daemonctl->checkProcess($options);
			$countPids=count($pids);
			$threads=$countPids;
			if($pids){
				$daemonctl->fg('STOP',"Stoping daemon: ".$countPids." threads ");
				$daemonctl->debug('STOP',"Stoping daemon: ".$countPids." threads ");
				foreach($pids as $position => $pidNumber){
					$psdata=explode("|", $pidNumber);
					shell_exec('kill -2 '.$psdata[0].'');
					$daemonctl->fg('STOP',"Daemon n[".$threads."] ".$psdata[1]." Stoped!");
					$daemonctl->debug('STOP',"Daemon n[".$threads."] ".$psdata[1]." Stoped!");
					$threads--;
				}
			}
			else{
				$daemonctl->fg('STOP',"Don't have daemons to stop");
				$daemonctl->debug('STOP',"Don't have daemons to stop");
			}
			exit;
		}
		elseif(isset($options['dest']) and $options['dest'] != 'all' and array_key_exists('stop', $options)){
			$pids=$daemonctl->checkProcess($options);
			$countPids=count($pids);
			$threads=$countPids;
			if($pids){
				$daemonctl->fg('STOP',"Stoping daemon: ".$countPids." threads ");
				$daemonctl->debug('STOP',"Stoping daemon: ".$countPids." threads ");
				foreach($pids as $position => $pidNumber){
					$psdata=explode("|", $pidNumber);
					shell_exec('kill -2 '.$psdata[0].'');
					$daemonctl->fg('STOP',"Daemon n[".$threads."] ".$psdata[1]." Stoped!");
					$daemonctl->debug('STOP',"Daemon n[".$threads."] ".$psdata[1]." Stoped!");
					$threads--;
				}
			}
			else{
				$daemonctl->fg('STOP',"Don't have daemons to stop");
				$daemonctl->debug('STOP',"Don't have daemons to stop");
			}
			exit;
		}
		else{
			$options['help']='1';
			$daemonctl->help($options);
		}
	}
}


?>