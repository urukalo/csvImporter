<?php
namespace csvImporter;

use League\Csv\Reader;

class csvImporter
{
    private $dbh;
    private $sth;
    private $table;
    private $fields;

    /**
     * csvImporter constructor.
     * @param PDOStatement $dbh
     */
    public function __construct(PDOStatement $dbh)
    {
        $this->dbh = $dbh;
    }

    /**
     * @param $fields
     * @param $file
     * @param $table
     * @return mixed
     */
    public function import($fields, $file, $table)
    {

        $this->table = $table;
        $this->fields = $fields;

        $this->sth = $this->dbh->prepare($this->generateSQL($fields));

        $csv = Reader::createFromPath($file);

        $header = Reader::fetchOne();
        $csv->setOffset(1); //because we don't want to insert the header

        return $csv->each(function ($row) use ($this, $header) {
            $this->attachBinds($row, $header);
            return $this->sth->execute(); //if the function return false then the iteration will stop
        });
    }

    /**
     * @param $fields
     * @return string
     */
    public function generateSQL($fields)
    {

        foreach ($fields as $field) {
            $names[] = $field;
            $values[] = ":" . $field;
        }

        return "INSERT INTO {$this->table} (" . implode(',', $names) . ") VALUES (" . implode(',', $values) . ")";

    }

    /**
     * @param $row
     * @internal param $fields
     * @internal param $sth
     */
    function attachBinds($row, $heads)
    {
        $i = 0;
        foreach ($this->fields as $field) {
            $this->sth->bindValue(':' . $field[$heads[$i]], $row[$i], PDO::PARAM_STR);
            $i++;
        }
    }
}
