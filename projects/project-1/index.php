<?php
    function update($conn)
    {

        $file = file_get_contents('http://www.cbr.ru/scripts/XML_daily.asp');
        $currencies = new SimpleXMLElement($file);
        
        foreach ($currencies as $currencн){
        $query = "INSERT INTO currencies
                    (id, num_code, char_code, nominal, name )
                values ('".(string) $currencн['ID']."', ".(string) $currencн->NumCode.", '".(string) $currencн->CharCode."', ".(string) $currencн->Nominal.", '".(string) $currencн->Name."') 
                on conflict (id) do nothing;";    
        $result = pg_query($conn,$query) or die('Query failed: ' . pg_last_error());
        $query = "INSERT INTO prices
                    (currencн_id, value, actual_date)
                values ('".(string) $currencн['ID']."', ".str_replace(",", ".", (string) $currencн->Value).", '".(string) $currencies['Date']."');";    
        $result = pg_query($conn,$query) or die('Query failed: ' . pg_last_error());
        }
    }

    $dbconn = pg_connect("host=postgres port=5432 dbname='currencн_db' user='currencн_db'  password='currencн_db2'")
    or die('Could not connect: ' . pg_last_error());
    
    $timer = 10;
    if ((int)$_ENV["UPDATE_INFO"] ) {
        $timer = (int)$_ENV["UPDATE_INFO"] ;
    }
    $query = "SELECT currencies.char_code, currencies.name, currencies.nominal, last_price.value, last_price.actual_date, last_price.created_at as last_update FROM currencies  JOIN (
        SELECT *
        FROM prices
        WHERE id IN (
           SELECT MAX(id)
           FROM prices
           GROUP BY currencн_id
    )
    ) AS last_price ON currencies.id=last_price.currencн_id; ";
    
    $result = pg_query($dbconn,$query) or die('Query failed: ' . pg_last_error());
    if (!$result) {
        var_dump("An error occurred.\n");
        exit;
    }
    
    $tablesRes = pg_fetch_all($result);
    $last_update = time() - strtotime($tablesRes[0]['last_update']);
    if (!$tablesRes || ($last_update > ($timer*60))) {
        update($dbconn);
        $result = pg_query($dbconn,$query) or die('Query failed: ' . pg_last_error());
        $tablesRes = pg_fetch_all($result);
    }
    
    echo(json_encode($tablesRes));

?>