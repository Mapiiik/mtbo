<?php

/**
 * Description of sportident
 *
 * @author Martin
 */
class SportIdent
{
    public $card_id;
    public $stamps;
    public $points;
    
    function SportIdent($card_id)
    {
        $this->card_id = $card_id;
    }
    function loadStampsFromDB()
    {
        $this->stamps = array();
        
        $database = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE_SI);
        if ($database->connect_error) {
            die('Connect Error (' . $database->connect_errno . ') '
                    . $database->connect_error);
        }
        $database->set_charset('utf8');
        
        $result = $database->query("SELECT * FROM stamps WHERE stamp_card_id = {$this->card_id} ORDER BY id_stamp;");
        while ($line = $result->fetch_assoc())
        {
            if (($line['stamp_control_code'] < 30) && ($line['stamp_control_mode'] == 4)) $end = true;
            else if ($end)
            {
                $this->stamps = array();
                unset($end);
            }
            
            if (($this->card_id > 500) && ($this->card_id < 500000))
            {
                $xdatetime = explode(' ', $line['stamp_punch_datetime']);
                $xtime = explode(':', $xdatetime[1]);
                if ($xtime[0] < 8) $xtime[0] = $xtime[0] + 12;
                $xtime = sprintf( '%02d:%02d:%02d', $xtime[0], $xtime[1], $xtime[2]);
                $line['stamp_punch_datetime'] = (date("Y-m-d ") . $xtime);
            }
            
            if (!isset($this->stamps[$line['stamp_control_code']])) $this->stamps[$line['stamp_control_code']] = $line;
            echo $database->error;
        }
    }

    function loadPointsFromDB()
    {
        $this->points = array();

        $database = new mysqli(MYSQL_HOST, MYSQL_USER, MYSQL_PASS, MYSQL_DATABASE);
        if ($database->connect_error) {
            die('Connect Error (' . $database->connect_errno . ') '
                    . $database->connect_error);
        }
        $database->set_charset('utf8');
        
        $result = $database->query("SELECT cislo, body FROM kontroly ORDER BY cislo;");
        while ($line = $result->fetch_assoc())
        {
            $this->points[$line['cislo']] = $line['body'];
            echo $database->error;
        }
    }

    function points()
    {
        foreach ($this->stamps as $stamp)
        {
            $points += $this->points[$stamp['stamp_control_code']];
        }
        return $points;
    }
}
?>
