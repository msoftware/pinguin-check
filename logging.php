<?

function logInfo ($str)
{
        global $logging, $dateformat;
        if ($logging > 0)
        {
                $time = time();
                echo "INFO: " . date ($dateformat,$time) . " " . $str . "<br>\r\n";
        }
}

?>
