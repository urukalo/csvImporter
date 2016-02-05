# csvImporter

simple importer from csv file to mysql database 

## Getting Started

add with composer
make folder with cvs files (one file by database)

look down at usage example

### Usage

here is one example for usage in laravel seeder

```
        $connection = new PDO('mysql:host=127.0.0.1;dbname=my_db;charset=utf8', 'my_user', 'my_pass', array(
            PDO::ATTR_PERSISTENT => true
        ));
        
        $csvPath = __DIR__."/csv/";
        $importer = new csvImporter($connection, $csvPath);

        $configs = [

            [
                'table' => 'table_name',
                'fields' => [
                    'CSV_ID' => 'table_id',
                    'CreatedDate' => 'created_at',
                    'ModifiedDate' => 'updated_at',
                    'Website' => 'website',
                    'FaceBook' => 'facebook',
                    'Twitter' => 'twitter',
                    'Instagram' => 'instagram',
                    'ModifiedBy' => 'modified_by',
                    'Enabled' => 'enabled'
                ],
                'file' => 'file_name.csv',
            ]
        ];
        
         echo $importer->run($configs);
        
```

## Running the tests

Explain how to run the automated tests for this system

### Break down into end to end tests

Explain what these tests test and why

```
Give an example
```

### And coding style tests

Explain what these tests test and why

```
Give an example
```

## Deployment

Add additional notes about how to deploy this on a live system

## Built With

* Dropwizard - Bla bla bla
* Maven - Maybe
* Atom - ergaerga

## Contributing

Please read [CONTRIBUTING.md](CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/your/project/tags). 

## Authors

* **Milan Urukalo** - *Initial work* - [PurpleBooth](https://github.com/urukalo)

See also the list of [contributors](https://github.com/your/project/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

* Hat tip to anyone who's code was used
* Inspiration
* etc
