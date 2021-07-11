<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddBeforeUserInsertTrigger extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    private $triggerName = "users_before_insert_assert_email_or_phone_exists";
    public function up()
    {
        DB::unprepared("CREATE TRIGGER ".$this->triggerName."
    BEFORE INSERT
    ON users FOR EACH ROW
BEGIN
    IF (NEW.email IS NULL or NEW.email='') and (NEW.phone IS NULL or NEW.phone='') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'email and phone are both empty';
    END IF;
END;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP TRIGGER IF EXISTS " . $this->triggerName);
    }
}
