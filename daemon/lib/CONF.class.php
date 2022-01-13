<?php
require_once(__DIR__ . '/TableBuilder.class.php');
require_once(__DIR__ . '/Console/Color2.php');

class conf
{
	private $xml = "/app/daemon/lib/main.xml";
	private $confxml;
	private $kill =0;
     /**
     * Conf constructor.
     */
	public function __construct()
	{
		if (file_exists($this->xml)) {
			$confxml = simplexml_load_file($this->xml);
	
		} else {
		    echo "The file $file does not exist \n";
			echo "Path: /app/daemon/lib/\n";
			exit;
		}
		#CONF	
		$this->sbinDir = $confxml->dir->sbin;
		$this->binDir = $confxml->dir->bin;
		$this->logDir = $confxml->dir->logs;
		$this->libDir = $confxml->dir->lib;
		$this->etcDir = $confxml->dir->etc;
		#Alowed User
		$this->user = $confxml->user;
		$this->timeZone = $confxml->timeZone;
		
	}
	
	private function __clone()
	{
		
	}
	
	public function getLogDir()
	{
		return $this->logDir;	
	}
	
	public function getLibDir()
	{
		return $this->libDir;	
	}
	
	public function getEtcDir()
	{
		return $this->etcDir;	
	}
	
	public function getSbinDir()
	{
		return $this->sbinDir;	
	}
	
	public function getBinDir()
	{
		return $this->binDir;	
	}
	
	public function getUser()
	{
		return $this->user;	
	}
	
	public function getTimeZone()
	{
		return $this->timeZone;	
	}
	
	public function getTableBuilder()
	{
		$tableBuilder = new TableBuilder();
	    return $tableBuilder;	
	}
	public function getConsoleColor()
	{
		$color = new Console_Color2();
	    return $color;	
	}
	
}
