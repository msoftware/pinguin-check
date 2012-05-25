<?
/**
 *
 * Das Modul Cache ermöglicht das Cachen von Funktionsaufrufen.
 * Hierbei werden die Ergebnisse der Funktionen persistent in einer Datei gespeichert.
 * Der Cache hat eine vorgegebene Gültigkeit unnd kann so in frei definierbaren Abständen 
 * aktualisiert werden. 
 * Beim Modul Cache liegt das Augenmerk auf hoher Performance und einfache API für den 
 * Entwickler.
 *
 * Name: PHP Cache
 * Version: 1.0
 * Autor: Michael Jentsch <M.Jentsch@web.de>
 * Webseite: http://www.m-software.de/
 * Lizenz: LGPL 2.0
 *
 **/

class Cache 
{
	/**
	 * Maximales Alter einr Cache Datei in Sekunden wenn nicht anders angegeben
	 **/
	// var $maxage = 2592000; // z.B. 2592000 = 30 Tage
	var $maxage = 86400; // z.B. 2592000 = 1 Tag

	/**
	 * Maximale Länge einer Varible für die Logausgabe
	 **/
	var $maxlogvarlength = 20;
	
	/**
	 * In diesem Vereichnis werden die Cachefiles abgelegt
	 **/
	var $cachedir = "./data";

	/**
	 * Endung der Cache Dateien
	 **/
	var $suffix = ".cache";

	/**
	 * Mit dieser Funktion wird eine belibige Variable im Cache gespeichert
	 * Folgende typen werden unterstützt
	 *  . array 
	 *  . double
	 *  . integer
	 *  . object
	 *  . string
	 *
	 * Parameter:
	 *	String name => Über diese Variable wird ein Cache File eindeutig identifiziert
	 *	Mixed value => Diese Variable wird im Cache gespeichert. Hierbei kann es sich um einen der
	 * 		       folgenden Typen handeln. (array, double, integer, object, string) 
	 *
	 * Return:
	 *	boolean  => true wenn erflogreich gespeichert
	 *
	 **/
	function saveInCache ($name, $value)
	{
		$args = func_get_args();
		$this->logging (1, __FILE__ . " " . __LINE__ . " " . __CLASS__ . " " . __FUNCTION__, $args);
		$file = $this->getFilename ($name);
		// unlink ($file);
		$data = $this->var2string ($value);
		return $this->saveData ($file, $data);
	}

	/**
	 * Mit dieser Funktion wird eine vorher gespeicherte Variable aus dem Cache geladen
	 * Folgende typen werden unterstützt
	 *  . array 
	 *  . double
	 *  . integer
	 *  . object
	 *  . string
	 *
	 * Parameter:
	 *	String name => Über diese Variable wird ein Cache File eindeutig identifiziert
	 *	Integer age => Optionaler Parameter, mit dem man den Wert maxage übersteuern kann.
	 *
	 * Return:
	 *	Mixed => Die Variable aus dem Cache (z.B. ein array oder ein string)
	 *
	 **/
	function loadFromCache ($name, $age = 0)
	{
		$args = func_get_args();
		$this->logging (1, __FILE__ . " " . __LINE__ . " " . __CLASS__ . " " . __FUNCTION__, $args);
		if ($age == 0)
		{
			$age = $this->maxage;
		}
		$file = $this->getFilename ($name);
		$cacheAge = $this->getCacheAge ($name);
		if ($cacheAge < $age)
		{
			$data = $this->loadData ($file);
			$value = $this->string2var ($data);
		} else {
			$value = false;
		}
		return $value;
	}

	/**
	 * Diese Funktion ermittelt das Alter der Datei in Sekunden
	 *
	 * Parameter:
	 *	String => Name des Variable im Cache
	 *
	 * Return:
	 *	Integer value => Alter der Cache Datei in Sekunden
	 *
	 **/
	function getCacheAge ($name)
	{
		$args = func_get_args();
		$this->logging (1, __FILE__ . " " . __LINE__ . " " . __CLASS__ . " " . __FUNCTION__, $args);
		$now = time ();
		$file = $this->getFilename ($name);
		$time = filemtime($file);
		$age = $now - $time;
		if ($age < 0) $age = 0;
		return intval ($age);

	}

