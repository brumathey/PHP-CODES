<?php
/**
 * The main class.
 * 
 * @category Daemon
 * @package  DaemonCtl
 * @author   Bruno H. Mathey <mathey@eyou.com.br>
 * @license  OpenSource
 * @link    https://github.com/brumathey/Simple-php-daemon
 */

require_once('../lib/CONF.class.php');	

class daemonctl
{
    public function __construct()
    {
		$this->conf = new Conf();
    }
	
	private function __clone()
	{
		
	}
	
	public function help($options){
		$color = $this->conf->getConsoleColor();
		$tbl = $this->conf->getTableBuilder();
		if(array_key_exists('help', $options)){
		
			$tbl->setHeaders(
			    array($color->convert("%CNAME%n"), $color->convert("%CSYNOPSIS%n"), $color->convert("%CDESCRIPTION%n"))
			);
			$tbl->addRow(array("daemon.php",'./daemon.php --<OPTION> --<TREADS|FILE>'," Simple PHP Daemon,\n See OPTION and TREADS|FILE to use it\n" ));
			$tbl->addRow(array($color->convert("%COPTION%n"),$color->convert("%P--<OPTION>%n"),"\n" ));
			$tbl->addRow(array("     help",'./daemon.php --help'," Display this Manual\n" ));
			$tbl->addRow(array("     start",'./daemon.php --start --dest=<FILENAME|ALL> --n=<NUMBER OF THREADS>'," START Daemon process,\n <OPTION> --dest is requered (See --dest)\n" ));
			$tbl->addRow(array("     stop",'./daemon.php --stop  --dest=<FILENAME|ALL> --n=<NUMBER OF THREADS>'," STOP Daemon process,\n <OPTION> --dest is requered (See --dest)\n" ));
			$tbl->addRow(array("     restart",'./daemon.php --restart --dest=<FILENAME> '," RESTART Daemon process, \n <OPTION> --dest is requered (See --dest),\n <OPTION> --n not enable (See --n)\n" ));
			$tbl->addRow(array("     status",'./daemon.php --status '," Show daemon STATUS for ALL Process\n" ));
			$tbl->addRow(array("     fg",'./daemon.php --start --dest=<FILENAME> --fg '," Foreground Mode\n" ));
			$tbl->addRow(array("     noloop",'./daemon.php --start  --dest=<FILENAME> --fg --noloop '," Foreground Mode, But the process will be kill after de first loop\n" ));
			$tbl->addRow(array($color->convert("%CTREADS|FILE%n"),$color->convert("%P--<TREADS|FILE>%n"),"\n" ));
			$tbl->addRow(array("     n",'./daemon.php --start  --dest=<FILENAME> --n=<NUMBER OF THREDS> '," Number of Threads you Want to start\n If is null, Daemon will start number of Threads list on Conf File\n" ));
			$tbl->addRow(array("     dest",'./daemon.php --start  --dest=<FILENAME> '," Start Threads of Conf File on Etc Dir\n See Example.json on ".$this->conf->getEtcDir()." \n" ));
			echo "\e[92;40m\e[1m";
			echo $tbl->getTable();
			echo "\e[0m";
		
			exit;
		}
	}
	
