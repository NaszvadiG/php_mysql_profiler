<?php
/*
 * A very basic attempt to create a Profiler to show mySQL query activity in a PHP application
 * The goal is the make a facility similar to Codeigniters Profiler
 * Based on mysql general log information here: http://dev.mysql.com/doc/refman/5.1/en/query-log.html
 */
$mysql_log_name = 'Localhost.log'; // name of your log file
$mysql_log_path = 'C:\\xampp\\mysql\\data\\'; // location of your log file
$mysql_log = file($mysql_log_path . $mysql_log_name);
$include_statements = array('select', 'from', 'where', 'join', 'order', 'sort', 'and'); //mysql keywords to look out for

/**
 * Check to see if one of the sql keywords is in the log item
 * @param string $log_item
 * @param array $include_statements
 * @return boolean $mysql
 */
function is_mysql($log_item, $include_statements)
{
    $mysql = false;
    foreach ($include_statements as $phrase)
    {
        if (stristr($log_item, $phrase))
        {
            $mysql = true;
        }
    }
    return $mysql;
}
?>

<table width="100%" border="1">
    <?php
    if (empty($mysql_log))
    {
        echo "<tr>\n<td>No log data found</td>\n</tr>\n";
    }
    else
    {
        $query = '';
        $query_started = false;
        $query_updated = false;
        $time = '';
        foreach ($mysql_log as $log)
        {
            $log_item_array = explode("\t", trim($log), 3);

            if (isset($log_item_array[0]) && substr($log_item_array[0], 0, 6) == date("ymd"))
            {
                $time = str_replace(date("ymd"), "", $log_item_array[0]);
            }

            if ((isset($log_item_array[0]) && is_mysql($log_item_array[0], $include_statements) == true) || (isset($log_item_array[1]) && is_mysql($log_item_array[1], $include_statements) == true))
            {
                $clean_log_item = strtolower(trim(preg_replace('/\s+/', ' ', $log_item_array[0])));

                if (is_numeric(substr($clean_log_item, 0, 1)) && substr($clean_log_item, 2, 5) == 'query')
                {
                    /*
                     * start of a new query
                     */
                    $query_started = true;
                    $query = " " . preg_replace('/\s+/', ' ', trim($log_item_array[1]));
                }
                else
                {
                    $query_started = false;
                }
            }

            if (is_mysql($log_item_array[0], $include_statements) == true)
            {
                $query .= " " . $log_item_array[0];
                $query_updated = true;
            }
            else
            {
                $query_updated = false;
            }

            if ($query != '' && $query_started == false && $query_updated == false)
            {
                echo "<tr>\n";
                echo "\t<td>$time</td>\n";
                echo "\t<td>$query</td>\n";
                echo "</tr>\n";
                $query = '';
            }
        }
    }
    ?>
</table>