	/**
	 * Diese Funktion wandelt einen String in eine bel. Variable um
	 *
	 * Parameter:
	 *	String => Inhalt der Variable als String
	 *
	 * Return:
	 *	Mixed value => Diese Variable wird aus dem String erzeugt
	 *
	 **/
	function string2var ($data)
	{
		$args = func_get_args();
		$this->logging (1, __FILE__ . " " . __LINE__ . " " . __CLASS__ . " " . __FUNCTION__, $args);
		$value = unserialize ($data);
		return $value;
	}

	/**
	 * Diese Funktion wandelt eine bel. Variable in einen String um
	 *
	 * Parameter:
	 *	Mixed value => Diese Variable wird im einen String umgewandelt
	 *
	 * Return:
	 *	String => Inhalt der Variable als String
	 *
	 **/
	function var2string ($value)
	{
		$args = func_get_args();
		$this->logging (1, __FILE__ . " " . __LINE__ . " " . __CLASS__ . " " . __FUNCTION__, $args);
		$data = serialize ($value);
		return $data;
	}

	/**
	 * Diese Funktion erzeugt den Dateinamen für die Identifikation des Cache
	 *
	 * Parameter:
	 *	String name => Name unter dem die Daten im Cache gespeichert werden sollen
	 *
	 * Return:
	 *	String => Dateiname für die Identifikation der Cache Datei
	 *
	 **/
	function getFilename ($name)
	{
		$args = func_get_args();
		$this->logging (1, __FILE__ . " " . __LINE__ . " " . __CLASS__ . " " . __FUNCTION__, $args);
		$crc = abs (crc32($name));
		$md5 = abs (md5($name));
		$ret = $this->cachedir . "/" . $crc . "-" . $md5 . $this->suffix;
		return $ret;
	}

	/**
	 * Diese Funktion speichert die Daten im Cachedir
	 *
	 * Parameter:
	 *	String file => Dateiname unter dem die Daten im Cache gespeichert werden sollen
	 *	String value => In einem String gespeicherte Daten der zu sichernden Variable
	 *
	 * Return:
	 *	boolean  => true wenn erflogreich gespeichert
	 *
	 **/
	function saveData ($file, $value)
	{
		$args = func_get_args();
		$this->logging (1, __FILE__ . " " . __LINE__ . " " . __CLASS__ . " " . __FUNCTION__, $args);
		$ret = false;
		$fp = fopen ($file,"w");
		fwrite($fp,$value);
		fclose($fp);
		return $ret;
	}

	/**
	 * Diese Funktion lädt die Daten aus dem Cachedir
	 *
	 * Parameter:
	 *	String file => Dateiname unter dem die Daten im Cache vorliegen muss
	 *
	 * Return:
	 *	string  => String Inhalt des Cache
	 *
	 **/
	function loadData ($file)
	{
		$args = func_get_args();
                $this->logging (1, __FILE__ . " " . __LINE__ . " " . __CLASS__ . " " . __FUNCTION__, $args);
                $fp = fopen ($file,"r");
		$data = fread ($fp, filesize ($file));
                fclose($fp);
		return $data;
	}

	/**
	 * Diese Funktion ist für das logging. Dies Funktion kann mit einer eigenen Funktion
	 * überschrieben werden oder der Inhalt kann angepasst werden, an den eigenen Logging Mechanismus
	 *
	 * Parameter:
	 *	Integer level => Der entsprechende Loglevel (Info, Warn, Error, Fatal)
	 *	String message => Der Hinweistext der Lognachricht
	 *
	 **/
	function logging ($level, $message, $args = NULL)
	{
		$argstr = "";
		if (isset ($args))
		{
			foreach( $args as $k => $v )
			{
				if (is_array ($v))
				{
					$v = "Array (" . count($v) . ")";
				}
				if (strlen ($v) > $this->maxlogvarlength)
				{
					$v = substr ($v, 0, $this->maxlogvarlength) . "...";
				}
				$argstr .= " " . $k . "->" . $v;
             		}
		}
		// echo $level . " " . $message . " " . $argstr. "\r\n";
	}
}
?>
