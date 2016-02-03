<?php
namespace csvImporter;

use League\Csv\Reader;

class csvImporter
{
    private $dbh;
    private $sth;
    private $table;
    private $fields;
    private $lastParams;
    private $update_where;
    private $extra_values = [];
    private $extra_fields = [];

    /**
     * csvImporter constructor.
     * @param PDOStatement $dbh
     */
    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    /**
     * @param $fields
     * @param $file
     * @param $table
     * @return mixed
     */
    public function import($fields, $file, $table, $insert = true)
    {

        $this->table = $table;
        $this->fields = $fields;

        $sql = $this->generateSQL($insert);
//var_dump($sql);
        $this->sth = $this->dbh->prepare($sql);

        $csv = Reader::createFromPath($file);
        $csv->setOffset(1); //because we don't want to insert the header
        $header = $csv->fetchOne();
//var_dump($header);
        $csv->setOffset(1); //because we don't want to insert the header

        $justDoIT = $csv->each(function ($row) use ($header) {
            $this->attachBinds($row, $header);

            try {
                $exec = $this->sth->execute();
            } catch (\ErrorException $e) {
                echo $e->getMessage();
                //var_dump($this);

            }
//echo PHP_EOL;
            return $exec; //if the function return false then the iteration will stop
        });

        if ($justDoIT) {
            return true;
        }

        //debug -- echo sql and params;
        echo "--DEBUG: ", $sql, $this->lastParams, PHP_EOL;
        return false;

    }

    /**
     * @return string
     * @internal param $fields
     */
    public function generateSQL($insert)
    {

        foreach ($this->fields as $field) {
            $names[] = $field;
            $values[] = ":" . $field;

            $update[] = $field . " = :" . $field;
        }

        return $insert ?
            "INSERT INTO {$this->table} (" . implode(',', $names) . ") VALUES (" . implode(',', $values) . ")"
            :
            "UPDATE {$this->table} SET " . implode(',', $update) . " WHERE " . $this->update_where;

    }

    /**
     * @param $row
     * @internal param $fields
     * @internal param $sth
     */
    function attachBinds($row, $heads)
    {

        //if (is_array($this->extra_fields) && is_array($this->extra_values)) {
           $heads = array_merge($heads, $this->extra_fields);
            $row = array_merge($row, $this->extra_values);
        //}
//var_dump($heads, $row, $this->extra_fields, $this->extra_values);

        $this->lastParams = '';
        foreach ($heads as $key => $head) {

            $clean = trim($head, chr(239) . chr(187) . chr(191));
            $row[$key] = $row[$key] == 'NULL' ? null : $row[$key];

            if (!isset($this->fields[$clean])) continue; //this one from csv isnt used in table

            $this->lastParams .= is_null($row[$key]) ? "null, " : "'" . ($row[$key]) . "', ";

            try {
                $this->sth->bindValue(':' . $this->fields[$clean], $row[$key]);
            } catch (\ErrorException $e) {
                echo $e->getMessage();
            }
        }
//var_dump($this->lastParams);


    }

    public function setUpdateWhere($string)
    {
        $this->update_where = $string;
    }

    public function addExtraData($extra_data)
    {
        foreach ($extra_data as $key => $value) {
            $this->extra_fields[] = $key;
            $this->extra_values[] = $value;
        }
    }
}
