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
    private $csvPath;

    /**
     * csvImporter constructor.
     * @param PDOStatement $dbh
     */
    public function __construct($dbh, $csvPath)
    {
        $this->dbh = $dbh;
        $this->csvPath = $csvPath;
    }

    /**
     * @param $configs
     * @return string
     */
    public function run($configs)
    {
        foreach ($configs as $config) {

            if (isset($config['update_where'])) {
                $this->setUpdateWhere($config['update_where']);
            }
            if (isset($config['extra_data'])) {
                $this->addExtraData($config['extra_data']);
            }

            echo $this->import($config['fields'], $this->csvPath . $config['file'], $config['table'], !isset($config['update'])) ?
                (isset($config['update']) ?
                    $config['table'] . "... updated!\n"
                    :
                    $config['table'] . "... imported!\n")
                :
                $config['table'] . "... FAILED!\n";
        }
    }

    /**
     * @param $fields
     * @param $file
     * @param $table
     * @return mixed
     */
    private function import($fields, $file, $table, $insert = true)
    {

        $this->table = $table;
        $this->fields = $fields;

        $sql = $this->generateSQL($insert);

        $this->sth = $this->dbh->prepare($sql);

        $csv = Reader::createFromPath($file);

        $header = $csv->fetchOne();

        $csv->setOffset(1); //because we don't want to insert the header

        $justDoIT = $csv->each(function ($row) use ($header, $sql) {
            $this->attachBinds($row, $header);

            try {
                $exec = $this->sth->execute();
            } catch (\ErrorException $e) {
                echo $e->getMessage();
            }

            if(!$exec) {
                echo "--DEBUG: ", $sql, $this->lastParams, PHP_EOL;
            }

            return true;
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
    private function generateSQL($insert)
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
    private function attachBinds($row, $heads)
    {

        if (is_array($this->extra_fields) && is_array($this->extra_values)) {
            $heads = array_merge($heads, $this->extra_fields);
            $row = array_merge($row, $this->extra_values);
        }

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
    }

    private function setUpdateWhere($string)
    {
        $this->update_where = $string;
    }

    private function addExtraData($extra_data)
    {
        foreach ($extra_data as $key => $value) {
            $this->extra_fields[] = $key;
            $this->extra_values[] = $value;
        }
    }
}