	public function status($options){
		$this->checkUser();
		$color = $this->conf->getConsoleColor();
		$confFiles=explode("\n",shell_exec('ls '.$this->conf->getEtcDir()));
		$tbl = $this->conf->getTableBuilder();
		$tbl->setHeaders(array($color->convert("%CCONF-FILE%n"), $color->convert("%CSTATUS%n"),$color->convert("%CTIME%n"),$color->convert("%CCPU%n"),$color->convert("%CMEM%n"), $color->convert("%CDEFAULT%n"), $color->convert("%CRUNNING%n")));
		foreach($confFiles as $key => $file){
			if(isset($file) and $file != ''){
				$options['dest']=$file;
				$pids=$this->checkProcess($options);
				$countPids=count($pids);
			
			
				$strJsonFileContents = file_get_contents($this->conf->getEtcDir().$options['dest']);
				$confFile = json_decode($strJsonFileContents, true);
				$options['n']=$confFile['common']['process']['threads'];
			
				if($countPids > 0){
					$totalMemory=0;
					$totalCpu=0;
					foreach($pids as $position => $pidNumber){
						$psdata=explode("|", $pidNumber);
						$memory=shell_exec('pmap '.$psdata[0].' |grep total');
						$time=shell_exec('ps -o etime -p '.$psdata[0].' |grep -v ELAP');
						$time=preg_replace('!\s+!', " ", $time);
						$cpu=shell_exec('ps -o %cpu -p '.$psdata[0].' |grep -v CPU');
						$cpu=preg_replace('!\s+!', " ", $cpu);
						$memory=preg_replace("/[^0-9]/", "", $memory);
						$totalMemory=$totalMemory+$memory;
						$totalCpu=$totalCpu+$cpu;
					}
					$totalMemory=$this->sizeFilter($totalMemory);
					$tbl->addRow(array("$file",$color->convert("%PRunning%n"),$color->convert("%P".$time."%n"),$color->convert("%P".$totalCpu."%%%n"),$color->convert("%P".$totalMemory."%n"),$options['n'],$color->convert("%P".$countPids."%n")));
				}
				else{
					$tbl->addRow(array("$file",$color->convert("%CStoped%n"),"-","-","-",$options['n'],"0" ));
				}
			}
		}
		$table=$tbl->getTable();
		echo "\e[92;40m\e[1m".$table."\e[0m";
	}
	
	public function checkCommand($options){
		$this->checkUser();
		if(array_key_exists('start', $options) or array_key_exists('restart', $options) or array_key_exists('stop', $options)){
			if(!array_key_exists('dest', $options) or !$options['dest']){
				echo "\e[31m\e[1mERROR : Missing Param < --dest >\e[0m\n";
				echo "\tPossible types: \n \t\t--dest=all \n \t\t--dest=<ConfFile>\n";
				echo "\t<ConfFile> must to be in : ".$this->conf->getEtcDir()." \n";
				echo "Try Again!\n";
				exit;
			}
			else{
				return true;
			}
		}
		elseif(array_key_exists('fg', $options) and $options['dest'] == 'all'){
				echo "\e[31m\e[1mERROR : Param < --fg >\e[0m\n";
				echo "\tPossible types: \n \t\t--dest=<ConfFile> --fg\n";
				echo "\t<ConfFile> must to be in : ".$this->conf->getEtcDir()." \n";
				echo "Try Again!\n";
				exit;
		
		}
		elseif(array_key_exists('debug', $options) and ($options['debug'] < 1 or $options['debug'] > 3)){
				echo "\e[31m\e[1mERROR : Param < --debug >\e[0m\n";
				echo "\tPossible types: \n \t\t--dest=<ConfFile> --debug=(1-3)\n";
				echo "\t<ConfFile> must to be in : ".$this->conf->getEtcDir()." \n";
				echo "Try Again!\n";
				exit;
		
		}
		elseif(array_key_exists('restart', $options) and $options['dest'] == 'all'){
				echo "\e[31m\e[1mERROR : Param < --restart >\e[0m\n";
				echo "\tPossible types: \n \t\t --restart \n  \t\t--dest=<ConfFile> \n";
				echo "\t<ConfFile> must to be in : ".$this->conf->getEtcDir()." \n";
				echo "Try Again!\n";
				exit;
		
		}
		elseif(array_key_exists('dest', $options)){
			if(!array_key_exists('start', $options) and !array_key_exists('stop', $options) and !array_key_exists('restart', $options)){
				echo "\e[31m\e[1mERROR : Param < --dest >\e[0m\n";
				echo "\tPossible types: \n \t\t--dest=<ConfFile> --<start|stop|restart> \n";
				echo "Try Again!\n";
				exit;
			}
			else{
				return true;
			}
		}
		else{
			return true;
		}
		
	}
	
	public function pcntlSignal(){
		
		pcntl_signal(SIGINT,function(){
			$this->fg("STOPING","received (Ctrl-C is pressed)");
			$this->debug("STOPING","received (Ctrl-C is pressed)");
			$GLOBALS["sendKill"]=1;
			}
		);
		pcntl_signal(SIGTERM,function(){
			$this->fg("STOPING","received ('kill' was called)");
			$this->debug("STOPING","received ('kill' was called)");
			$GLOBALS["sendKill"]=1;
			}
		);
		
	}
	
