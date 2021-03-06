<?php

function add_quotes($str) {
    return sprintf("'%s'", $str);
}

class dbData {
    public function getTemperature() {
        $temperature = '';
        $humidity = '';
        $temperature_o = '';
        $humidity_o = '';
        $time = time();

        if ($db = new PDO('sqlite:temperature/temperature.db')) {
            $waiting = true;
            while($waiting) {
                try {
                    $result = $db->query('SELECT * FROM temperature order by id desc limit 1')->fetch(PDO::FETCH_ASSOC);
                    $temperature = $result['temperature'];
                    $humidity = $result['humidity'];
                    $temperature_o = $result['temperature_o'];
                    $humidity_o = $result['humidity_o'];
                    $time = $result['time'];

                    $waiting = false;
                } catch(PDOException $e) {
                    if(stripos($e->getMessage(), 'DATABASE IS LOCKED') !== false) {
                        usleep(250000);
                    } else {
                        throw $e;
                    }
                }
            }
        }

        return [$temperature,$humidity,$time,$temperature_o,$humidity_o];
    }

    public function getPir() {
        $time = time();
        if ($db = new PDO('sqlite:pir/pir.db')) {
            $waiting = true;
            while($waiting) {
                try {
                    $result = $db->query('SELECT * FROM pir order by id desc limit 1')->fetch(PDO::FETCH_ASSOC);
                    $time = $result['time'];

                    $waiting = false;
                } catch(PDOException $e) {
                    if(stripos($e->getMessage(), 'DATABASE IS LOCKED') !== false) {
                        usleep(250000);
                    } else {
                        throw $e;
                    }
                }
            }
        }
        return $time;
    }

    public function getTemperatureTodayData()
    {
        $time_max = time();
        $time_min = $time_max - 86400;
        $labels = array();
        $data = array();
        $dataH = array();
        $data_o = array();
        $dataH_o = array();

        if ($db = new PDO('sqlite:temperature/temperature.db')) {
            $waiting = true;
            while($waiting) {
                try {
                    $result = $db->query("SELECT * FROM temperature where time < $time_max AND time > $time_min order by id asc");
                    $i = 1;
                    $z = 0;
                    $temp = 0;
                    $temp2 = 0;
                    $temp3 = 0;
                    $temp4 = 0;
                    $time = 0;
                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        if ($i === 12) {

                            $labels[] = date('H:i',$time);
                            $data[] = round($temp/$z,1);
                            $dataH[] = round($temp2/$z,1);
                            $data_o[] = round($temp3/$z,1);
                            $dataH_o[] = round($temp4/$z,1);

                            $temp = 0;
                            $temp2 = 0;
                            $temp3 = 0;
                            $temp4 = 0;
                            $i = 0;
                            $z = 0;
                        }


                        if($row['temperature'] > 0) {
                            $temp += $row['temperature'];
                            $temp2 += $row['humidity'];
                            $temp3 += $row['temperature_o'];
                            $temp4 += $row['humidity_o'];
                            $time = $row['time'];
                            $z++;
                        }

                        $i++;
                    }

                    $waiting = false;
                } catch(PDOException $e) {
                    if(stripos($e->getMessage(), 'DATABASE IS LOCKED') !== false) {
                        usleep(250000);
                    } else {
                        throw $e;
                    }
                }
            }
        }



        return [
            $labels,
            $data,
            $dataH,
            $data_o,
            $dataH_o
        ];
    }

    public function getPirTodayData()
    {
        $time_max = time();
        $time_min = $time_max - 86400;
        $labels = array();
        $data = array();

        if ($db = new PDO('sqlite:pir/pir.db')) {
            $waiting = true;
            while($waiting) {
                try {
                    $result = $db->query("SELECT * FROM pir where time < $time_max AND time > $time_min order by id asc");
                    $time_last = $time_min + 1800;
                    $temp = 0;
                    $time = 0;
                    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                        if ($time_last < $row['time']) {

                            $labels[] = date('H:i',$time);
                            $data[] = $temp;

                            $temp = 0;
                            $time_last = $row['time']+1800;
                        }

                        ++$temp;
                        $time = $row['time'];
                    }
                    $waiting = false;
                } catch(PDOException $e) {
                    if(stripos($e->getMessage(), 'DATABASE IS LOCKED') !== false) {
                        usleep(250000);
                    } else {
                        throw $e;
                    }
                }
            }
        }

        return [$labels,$data];
    }
}