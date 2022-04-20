<?php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Capsule\Manager as Capsule;

class Tcc extends Migration
{
    public function up()
    {
        $capsule = new Capsule();
        $capsule::schema()->create('tcc', function (Blueprint $table) {
            $table->increments('id');
            $table->string('serial_number');
            $table->string('dbpath')->nullable();
            $table->string('service')->nullable();
            $table->string('client')->nullable();
            $table->integer('client_type')->nullable();
            $table->boolean('allowed')->nullable();
            $table->integer('prompt_count')->nullable();
            $table->string('indirect_object_identifier')->nullable();
            $table->bigInteger('last_modified')->nullable();
            
            $table->index('serial_number');
            $table->index('dbpath');
            $table->index('service');
            $table->index('client');
            $table->index('client_type');
            $table->index('allowed');
            $table->index('prompt_count');
            $table->index('indirect_object_identifier');
        });
    }
    
    public function down()
    {
        $capsule = new Capsule();
        $capsule::schema()->dropIfExists('tcc');
    }
}
