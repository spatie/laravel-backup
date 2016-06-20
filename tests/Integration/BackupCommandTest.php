<?php

namespace Spatie\Backup\Test\Integration;

use ZipArchive;
use Spatie\Backup\Test\TestHelper;
use Spatie\Backup\Tasks\Backup\Zip;
use Spatie\DbDumper\Databases\MySql;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class BackupCommandTest extends TestCase
{
     protected $testHelper;

    public function setUp()
    {
        parent::setUp();

        $this->app['config']->set('laravel-backup.backup.destination.disks', [
            'local',
            'secondLocal',
        ]);

        $this->testHelper = new TestHelper();
    }

    /** @test */
    public function it_can_backup_only_the_files()
    {
        $resultCode = Artisan::call('backup:run', ['--only-files' => true]);

        $this->assertEquals(0, $resultCode);

        $this->assertFileWithExtensionExistInDirectoryOnDisk('zip', 'mysite.com', 'local');
        $this->assertFileWithExtensionExistInDirectoryOnDisk('zip', 'mysite.com', 'secondLocal');
    }

    /** @test */
    public function it_can_backup_to_a_specific_disk()
    {
        $resultCode = Artisan::call('backup:run', [
            '--only-files' => true,
            '--only-to-disk' => 'secondLocal',
        ]);

        $this->assertEquals(0, $resultCode);

        $this->assertFileWithExtensionDoNotExistInDirectoryOnDisk('zip', 'mysite.com', 'local');
        $this->assertFileWithExtensionExistInDirectoryOnDisk('zip', 'mysite.com', 'secondLocal');
    }

    /** @test */
    public function it_will_fail_when_try_to_backup_only_the_files_and_only_the_db()
    {
        $resultCode = Artisan::call('backup:run', [
            '--only-files' => true,
            '--only-db' => true,
        ]);

        $this->assertEquals(-1, $resultCode);

        $this->seeInConsoleOutput('Cannot use only-db and only-files together');

        $this->assertFileWithExtensionDoNotExistInDirectoryOnDisk('zip', 'mysite.com', 'local');
        $this->assertFileWithExtensionDoNotExistInDirectoryOnDisk('zip', 'mysite.com', 'secondLocal');
    }

    /** @test */
    public function it_will_fail_when_trying_to_backup_a_non_existing_database()
    {
        //as you must have a valid mysql database setted to get green in some other tests, you must invalidate this setting. 
        config(['database.connections.mysql.database' => 'this_db_does_not_exists']);

        $resultCode = Artisan::call('backup:run', [
            '--only-db' => true,
        ]);

        $this->seeInConsoleOutput('Backup failed');
    }

    /** @test */
    public function it_can_backup_an_existing_mysql_database()
    {
        $resultCode = Artisan::call('backup:run', [
            '--only-db' => true,
        ]);

        $fileName = env('DB_DATABASE').'.sql';

        $pathToZip = $this->testHelper->getFirstZipFileFromPath( 'mysite.com', 'local' );

        $this->assertFileExistsInZipFile( $fileName, $pathToZip );
        
    }

    /** @test */
    public function it_backups_by_default_from_a_mysql_database()
    {
        
        MySql::dropTables();

        $resultCode = Artisan::call('migrate', [
            '--database' => 'mysql',
            '--realpath' => $this->testHelper->getMigrationDirectory()
        ]);

        $resultCode = Artisan::call('backup:run', [
            '--only-db' => true,
        ]);

        $fileName = env('DB_DATABASE').'.sql';

        $pathToZip = $this->testHelper->getFirstZipFileFromPath( 'mysite.com', 'local' );

        $zip = new Zip( $pathToZip );

        $fileContent = stream_get_contents($zip->getFile( $fileName ));

        $this->assertContains( '`migrations`', $fileContent );
        $this->assertContains( '`table_1`', $fileContent );
        $this->assertContains( '`table_2`', $fileContent );
        
    }

    /** @test */
    public function it_can_ingore_tables_when_backups_a_mysql_database_using_config_file_settings()
    {

        $this->app['config']->set('laravel-backup.backup.source.databases.mysql', [
            'excludeTables' => [
                'table_1' ,
                'table_2'
            ]
        ]);
        
        MySql::dropTables();

        $resultCode = Artisan::call('migrate', [
            '--database' => 'mysql',
            '--realpath' => $this->testHelper->getMigrationDirectory()
        ]);

        $resultCode = Artisan::call('backup:run', [
            '--only-db' => true,
        ]);

        $fileName = env('DB_DATABASE').'.sql';

        $pathToZip = $this->testHelper->getFirstZipFileFromPath( 'mysite.com', 'local' );

        $zip = new Zip( $pathToZip );

        $fileContent = stream_get_contents($zip->getFile( $fileName ));

        $this->assertContains( '`migrations`', $fileContent );
        $this->assertNotContains( '`table_1`', $fileContent );
        $this->assertNotContains( '`table_2`', $fileContent );
        
    }

    /** @test */
    public function it_can_ingore_tables_when_backups_a_mysql_database_using_artisan_parameters()
    {
        MySql::dropTables();

        $resultCode = Artisan::call('migrate', [
            '--database' => 'mysql',
            '--realpath' => $this->testHelper->getMigrationDirectory()
        ]);

        $resultCode = Artisan::call('backup:run', [
            '--only-db' => true,
            '--exclude-tables' => 'table_1,table_2'
        ]);

        $fileName = env('DB_DATABASE').'.sql';

        $pathToZip = $this->testHelper->getFirstZipFileFromPath( 'mysite.com', 'local' );

        $zip = new Zip( $pathToZip );

        $fileContent = stream_get_contents($zip->getFile( $fileName ));

        $this->assertContains( '`migrations`', $fileContent );
        $this->assertNotContains( '`table_1`', $fileContent );
        $this->assertNotContains( '`table_2`', $fileContent );
        
    }

    /** @test */
    public function it_will_fail_when_trying_to_backup_to_an_non_existing_diskname()
    {
        $resultCode = Artisan::call('backup:run', [
            '--only-to-disk' => 'non existing disk',
        ]);

        $this->assertEquals(-1, $resultCode);

        $this->seeInConsoleOutput('There is not backup destination with a disk named');

        $this->assertFileWithExtensionDoNotExistInDirectoryOnDisk('zip', 'mysite.com', 'local');
        $this->assertFileWithExtensionDoNotExistInDirectoryOnDisk('zip', 'mysite.com', 'secondLocal');
    }
}