	public function debug($type,$info){
		$logDir=$this->conf->getLogDir();
		$timezone = new DateTimeZone('America/Fortaleza');
		$dateNow = new DateTime('now', $timezone);
		$logTime=$dateNow->format('Ymd H:i:s');
		$logDay=$dateNow->format('Ymd');
		$logFileName="daemonctl.log";
		
		
		$fd = fopen($logDir.$logDay."_".$logFileName, "a+");
		$str = "[".$logTime."] $type: " . $info ."\r\n";
		fwrite($fd, $str);
		fclose($fd);
		
	}
	
	public function fg($type,$info){
		
		$timezone = new DateTimeZone($this->conf->getTimeZone());
		$dateNow = new DateTime('now', $timezone);
		$logTime=$dateNow->format('Ymd H:i:s');
		if(is_array($info)){
			echo "[".$logTime."] $type: " . $info ."\r\n";
			print_r($info);
		}
		else{
			echo "[".$logTime."] $type: " . $info ."\r\n";
		}
		
		
	}
	
	public function checkLoad($confFile){
		$logDir=$this->conf->getLogDir();
		$timezone = new DateTimeZone($this->conf->getTimeZone());
		$dateNow = new DateTime('now', $timezone);
		$logTime=$dateNow->format('Ymd H:i:s');
		$logDay=$dateNow->format('Ymd');
		$logFileName="daemonctl.log";
		
		$load=sys_getloadavg();
		if ($load[0] >= $confFile['common']['process']['load']['max']) {
			$fd = fopen($logDir.$logDay."_".$logFileName, "a+");
			$str = "[".$logTime."] INFO: SERVER LOAD : ".$load[0]."  Maximum System Load is ".$confFile['common']['process']['load']['max']."  Sleeping ".$confFile['common']['process']['load']['sleep']." seconds \r\n";
			fwrite($fd, $str);
			fclose($fd);
			sleep($confFile['common']['process']['load']['sleep']);
		}
		
	}
	
	public function checkProcess($options){
		
		if($options['dest'] == 'all'){
			$result=shell_exec('ps -ef |grep daemon.php |grep -v grep |grep bg');
			
			$psdata=explode("\n", $result);
			$countProcess=0;
			foreach($psdata as $key => $value){
				if($value){
					$value=preg_replace('!\s+!', " ", $value);
					$psdata=explode(" ", $value);
					#print_r($psdata);
					foreach($psdata as $position => $dest){
						if (preg_match("/dest\=/i", $dest)) {
							$pids[$countProcess]=$psdata[1].'|'.$dest;
						}
					}
					$countProcess++;
				}
			}
		}
		else{
			
			$result=shell_exec('ps -ef |grep '.$options['dest'].' |grep -v grep |grep bg');
			
			$psdata=explode("\n", $result);
			$countProcess=0;
			foreach($psdata as $key => $value){
				if($value){
					$value=preg_replace('!\s+!', " ", $value);
					$psdata=explode(" ", $value);
					#print_r($psdata);
					foreach($psdata as $position => $dest){
						if (preg_match("/dest\=/i", $dest)) {
							$pids[$countProcess]=$psdata[1].'|'.$dest;
						}
					}
					
					$countProcess++;
				}
			}
		}
		
		if(isset($pids)){
			return($pids);
		}
	}
	
	public function listConf(){
		$confFiles=explode("\n",shell_exec('ls '.$this->conf->getEtcDir()));
		return $confFiles;
	}
	
	private function sizeFilter( $bytes ){
	    $label = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB' );
	    for( $i = 0; $bytes >= 1024 && $i < ( count( $label ) -1 ); $bytes /= 1024, $i++ );
	    return( round( $bytes, 2 ) . " " . $label[$i] );
	}	
	
	private function checkUser(){
		$processUser = trim(shell_exec('whoami'));
		if($processUser != $this->conf->getUser() and $processUser != 'root'){
			echo "\e[31m\e[1mERROR : User ".$processUser." not Allowed, you need to be ".$this->conf->getUser()."\e[0m\n";
			exit;
		}
	}	
}	
?>